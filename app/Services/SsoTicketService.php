<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * SSO dua arah dengan NewZakicaPOS lewat tiket di tabel sso_tickets (database bersama).
 * Tiket berlaku selama sesi di aplikasi asal: bisa dipakai berulang (user bolak-balik) sampai
 * expires_at (= umur sesi) atau dicabut saat logout. Yang disimpan hanya hash SHA-256 token.
 * Implementasi pasangannya: NewZakicaPOS Lib/General/SsoTicket.cs (format hash harus sama).
 */
class SsoTicketService
{
    public const APP_ZAKIAH = 'zakiah-app';

    public const APP_NEWZAKICA = 'newzakica';

    private const SESSION_KEY = 'sso.newzakica_ticket';

    public function newZakicaUrl(): ?string
    {
        $url = config('services.newzakica.url');

        return filled($url) ? rtrim($url, '/') : null;
    }

    /**
     * URL masuk ke NewZakicaPOS. Tiket disimpan di sesi dan dipakai ulang selama masih berlaku,
     * jadi satu sesi zakiah-app = satu tiket.
     */
    public function newZakicaSignInUrl(User $user, ?string $redirectPath = null): string
    {
        $token = $this->sessionTicket($user);

        return $this->newZakicaUrl().'/Sso/Masuk?'.http_build_query(array_filter([
            'ticket' => $token,
            'redirect' => $redirectPath,
        ]));
    }

    /**
     * URL struk NewZakicaPOS lewat SSO: masuk dengan tiket sesi lalu langsung ke halaman struk
     * (print=1 memunculkan dialog cetak). API struk di sana memakai sesi login user ini.
     *
     * @param  'penjualan'|'rekapan'  $kind
     */
    public function newZakicaStrukUrl(User $user, string $kind, int $id, ?int $locationId = null): string
    {
        $path = "/api/struk/{$kind}/{$id}?".http_build_query(array_filter([
            'location' => $locationId,
            'print' => 1,
        ]));

        return $this->newZakicaSignInUrl($user, $path);
    }

    /**
     * Tukar tiket yang ditujukan ke zakiah-app (boleh berulang selama berlaku & belum dicabut).
     */
    public function consume(string $token): ?User
    {
        if ($token === '') {
            return null;
        }

        $hash = $this->hash($token);

        $updated = DB::update(
            'UPDATE sso_tickets SET last_used_at = UTC_TIMESTAMP(), last_used_ip = ?, use_count = use_count + 1
             WHERE token_hash = ? AND audience = ? AND revoked_at IS NULL AND expires_at > UTC_TIMESTAMP()',
            [request()->ip(), $hash, self::APP_ZAKIAH]
        );

        if ($updated !== 1) {
            return null;
        }

        return User::find(DB::table('sso_tickets')->where('token_hash', $hash)->value('user_id'));
    }

    /**
     * Cabut semua tiket yang dibuat zakiah-app untuk user ini (dipanggil saat logout).
     */
    public function revokeIssuedBy(int $userId): void
    {
        DB::update(
            'UPDATE sso_tickets SET revoked_at = UTC_TIMESTAMP() WHERE user_id = ? AND source = ? AND revoked_at IS NULL',
            [$userId, self::APP_ZAKIAH]
        );
    }

    /**
     * Hanya karyawan aktif dari entity aktif yang boleh masuk lewat SSO.
     */
    public function canSignIn(User $user): bool
    {
        $employee = $user->employee;

        return $employee !== null
            && $employee->entity()->whereNull('deleted_at')->where('status', 'active')->exists();
    }

    /**
     * Path tujuan hanya boleh relatif (cegah open redirect).
     */
    public function safeRedirectPath(?string $path): ?string
    {
        if (! is_string($path) || ! Str::startsWith($path, '/') || Str::startsWith($path, ['//', '/\\'])) {
            return null;
        }

        return $path;
    }

    private function sessionTicket(User $user): string
    {
        $stored = session(self::SESSION_KEY);

        if (is_array($stored) && ($stored['user_id'] ?? null) === $user->id && $this->isValid($stored['token'] ?? '')) {
            return $stored['token'];
        }

        $token = $this->issue($user->id, self::APP_NEWZAKICA);
        session([self::SESSION_KEY => ['token' => $token, 'user_id' => $user->id]]);

        return $token;
    }

    private function issue(int $userId, string $audience): string
    {
        $token = bin2hex(random_bytes(32));

        // Bersihkan tiket yang sudah lama habis (disimpan 1 hari untuk jejak audit).
        DB::delete('DELETE FROM sso_tickets WHERE expires_at < UTC_TIMESTAMP() - INTERVAL 1 DAY');

        DB::insert(
            'INSERT INTO sso_tickets (token_hash, user_id, source, audience, issued_ip, expires_at, created_at)
             VALUES (?, ?, ?, ?, ?, UTC_TIMESTAMP() + INTERVAL ? MINUTE, UTC_TIMESTAMP())',
            [$this->hash($token), $userId, self::APP_ZAKIAH, $audience, request()->ip(), (int) config('session.lifetime')]
        );

        return $token;
    }

    private function isValid(string $token): bool
    {
        return $token !== '' && DB::table('sso_tickets')
            ->where('token_hash', $this->hash($token))
            ->whereNull('revoked_at')
            ->whereRaw('expires_at > UTC_TIMESTAMP()')
            ->exists();
    }

    private function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
