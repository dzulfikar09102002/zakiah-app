<?php

namespace App\Services;

use App\Enums\OpnameSessionType;
use App\Models\ProductOpnameSession;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * PDF laporan hasil SO dan Revisi SO: halaman ringkasan ber-QR tanda tangan,
 * lalu lampiran produk yang tidak sesuai.
 */
class ProductOpnameReportService
{
    private const QR_SIZE = 28;

    public function __construct(
        protected ReportPdfService $reports,
        protected ProductOpnameReviewService $reviews,
        protected ProductOpnameRevisionService $revisions,
        protected DocumentSignatureService $signatures,
        protected EntityBrandingService $branding,
    ) {}

    public function stream(ProductOpnameSession $session): Response
    {
        $isRevision = $session->sessionType() === OpnameSessionType::Revision;
        $title = $isRevision ? 'Laporan Revisi Hasil Stok Opname' : 'Laporan Hasil Stok Opname';
        $entity = auth()->user()?->entity;

        // Sama dengan laporan lain: halaman pembungkus dulu, PDF dimuat lewat ?raw=1.
        if (! request()->boolean('raw')) {
            return response()->view('report-viewer', [
                'title' => $title.' '.$session->code.' | '.$this->branding->displayName($entity?->name).' Backoffice',
                'favicon' => $this->branding->logoUrl($entity?->name),
                'src' => request()->fullUrlWithQuery(['raw' => 1]),
            ]);
        }

        $pdf = new ReportPdfDocument('portrait');
        $pdf->SetTitle($pdf->encode($title.' '.$session->code));
        $pdf->setFooterText('No. Dokumen '.$session->code);
        $pdf->AddPage();

        $pdf->drawLetterhead(
            $this->reports->logoPath($entity?->name),
            $entity?->name ?? config('app.name'),
            $this->reports->letterheadLines($entity),
        );
        $pdf->drawTitle($title);
        $pdf->drawFilters($this->information($session));

        if ($isRevision) {
            $this->drawRevisionSummary($pdf, $session);
            $this->drawSignature($pdf, $session);
            $this->drawRevisionTable($pdf, $session);
        } else {
            $this->drawOpnameSummary($pdf, $session);
            $this->drawSignature($pdf, $session);
            $this->drawAttachment($pdf, $session);
        }

        $fileName = Str::slug($title.' '.$session->code).'.pdf';

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function information(ProductOpnameSession $session): array
    {
        $info = [
            'No. Dokumen' => $session->code,
            'Toko' => (string) $session->location?->name,
            'Tanggal SO' => $this->date($session->opname_date, 'd F Y'),
            'Mulai' => $this->date($session->start_at, 'd F Y H:i'),
            'Selesai' => $this->date($session->end_at, 'd F Y H:i'),
            'PJ SO' => $this->fullName($session->pic),
            'Petugas' => $session->employees->map(fn ($employee) => $this->fullName($employee))->implode(', '),
        ];

        if ($session->parent) {
            $info = ['SO Asal' => $session->parent->code, ...$info];
        }

        $corrections = $this->reviews->correctionCount($session);

        if ($corrections > 0) {
            $info['Koreksi Scan'] = "{$corrections} scan dibatalkan PJ";
        }

        return $info;
    }

    private function drawOpnameSummary(ReportPdfDocument $pdf, ProductOpnameSession $session): void
    {
        $totals = $this->reviews->reportTotals($session);
        $number = fn (int $value) => number_format($value, 0, ',', '.');

        $groups = [
            'Sesuai' => $totals['matched'],
            'Tidak Sesuai, Diterima (Lampiran 1)' => $totals['accepted'],
            'Tidak Sesuai, Ditolak (Lampiran 2)' => $totals['rejected'],
        ];

        $pdf->startTable([
            ['label' => 'Ringkasan', 'width' => 74, 'align' => 'L'],
            ['label' => 'Jumlah Produk', 'width' => 29, 'align' => 'R'],
            ['label' => 'Stok Sistem', 'width' => 29, 'align' => 'R'],
            ['label' => 'Stok SO', 'width' => 29, 'align' => 'R'],
            ['label' => 'Selisih', 'width' => 29, 'align' => 'R'],
        ], 8);

        $index = 0;

        foreach ($groups as $label => $group) {
            $pdf->drawRow([
                $label,
                $number($group['products']),
                $number($group['recorded']),
                $number($group['counted']),
                $this->signed($group['difference']),
            ], $index++ % 2 === 1);
        }

        $sum = fn (string $key) => array_sum(array_column($groups, $key));

        $pdf->drawTotalRow([
            'Total',
            $number($sum('products')),
            $number($sum('recorded')),
            $number($sum('counted')),
            $this->signed($sum('difference')),
        ]);
        $pdf->endTable();

        $pdf->drawNote('Stok sistem hanya berubah dari selisih yang diterima, yaitu '.$this->signed($totals['accepted']['difference']).'. Selisih yang ditolak tidak mengubah stok.');
        $pdf->Ln(2);
    }

    private function drawRevisionSummary(ReportPdfDocument $pdf, ProductOpnameSession $session): void
    {
        $rows = $this->revisions->rows($session);

        $pdf->drawSummary([
            'Baris Revisi' => number_format($rows->count(), 0, ',', '.'),
            'Produk' => number_format($rows->unique('product_id')->count(), 0, ',', '.'),
            'Total Penyesuaian' => $this->signed((int) $rows->sum('adjustment_qty')),
        ]);
    }

    private function drawSignature(ReportPdfDocument $pdf, ProductOpnameSession $session): void
    {
        if ($pdf->GetY() + self::QR_SIZE + 22 > $pdf->GetPageHeight() - 16) {
            $pdf->AddPage();
        }

        $x = $pdf->GetPageWidth() - 10 - self::QR_SIZE - 32;
        $width = self::QR_SIZE + 32;

        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetXY($x, $pdf->GetY() + 2);
        $pdf->Cell($width, 4, $pdf->encode('Ditandatangani secara elektronik'), 0, 2, 'C');
        $pdf->Cell($width, 4, $pdf->encode($this->date($session->end_at, 'd F Y')), 0, 2, 'C');

        $this->drawQr($pdf, $this->signatures->validationUrl($session), $x + 16, $pdf->GetY() + 1);

        $pdf->SetXY($x, $pdf->GetY() + self::QR_SIZE + 2);
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell($width, 4, $pdf->encode($this->fullName($session->pic)), 0, 2, 'C');
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->Cell($width, 4, 'PJ Stok Opname', 0, 1, 'C');

        $pdf->SetX(10);
        $pdf->drawNote('Scan QR untuk memeriksa keaslian dokumen. Bandingkan ringkasan di halaman validasi dengan isi dokumen ini.');
    }

    private function drawQr(ReportPdfDocument $pdf, string $content, float $x, float $y): void
    {
        $matrix = $this->signatures->qrMatrix($content);
        $module = self::QR_SIZE / count($matrix);

        $pdf->SetFillColor(0, 0, 0);

        foreach ($matrix as $rowIndex => $row) {
            $runStart = null;

            // Modul hitam yang berurutan digambar satu kotak supaya PDF kecil dan tanpa garis tipis.
            foreach ([...$row, false] as $columnIndex => $dark) {
                if ($dark && $runStart === null) {
                    $runStart = $columnIndex;
                }

                if (! $dark && $runStart !== null) {
                    $pdf->Rect($x + $runStart * $module, $y + $rowIndex * $module, ($columnIndex - $runStart) * $module, $module, 'F');
                    $runStart = null;
                }
            }
        }
    }

    private function drawAttachment(ReportPdfDocument $pdf, ProductOpnameSession $session): void
    {
        $this->drawDifferenceTable(
            $pdf,
            'Lampiran 1: Tidak Sesuai, Diterima',
            $this->reviews->attachmentRows($session),
            'Tidak ada selisih yang diterima.',
        );

        $this->drawDifferenceTable(
            $pdf,
            'Lampiran 2: Tidak Sesuai, Ditolak',
            $this->reviews->rejectedRows($session),
            'Tidak ada selisih yang ditolak.',
        );
    }

    /**
     * @param  Collection<int, object>  $rows
     */
    private function drawDifferenceTable(ReportPdfDocument $pdf, string $title, Collection $rows, string $emptyMessage): void
    {
        $pdf->AddPage();
        $pdf->drawTitle($title);

        $pdf->startTable([
            ['label' => 'No', 'width' => 9, 'align' => 'L'],
            ['label' => 'Nama Barang', 'width' => 70, 'align' => 'L'],
            ['label' => 'Barcode', 'width' => 37, 'align' => 'L'],
            ['label' => 'Stok Sistem', 'width' => 19, 'align' => 'R'],
            ['label' => 'Stok SO', 'width' => 17, 'align' => 'R'],
            ['label' => 'Selisih', 'width' => 16, 'align' => 'R'],
            ['label' => 'Ket.', 'width' => 22, 'align' => 'L'],
        ], 7.5);

        foreach ($rows as $index => $row) {
            $pdf->drawRow([
                (string) ($index + 1),
                (string) $row->name,
                (string) ($row->barcode ?: $row->sku),
                (string) $row->recorded_stock,
                (string) $row->counted_stock,
                $this->signed((int) $row->difference_stock),
                $row->scanned ? 'Selisih' : 'Tidak ter-scan',
            ], $index % 2 === 1);
        }

        if ($rows->isEmpty()) {
            $pdf->drawEmptyRow($emptyMessage);
        }

        $pdf->endTable();
    }

    private function drawRevisionTable(ReportPdfDocument $pdf, ProductOpnameSession $session): void
    {
        $rows = $this->revisions->rows($session)->values();

        $pdf->AddPage();
        $pdf->drawTitle('Lampiran: Rincian Revisi');

        $pdf->startTable([
            ['label' => 'No', 'width' => 9, 'align' => 'L'],
            ['label' => 'Nama Barang', 'width' => 62, 'align' => 'L'],
            ['label' => 'Barcode', 'width' => 33, 'align' => 'L'],
            ['label' => 'Alasan', 'width' => 18, 'align' => 'L'],
            ['label' => 'Penyesuaian', 'width' => 20, 'align' => 'R'],
            ['label' => 'Catatan', 'width' => 48, 'align' => 'L'],
        ], 7.5);

        foreach ($rows as $index => $row) {
            $pdf->drawRow([
                (string) ($index + 1),
                (string) $row['name'],
                (string) ($row['barcode'] ?: $row['sku']),
                (string) $row['reason_label'],
                $this->signed($row['adjustment_qty']),
                (string) ($row['note'] ?? ''),
            ], $index % 2 === 1);
        }

        $pdf->endTable();
    }

    private function signed(int $value): string
    {
        return ($value > 0 ? '+' : '').number_format($value, 0, ',', '.');
    }

    private function date(?CarbonInterface $value, string $format): string
    {
        return $value ? $value->copy()->locale('id')->translatedFormat($format) : '-';
    }

    private function fullName(?object $employee): string
    {
        return trim(($employee->first_name ?? '').' '.($employee->last_name ?? '')) ?: '-';
    }
}
