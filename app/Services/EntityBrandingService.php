<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Sumber tunggal nama & logo entity untuk favicon, title tab, dan kop PDF.
 * Logo: public/assets/images/{slug-entity}.png, fallback ke zakiah.png.
 */
class EntityBrandingService
{
    private const LOGO_DIRECTORY = 'assets/images';

    private const FALLBACK_LOGO = 'zakiah.png';

    /**
     * Path absolut file logo (untuk PDF), atau null bila tidak ada sama sekali.
     */
    public function logoFile(?string $entityName): ?string
    {
        $relative = $this->logoRelativePath($entityName);

        return $relative ? public_path($relative) : null;
    }

    /**
     * URL logo untuk browser (favicon), dengan versi berdasarkan waktu ubah file agar cache ikut ter-update.
     */
    public function logoUrl(?string $entityName): ?string
    {
        $relative = $this->logoRelativePath($entityName);

        return $relative ? '/'.$relative.'?v='.filemtime(public_path($relative)) : null;
    }

    /**
     * Nama entity untuk ditampilkan; nama yang seluruhnya kapital/kecil dijadikan Title Case.
     */
    public function displayName(?string $entityName): string
    {
        $name = trim((string) $entityName) ?: (string) config('app.name');

        if ($name === Str::upper($name) || $name === Str::lower($name)) {
            return Str::title(Str::lower($name));
        }

        return $name;
    }

    private function logoRelativePath(?string $entityName): ?string
    {
        if ($entityName) {
            $relative = self::LOGO_DIRECTORY.'/'.Str::slug($entityName).'.png';

            if (is_file(public_path($relative))) {
                return $relative;
            }
        }

        $fallback = self::LOGO_DIRECTORY.'/'.self::FALLBACK_LOGO;

        return is_file(public_path($fallback)) ? $fallback : null;
    }
}
