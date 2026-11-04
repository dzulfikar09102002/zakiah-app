<?php

namespace App\Services;

use App\Models\ProductOpnameSession;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

/**
 * QR tanda tangan elektronik, mengikuti pola TtdElektronik di SurabayaTaxNextGen.
 *
 * isi = "1|<jenis>|<id sesi>|<yyMMdd>|<id karyawan>"
 * key = AES-256-GCM dari isi, base64url. Key yang diubah gagal didekripsi.
 *
 * Isi dibuat sependek mungkin supaya QR tidak rapat dan tetap terbaca dari hasil cetak.
 */
class DocumentSignatureService
{
    private const VERSION = '1';

    private const PURPOSE = 'TTD';

    private const CIPHER = 'aes-256-gcm';

    private const IV_LENGTH = 12;

    private const TAG_LENGTH = 16;

    public function makeKey(ProductOpnameSession $session): string
    {
        $content = implode('|', [
            self::VERSION,
            $session->type,
            $session->id,
            $session->end_at->format('ymd'),
            $session->pic_employee_id,
        ]);

        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';
        $encrypted = openssl_encrypt($content, self::CIPHER, $this->encryptionKey(), OPENSSL_RAW_DATA, $iv, $tag, '', self::TAG_LENGTH);

        return rtrim(strtr(base64_encode($iv.$tag.$encrypted), '+/', '-_'), '=');
    }

    /**
     * @return array{type: int, session_id: int, signed_on: string, employee_id: int}|null null bila key tidak valid
     */
    public function readKey(?string $key): ?array
    {
        if (blank($key)) {
            return null;
        }

        $raw = base64_decode(strtr($key, '-_', '+/'), true);

        if ($raw === false || strlen($raw) <= self::IV_LENGTH + self::TAG_LENGTH) {
            return null;
        }

        $content = openssl_decrypt(
            substr($raw, self::IV_LENGTH + self::TAG_LENGTH),
            self::CIPHER,
            $this->encryptionKey(),
            OPENSSL_RAW_DATA,
            substr($raw, 0, self::IV_LENGTH),
            substr($raw, self::IV_LENGTH, self::TAG_LENGTH),
        );

        if ($content === false) {
            return null;
        }

        $parts = explode('|', $content);

        if (count($parts) !== 5 || $parts[0] !== self::VERSION) {
            return null;
        }

        return [
            'type' => (int) $parts[1],
            'session_id' => (int) $parts[2],
            'signed_on' => $parts[3],
            'employee_id' => (int) $parts[4],
        ];
    }

    public function validationUrl(ProductOpnameSession $session): string
    {
        return route('validasi-dokumen', ['k' => $this->makeKey($session)]);
    }

    /**
     * Matriks QR: baris berisi nilai true untuk modul hitam.
     *
     * @return array<int, array<int, bool>>
     */
    public function qrMatrix(string $content): array
    {
        $matrix = Encoder::encode($content, ErrorCorrectionLevel::M())->getMatrix();
        $rows = [];

        for ($y = 0; $y < $matrix->getHeight(); $y++) {
            for ($x = 0; $x < $matrix->getWidth(); $x++) {
                $rows[$y][$x] = $matrix->get($x, $y) === 1;
            }
        }

        return $rows;
    }

    /**
     * QR sebagai path SVG beserta ruang kosong di tepinya, supaya tajam di layar besar dan cetakan.
     *
     * @return array{size: int, path: string}
     */
    public function qrSvg(string $content): array
    {
        $quiet = 2;
        $matrix = $this->qrMatrix($content);
        $path = '';

        foreach ($matrix as $y => $row) {
            $start = null;

            foreach ([...$row, false] as $x => $dark) {
                if ($dark && $start === null) {
                    $start = $x;
                }

                if (! $dark && $start !== null) {
                    $path .= 'M'.($start + $quiet).' '.($y + $quiet).'h'.($x - $start).'v1h-'.($x - $start).'z';
                    $start = null;
                }
            }
        }

        return ['size' => count($matrix) + $quiet * 2, 'path' => $path];
    }

    private function encryptionKey(): string
    {
        return hash('sha256', config('app.key').'|'.self::PURPOSE, true);
    }
}
