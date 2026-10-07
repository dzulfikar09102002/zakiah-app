<?php

namespace App\Services;

use App\Models\Location;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Closure;
use Generator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ReportPdfService
{
    /**
     * Batas baris per PDF. Dengan FPDF ±0,25 ms & beberapa KB per baris,
     * 20.000 baris ≈ 5 detik / ±30 MB (±700 halaman A4). Query PDF mengambil
     * MAX_ROWS + 1 baris supaya bisa tahu datanya terpotong.
     */
    public const MAX_ROWS = 20000;

    /**
     * Jumlah baris awal yang dipakai untuk menghitung lebar kolom.
     */
    private const WIDTH_SAMPLE_ROWS = 300;

    /**
     * Render laporan ke PDF dan stream ke browser.
     *
     * $columns: [['key' => 'total', 'label' => 'Total', 'format' => 'currency|number|date|datetime|text', 'total' => true]]
     * $rows: iterable (sebaiknya LazyCollection dari cursor()) — dibaca satu per satu, tidak ditampung di memori.
     * $filters: ['Periode' => '01 Jan 2026 - 07 Jan 2026', 'Lokasi' => 'Semua lokasi']
     * $summary: ['Total Stok' => '1.000'] — ditampilkan sebagai kotak ringkasan di atas tabel.
     */
    public function stream(
        string $title,
        array $columns,
        iterable $rows,
        array $filters = [],
        array|Closure $summary = [],
        string $orientation = 'landscape',
    ): Response {
        $entity = auth()->user()?->entity;

        // Tab browser untuk file PDF mentah tidak bisa diberi title & favicon, jadi request
        // pertama mengembalikan halaman pembungkus; PDF dibuat saat dimuat lewat ?raw=1.
        // Rows (LazyCollection) & summary (Closure) belum dieksekusi pada tahap ini.
        if (! request()->boolean('raw')) {
            $branding = app(EntityBrandingService::class);

            return response()->view('report-viewer', [
                'title' => $title.' | '.$branding->displayName($entity?->name).' Backoffice',
                'favicon' => $branding->logoUrl($entity?->name),
                'src' => request()->fullUrlWithQuery(['raw' => 1]),
            ]);
        }

        set_time_limit(120);

        if (filled(request('search'))) {
            $filters['Pencarian'] = (string) request('search');
        }

        $summary = $summary instanceof Closure ? $summary() : $summary;

        $printedBy = auth()->user()?->name;
        $printedAt = Carbon::now()->locale('id')->translatedFormat('d F Y H:i');

        $pdf = new ReportPdfDocument($orientation);
        $pdf->SetTitle($pdf->encode($title));
        $pdf->setFooterText('Dicetak'.($printedBy ? ' oleh '.$printedBy : '').' pada '.$printedAt);
        $pdf->AddPage();

        $pdf->drawLetterhead(
            $this->logoPath($entity?->name),
            $entity?->name ?? config('app.name'),
            $this->letterheadLines($entity),
        );
        $pdf->drawTitle($title);

        if (count($filters)) {
            $pdf->drawFilters($filters);
        }

        if (count($summary)) {
            $pdf->drawSummary($summary);
        }

        $this->drawTable($pdf, $columns, $rows);

        $fileName = Str::slug($title.' '.Carbon::now()->format('Y-m-d His')).'.pdf';

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }

    public function periodLabel(?CarbonInterface $defaultStartAt = null): string
    {
        $startAt = request('start_at')
            ? Carbon::parse(request('start_at'))
            : ($defaultStartAt ?? Carbon::now()->subDays(7));

        $endAt = request('end_at')
            ? Carbon::parse(request('end_at'))
            : Carbon::now();

        return $startAt->locale('id')->translatedFormat('d F Y').' - '.$endAt->locale('id')->translatedFormat('d F Y');
    }

    /**
     * Label lokasi dari filter select_all_location / locs / exclude_locs.
     */
    public function locationsLabel(bool $defaultSelectAll = true): string
    {
        $selectAll = request()->has('select_all_location')
            ? request('select_all_location') == '1'
            : $defaultSelectAll;
        $locs = array_map('intval', (array) request('locs', []));
        $excludeLocs = array_map('intval', (array) request('exclude_locs', []));

        if ($selectAll && count($excludeLocs) > 0) {
            return 'Semua lokasi, kecuali '.$this->locationNames($excludeLocs);
        }

        if (! $selectAll && count($locs) > 0) {
            return $this->locationNames($locs);
        }

        return 'Semua lokasi';
    }

    public function discountLabel(): string
    {
        return match (request('discount')) {
            'available' => 'Dengan diskon',
            'none' => 'Tanpa diskon',
            default => 'Semua diskon',
        };
    }

    public function locationName(?int $locationId): string
    {
        return $locationId ? $this->locationNames([$locationId]) : '-';
    }

    /**
     * Helper kecil untuk kolom; dipakai controller agar definisi kolom ringkas.
     */
    public static function column(string $key, string $label, string $format = 'text', bool $total = false): array
    {
        return compact('key', 'label', 'format', 'total');
    }

    /**
     * Tabel digambar secara streaming: beberapa baris awal ditampung untuk menghitung
     * lebar kolom, sisanya langsung digambar lalu dibuang dari memori.
     */
    private function drawTable(ReportPdfDocument $pdf, array $columns, iterable $rows): void
    {
        $rowsIterator = $this->iterate($rows);
        $totals = array_fill_keys(
            array_column(array_filter($columns, fn (array $column) => $column['total']), 'key'),
            0.0
        );

        $sample = [];
        while ($rowsIterator->valid() && count($sample) < self::WIDTH_SAMPLE_ROWS) {
            $sample[] = [$this->formatRow($rowsIterator->current(), $columns, $totals), $rowsIterator->current()];
            $rowsIterator->next();
        }

        $fontSize = match (true) {
            count($columns) <= 8 => 8,
            count($columns) <= 11 => 7,
            default => 6.5,
        };
        $pdf->setTableFontSize($fontSize);
        $pdf->startTable($this->tableColumns($pdf, $columns, array_column($sample, 0)), $fontSize);

        if (! count($sample)) {
            $pdf->endTable();
            $pdf->drawEmptyRow('Tidak ada data untuk filter ini.');

            return;
        }

        $number = 0;
        foreach ($sample as [$cells, $row]) {
            $number++;
            $this->drawDataRow($pdf, $columns, $number, $cells, $row);
        }
        unset($sample);

        while ($rowsIterator->valid() && $number < self::MAX_ROWS) {
            $number++;
            $row = $rowsIterator->current();
            $this->drawDataRow($pdf, $columns, $number, $this->formatRow($row, $columns, $totals), $row);
            $rowsIterator->next();
        }

        $truncated = $rowsIterator->valid();

        if (count($totals)) {
            $totalCells = [''];
            foreach ($columns as $index => $column) {
                $totalCells[] = array_key_exists($column['key'], $totals)
                    ? $this->formatValue($totals[$column['key']], $column['format'])
                    : ($index === 0 ? 'TOTAL' : '');
            }
            $pdf->drawTotalRow($totalCells);
        }

        $pdf->endTable();

        if ($truncated) {
            $pdf->drawNote('* Data dibatasi '.number_format(self::MAX_ROWS, 0, ',', '.').' baris pertama (total pun hanya dari baris yang tercetak). Persempit filter (periode/lokasi) untuk mencetak data selengkapnya.');
        }
    }

    /**
     * Baris biasa, atau master-detail bila baris punya kunci '_details'
     * (list baris dengan 'label' + nilai kolom angka). Label detail memakai lebar
     * kolom teks di depan; nilainya sejajar kolom angka master. Total hanya dari master.
     */
    private function drawDataRow(ReportPdfDocument $pdf, array $columns, int $number, array $cells, mixed $row): void
    {
        $details = data_get($row, '_details');

        if (! is_array($details)) {
            $pdf->drawRow([(string) $number, ...$cells], $number % 2 === 0);

            return;
        }

        $pdf->drawMasterRow([(string) $number, ...$cells], count($details));

        $firstNumeric = collect($columns)->search(fn (array $column) => in_array($column['format'], ['currency', 'number'], true));
        $span = $firstNumeric === false ? count($columns) : $firstNumeric;
        $noTotals = [];

        foreach ($details as $detail) {
            $detailCells = $this->formatRow($detail, $columns, $noTotals);
            $pdf->drawDetailRow((string) ($detail['label'] ?? ''), array_slice($detailCells, $span), $span + 1);
        }
    }

    /**
     * Lebar kolom dihitung dari header + sampel data, lalu disesuaikan ke lebar halaman:
     * kolom angka dipertahankan, kolom teks yang melebar/menyempit.
     *
     * @return array<int, array{label: string, width: float, align: string}>
     */
    private function tableColumns(ReportPdfDocument $pdf, array $columns, array $sample): array
    {
        $padding = 3;
        $definitions = [['label' => 'No', 'format' => 'number', 'total' => false]];
        foreach ($columns as $column) {
            $definitions[] = $column;
        }

        $widths = [];
        $numeric = [];
        $flexible = [];
        foreach ($definitions as $index => $column) {
            $width = $pdf->stringWidth($column['label'], 'B');

            if ($index === 0) {
                $width = max($width, $pdf->stringWidth((string) self::MAX_ROWS));
            } else {
                foreach ($sample as $cells) {
                    $width = max($width, $pdf->stringWidth($cells[$index - 1]));
                }
            }

            $widths[$index] = $width + $padding;
            $numeric[$index] = in_array($column['format'], ['currency', 'number'], true);
            // Angka & tanggal tidak boleh terpotong; hanya kolom teks yang boleh menyempit.
            $flexible[$index] = $index > 0 && $column['format'] === 'text';
        }

        $available = $pdf->contentWidth();
        $flexibleIndexes = array_keys(array_filter($flexible));
        $flexibleWidths = array_intersect_key($widths, array_flip($flexibleIndexes));
        $flexibleBudget = $available - (array_sum($widths) - array_sum($flexibleWidths));

        if (! count($flexibleIndexes) || $flexibleBudget < count($flexibleIndexes) * 12) {
            // Tidak ada ruang untuk kolom teks: skala semua kolom proporsional.
            $scale = $available / array_sum($widths);
            $widths = array_map(fn (float $width) => $width * $scale, $widths);
        } elseif (array_sum($flexibleWidths) <= $flexibleBudget) {
            // Muat: sisa ruang diprioritaskan untuk kolom total (angka total lebih panjang
            // dari baris data), baru sisanya dibagi ke kolom teks.
            $spare = $flexibleBudget - array_sum($flexibleWidths);
            foreach ($definitions as $index => $column) {
                if ($index === 0 || ! $column['total'] || $spare <= 0) {
                    continue;
                }

                $wanted = $pdf->stringWidth($column['format'] === 'currency' ? 'Rp 9.999.999.999' : '9.999.999', 'B') + $padding;
                $extra = min(max($wanted - $widths[$index], 0), $spare);
                $widths[$index] += $extra;
                $spare -= $extra;
            }

            $scale = (array_sum($flexibleWidths) + $spare) / max(array_sum($flexibleWidths), 1);
            foreach ($flexibleIndexes as $index) {
                $widths[$index] *= $scale;
            }
        } else {
            // Terlalu lebar: pangkas hanya kolom teks terlebar sampai muat.
            $cap = $this->widthCap(array_values($flexibleWidths), $flexibleBudget);
            foreach ($flexibleIndexes as $index) {
                $widths[$index] = min($widths[$index], $cap);
            }
        }

        $result = [];
        foreach ($definitions as $index => $column) {
            $result[] = [
                'label' => $column['label'],
                'width' => $widths[$index],
                'align' => $index === 0 ? 'C' : ($numeric[$index] ? 'R' : 'L'),
            ];
        }

        return $result;
    }

    /**
     * Cari batas lebar c sehingga sum(min(w, c)) = budget (water-filling):
     * kolom pendek tetap utuh, hanya kolom terlebar yang dipangkas.
     *
     * @param  array<int, float>  $widths
     */
    private function widthCap(array $widths, float $budget): float
    {
        sort($widths);
        $remaining = $budget;
        $count = count($widths);

        foreach ($widths as $position => $width) {
            $cap = $remaining / ($count - $position);

            if ($width >= $cap) {
                return $cap;
            }

            $remaining -= $width;
        }

        return $budget;
    }

    /**
     * @return array<int, string>
     */
    private function formatRow(mixed $row, array $columns, array &$totals): array
    {
        $cells = [];
        foreach ($columns as $column) {
            $value = data_get($row, $column['key']);

            if (array_key_exists($column['key'], $totals)) {
                $totals[$column['key']] += (float) $value;
            }

            $cells[] = $this->formatValue($value, $column['format']);
        }

        return $cells;
    }

    private function formatValue(mixed $value, string $format): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return match ($format) {
            'currency' => 'Rp '.number_format((float) $value, 0, ',', '.'),
            'number' => number_format((float) $value, 0, ',', '.'),
            'date' => Carbon::parse($value)->locale('id')->translatedFormat('d F Y'),
            'datetime' => Carbon::parse($value)->locale('id')->translatedFormat('d F Y H:i'),
            default => (string) $value,
        };
    }

    private function iterate(iterable $rows): Generator
    {
        foreach ($rows as $row) {
            yield $row;
        }
    }

    private function locationNames(array $ids): string
    {
        return Location::query()
            ->where('entity_id', auth()->user()?->entity?->id)
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->pluck('name')
            ->map(fn (string $name) => Str::title(Str::lower($name)))
            ->implode(', ');
    }

    /**
     * Baris keterangan kop: "Alamat : ...  ·  Kode Pos : ..." dan "No. Telp : ...  ·  Email : ...".
     * Bagian yang kosong tidak ditampilkan.
     *
     * @return array<int, string>
     */
    private function letterheadLines(?object $entity): array
    {
        $address = collect([$entity?->full_address, $entity?->city, $entity?->province])
            ->filter(fn (mixed $value) => filled($value))
            ->implode(', ');

        $phone = null;
        if (filled($entity?->phone_number)) {
            $countryCode = filled($entity->phone_number_country_code)
                ? '+'.ltrim((string) $entity->phone_number_country_code, '+').' '
                : '';
            $phone = $countryCode.$entity->phone_number;
        }

        return collect([
            ['Alamat' => $address, 'Kode Pos' => $entity?->postal_code],
            ['No. Telp' => $phone, 'Email' => $entity?->email, 'Website' => $entity?->website],
        ])
            ->map(fn (array $parts) => collect($parts)
                ->filter(fn (mixed $value) => filled($value))
                ->map(fn (mixed $value, string $label) => $label.' : '.$value)
                ->implode('   ·   '))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Logo sama dengan favicon (EntityBrandingService). FPDF tidak mendukung PNG ber-alpha,
     * jadi logo diratakan ke latar putih (GD) dan hasilnya di-cache.
     */
    private function logoPath(?string $entityName): ?string
    {
        $source = app(EntityBrandingService::class)->logoFile($entityName);

        if (! $source) {
            return null;
        }

        $cached = storage_path('framework/cache/pdf-logo-'.md5($source.filemtime($source)).'.png');

        if (is_file($cached)) {
            return $cached;
        }

        $image = @imagecreatefrompng($source);
        if (! $image) {
            return null;
        }

        // Perkecil ke tinggi maks. 200px: cukup tajam untuk logo 18mm dan PDF tetap kecil.
        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1, 200 / max($height, 1));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $flattened = imagecreatetruecolor($targetWidth, $targetHeight);
        imagefill($flattened, 0, 0, imagecolorallocate($flattened, 255, 255, 255));
        imagecopyresampled($flattened, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagepng($flattened, $cached);

        return $cached;
    }
}
