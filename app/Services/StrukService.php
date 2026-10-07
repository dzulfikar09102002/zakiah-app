<?php

namespace App\Services;

/**
 * Tautan bertanda tangan ke API struk NewZakicaPOS (Controllers/Api/StrukController.cs).
 * signature = hex HMAC-SHA256 dari "{kind}|{id}|{entity}|{expires}" dengan secret bersama;
 * secret tidak pernah dikirim ke browser.
 */
class StrukService
{
    public const KIND_SALE = 'penjualan';

    public const KIND_TAKING = 'rekapan';

    public function enabled(): bool
    {
        return filled(config('services.struk.url')) && filled(config('services.struk.secret'));
    }

    /**
     * URL struk yang langsung memicu dialog cetak (print=1).
     */
    public function url(string $kind, int $id, int $entityId, ?int $locationId = null): string
    {
        $expires = now()->addSeconds(config('services.struk.ttl'))->getTimestamp();

        $query = array_filter([
            'entity' => $entityId,
            'location' => $locationId,
            'print' => 1,
            'expires' => $expires,
            'signature' => hash_hmac('sha256', "{$kind}|{$id}|{$entityId}|{$expires}", (string) config('services.struk.secret')),
        ], fn (mixed $value) => $value !== null);

        return rtrim((string) config('services.struk.url'), '/')."/api/struk/{$kind}/{$id}?".http_build_query($query);
    }
}
