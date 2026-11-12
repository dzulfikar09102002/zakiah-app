<?php

namespace App\Services;

use FPDF;

/**
 * Dokumen PDF laporan berbasis FPDF: tabel digambar langsung (tanpa parsing HTML)
 * sehingga cepat dan hemat memori untuk ribuan baris.
 *
 * Satuan dalam milimeter. Teks harus sudah dikonversi ke Windows-1252 lewat encode().
 */
class ReportPdfDocument extends FPDF
{
    private const MARGIN = 10;

    /*
     * Palet warna per tema entity (sama dengan token app.css, lihat EntityBrandingService::theme):
     * text = teks utama, muted = keterangan, primary = nama kop, judul, garis kop & header tabel,
     * accent = garis aksen tipis, head = latar header & total tabel,
     * stripe = baris genap & kotak ringkasan, border = garis antar baris.
     * Latar dibuat sangat muda agar hemat tinta saat dicetak.
     *
     * @var array<string, array<string, array{int, int, int}>>
     */
    private const PALETTES = [
        'zakiah' => [
            'text' => [31, 42, 60],         // #1f2a3c
            'muted' => [95, 107, 128],      // #5f6b80
            'primary' => [47, 62, 86],      // #2f3e56 navy
            'accent' => [201, 160, 102],    // #c9a066 emas
            'head' => [236, 239, 245],      // #eceff5
            'stripe' => [248, 249, 251],    // #f8f9fb
            'border' => [223, 227, 234],    // #dfe3ea
        ],
        'secaca' => [
            'text' => [29, 45, 51],         // #1d2d33
            'muted' => [90, 110, 116],      // #5a6e74
            'primary' => [31, 127, 140],    // #1f7f8c teal
            'accent' => [229, 84, 90],      // #e5545a coral
            'head' => [230, 243, 245],      // #e6f3f5
            'stripe' => [246, 251, 251],    // #f6fbfb
            'border' => [214, 232, 235],    // #d6e8eb
        ],
    ];

    /** @var array<string, array{int, int, int}> */
    private array $palette;

    private const ROW_HEIGHT = 6;

    private const DETAIL_ROW_HEIGHT = 5;

    /*
     * Pengaturan logo di kop laporan (satuan milimeter), sama dengan laporan-cetak.css.
     * - LOGO_MAX_WIDTH / LOGO_MAX_HEIGHT : logo diperbesar/diperkecil agar muat di kotak ini
     *   dengan proporsi tetap (object-fit: contain); logo & teks kop sejajar di tengah.
     * - LOGO_TEXT_GAP : jarak antara logo dan teks nama entity.
     * File logo sendiri: public/assets/images/{nama-entity}.png (lihat EntityBrandingService).
     */
    private const LOGO_MAX_WIDTH = 22;

    private const LOGO_MAX_HEIGHT = 16;

    private const LOGO_TEXT_GAP = 3.5;

    /** @var array<int, array{label: string, width: float, align: string}> */
    private array $tableColumns = [];

    private bool $repeatTableHeader = false;

    private float $fontSize = 8;

    private string $footerText = '';

    /**
     * @param  string  $theme  Tema entity: "zakiah" (default) atau "secaca".
     */
    public function __construct(string $orientation, string $theme = 'zakiah')
    {
        parent::__construct($orientation === 'portrait' ? 'P' : 'L', 'mm', 'A4');

        $this->palette = self::PALETTES[$theme] ?? self::PALETTES['zakiah'];

        $this->SetMargins(self::MARGIN, self::MARGIN, self::MARGIN);
        $this->SetAutoPageBreak(true, 16);
        $this->AliasNbPages();
        $this->SetTextColor(...$this->palette['text']);
    }

    /**
     * Warna palet aktif (RGB), mis. untuk bagian laporan yang digambar di luar kelas ini.
     *
     * @return array{int, int, int}
     */
    public function color(string $name): array
    {
        return $this->palette[$name] ?? $this->palette['text'];
    }

    public function contentWidth(): float
    {
        return $this->GetPageWidth() - self::MARGIN * 2;
    }

    public function setFooterText(string $text): void
    {
        $this->footerText = $text;
    }

    /**
     * Kop: logo, nama entity, baris keterangan (alamat, kode pos, telepon; hanya yang terisi),
     * lalu garis tebal.
     *
     * @param  array<int, string>  $lines
     */
    public function drawLetterhead(?string $logoPath, string $name, array $lines): void
    {
        $top = $this->GetY();
        $textX = self::MARGIN;
        $textHeight = 7 + 4 * count($lines);
        $blockHeight = $textHeight;

        if ($logoPath) {
            [$pixelWidth, $pixelHeight] = getimagesize($logoPath) ?: [1, 1];
            $ratio = $pixelWidth / max($pixelHeight, 1);
            $logoHeight = min(self::LOGO_MAX_HEIGHT, self::LOGO_MAX_WIDTH / $ratio);
            $logoWidth = $logoHeight * $ratio;
            $blockHeight = max($textHeight, $logoHeight);

            $this->Image($logoPath, self::MARGIN, $top + ($blockHeight - $logoHeight) / 2, $logoWidth, $logoHeight);
            $textX = self::MARGIN + $logoWidth + self::LOGO_TEXT_GAP;
        }

        $this->SetXY($textX, $top + ($blockHeight - $textHeight) / 2);
        $this->SetFont('Helvetica', 'B', 12);
        $this->SetTextColor(...$this->palette['primary']);
        $this->Cell(0, 7, $this->encode(mb_strtoupper($name)), 0, 2);

        $this->SetFont('Helvetica', '', 7.5);
        $this->SetTextColor(...$this->palette['muted']);

        foreach ($lines as $line) {
            $this->Cell(0, 4, $this->encode($line), 0, 2);
        }

        $this->SetTextColor(...$this->palette['text']);

        // Garis kop ganda: tebal warna utama, lalu garis aksen tipis (emas Zakiah / coral Secaca).
        $lineY = $top + $blockHeight + 2;
        $right = $this->GetPageWidth() - self::MARGIN;
        $this->SetDrawColor(...$this->palette['primary']);
        $this->SetLineWidth(0.6);
        $this->Line(self::MARGIN, $lineY, $right, $lineY);
        $this->SetDrawColor(...$this->palette['accent']);
        $this->SetLineWidth(0.3);
        $this->Line(self::MARGIN, $lineY + 0.9, $right, $lineY + 0.9);
        $this->SetLineWidth(0.2);

        $this->SetY($lineY + 4.5);
    }

    public function drawTitle(string $title): void
    {
        $this->SetFont('Helvetica', 'B', 11);
        $this->SetTextColor(...$this->palette['primary']);
        $this->Cell(0, 7, $this->encode(mb_strtoupper($title)), 0, 1, 'C');
        $this->SetTextColor(...$this->palette['text']);

        // Ornamen kecil di bawah judul: garis aksen pendek di tengah.
        $center = $this->GetPageWidth() / 2;
        $this->SetDrawColor(...$this->palette['accent']);
        $this->SetLineWidth(0.5);
        $this->Line($center - 9, $this->GetY(), $center + 9, $this->GetY());
        $this->SetLineWidth(0.2);
        $this->Ln(2.5);
    }

    /**
     * @param  array<string, string>  $filters
     */
    public function drawFilters(array $filters): void
    {
        foreach ($filters as $label => $value) {
            $this->SetFont('Helvetica', '', 8);
            $this->SetTextColor(...$this->palette['muted']);
            $this->Cell(24, 4.5, $this->encode($label));
            $this->Cell(4, 4.5, ':');
            $this->SetTextColor(...$this->palette['text']);
            $this->MultiCell(0, 4.5, $this->encode($value));
        }

        $this->Ln(3);
    }

    /**
     * Kotak ringkasan sejajar (label kecil + nilai tebal).
     *
     * @param  array<string, string>  $summary
     */
    public function drawSummary(array $summary): void
    {
        $gap = 3;
        $count = count($summary);
        $width = ($this->contentWidth() - $gap * ($count - 1)) / $count;
        $y = $this->GetY();
        $x = self::MARGIN;

        $this->SetDrawColor(...$this->palette['border']);
        $this->SetFillColor(...$this->palette['stripe']);

        foreach ($summary as $label => $value) {
            $this->Rect($x, $y, $width, 13, 'DF');
            // Strip warna utama di sisi kiri kotak ringkasan.
            $this->SetFillColor(...$this->palette['primary']);
            $this->Rect($x, $y, 1, 13, 'F');
            $this->SetFillColor(...$this->palette['stripe']);

            $this->SetXY($x + 3, $y + 2);
            $this->SetFont('Helvetica', '', 6.5);
            $this->SetTextColor(...$this->palette['muted']);
            $this->Cell($width - 5, 3.5, $this->encode(mb_strtoupper($label)));

            $this->SetXY($x + 3, $y + 6.5);
            $this->SetFont('Helvetica', 'B', 10);
            $this->SetTextColor(...$this->palette['text']);
            $this->Cell($width - 5, 5, $this->fit($this->encode($value), $width - 5));

            $x += $width + $gap;
        }

        $this->SetY($y + 13 + 4);
    }

    /**
     * @param  array<int, array{label: string, width: float, align: string}>  $columns
     */
    public function startTable(array $columns, float $fontSize): void
    {
        $this->tableColumns = $columns;
        $this->fontSize = $fontSize;
        $this->repeatTableHeader = true;
        $this->drawTableHeader();
    }

    public function endTable(): void
    {
        $this->repeatTableHeader = false;
    }

    /**
     * @param  array<int, string>  $cells  Teks yang sudah diformat (UTF-8).
     */
    public function drawRow(array $cells, bool $striped): void
    {
        $this->ensureSpace(self::ROW_HEIGHT);

        $this->SetFont('Helvetica', '', $this->fontSize);
        $this->SetFillColor(...$this->palette['stripe']);
        $this->SetDrawColor(...$this->palette['border']);
        $this->SetLineWidth(0.1);

        foreach ($this->tableColumns as $index => $column) {
            $text = $this->fit($this->encode($cells[$index] ?? ''), $column['width'] - 2);
            $this->Cell($column['width'], self::ROW_HEIGHT, $text, 'B', 0, $column['align'], $striped);
        }

        $this->Ln();
    }

    /**
     * Baris master pada tabel master-detail: tebal dengan latar, agar terpisah dari detailnya.
     *
     * @param  array<int, string>  $cells
     */
    public function drawMasterRow(array $cells, int $detailCount): void
    {
        // Master tidak boleh sendirian di bawah halaman tanpa baris detail pertamanya.
        $this->ensureSpace(self::ROW_HEIGHT + ($detailCount > 0 ? self::DETAIL_ROW_HEIGHT : 0));

        $this->SetFont('Helvetica', 'B', $this->fontSize);
        $this->SetFillColor(...$this->palette['stripe']);
        $this->SetDrawColor(...$this->palette['border']);
        $this->SetLineWidth(0.1);

        foreach ($this->tableColumns as $index => $column) {
            $text = $this->fit($this->encode($cells[$index] ?? ''), $column['width'] - 2);
            $this->Cell($column['width'], self::ROW_HEIGHT, $text, 'T', 0, $column['align'], true);
        }

        $this->Ln();
    }

    /**
     * Baris detail di bawah master: label menjorok selebar $span kolom pertama,
     * nilai sisanya mengikuti kolom master.
     *
     * @param  array<int, string>  $cells  Nilai untuk kolom ke-$span dan seterusnya.
     */
    public function drawDetailRow(string $label, array $cells, int $span): void
    {
        $this->ensureSpace(self::DETAIL_ROW_HEIGHT);

        $this->SetFont('Helvetica', '', $this->fontSize);
        $labelWidth = array_sum(array_column(array_slice($this->tableColumns, 0, $span), 'width'));
        $indent = $this->tableColumns[0]['width'] ?? 0;

        $this->SetTextColor(...$this->palette['muted']);
        $this->Cell($indent, self::DETAIL_ROW_HEIGHT, '');
        $this->Cell($labelWidth - $indent, self::DETAIL_ROW_HEIGHT, $this->fit($this->encode($label), $labelWidth - $indent - 2));
        $this->SetTextColor(...$this->palette['text']);

        foreach (array_slice($this->tableColumns, $span) as $index => $column) {
            $text = $this->fit($this->encode($cells[$index] ?? ''), $column['width'] - 2);
            $this->Cell($column['width'], self::DETAIL_ROW_HEIGHT, $text, 0, 0, $column['align']);
        }

        $this->Ln();
    }

    /**
     * @param  array<int, string>  $cells
     */
    public function drawTotalRow(array $cells): void
    {
        $this->ensureSpace(self::ROW_HEIGHT + 1);

        $this->SetFont('Helvetica', 'B', $this->fontSize);
        $this->SetFillColor(...$this->palette['head']);
        $this->SetDrawColor(...$this->palette['primary']);
        $this->SetTextColor(...$this->palette['primary']);
        $this->SetLineWidth(0.3);

        foreach ($this->tableColumns as $index => $column) {
            $text = $this->encode($cells[$index] ?? '');

            // Angka total bisa lebih panjang dari baris data: kecilkan font, jangan dipotong.
            $size = $this->fontSize;
            $this->SetFontSize($size);
            while ($size > 5 && $this->GetStringWidth($text) > $column['width'] - 2) {
                $size -= 0.5;
                $this->SetFontSize($size);
            }

            $this->Cell($column['width'], self::ROW_HEIGHT + 1, $this->fit($text, $column['width'] - 2), 'TB', 0, $column['align'], true);
        }

        $this->SetFontSize($this->fontSize);

        $this->Ln();
        $this->SetTextColor(...$this->palette['text']);
        $this->SetLineWidth(0.2);
    }

    public function drawEmptyRow(string $message): void
    {
        $this->SetFont('Helvetica', '', $this->fontSize);
        $this->SetTextColor(...$this->palette['muted']);
        $this->SetDrawColor(...$this->palette['border']);
        $this->SetLineWidth(0.1);
        $this->Cell($this->contentWidth(), 12, $this->encode($message), 'B', 1, 'C');
        $this->SetTextColor(...$this->palette['text']);
    }

    public function drawNote(string $note): void
    {
        $this->Ln(2);
        $this->SetFont('Helvetica', 'I', 7.5);
        $this->SetTextColor(...$this->palette['muted']);
        $this->MultiCell(0, 4, $this->encode($note));
        $this->SetTextColor(...$this->palette['text']);
    }

    public function Header(): void
    {
        if ($this->repeatTableHeader && $this->PageNo() > 1) {
            $this->drawTableHeader();
        }
    }

    public function Footer(): void
    {
        $this->SetY(-12);
        $this->SetDrawColor(...$this->palette['border']);
        $this->SetLineWidth(0.1);
        $this->Line(self::MARGIN, $this->GetY(), $this->GetPageWidth() - self::MARGIN, $this->GetY());

        $this->SetFont('Helvetica', '', 7);
        $this->SetTextColor(...$this->palette['muted']);
        $this->Cell($this->contentWidth() / 2, 6, $this->encode($this->footerText));
        $this->Cell($this->contentWidth() / 2, 6, 'Halaman '.$this->PageNo().' dari {nb}', 0, 0, 'R');
        $this->SetTextColor(...$this->palette['text']);
    }

    /**
     * Konversi UTF-8 ke Windows-1252 (encoding font inti FPDF).
     */
    public function encode(string $value): string
    {
        $converted = @iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $value);

        return $converted === false ? $value : $converted;
    }

    public function stringWidth(string $value, string $style = ''): float
    {
        $this->SetFont('Helvetica', $style, $this->fontSize);

        return $this->GetStringWidth($this->encode($value));
    }

    public function setTableFontSize(float $fontSize): void
    {
        $this->fontSize = $fontSize;
    }

    private function drawTableHeader(): void
    {
        $this->SetFont('Helvetica', 'B', $this->fontSize);
        $this->SetFillColor(...$this->palette['head']);
        $this->SetDrawColor(...$this->palette['primary']);
        $this->SetTextColor(...$this->palette['primary']);
        $this->SetLineWidth(0.3);

        foreach ($this->tableColumns as $column) {
            $this->Cell($column['width'], 7, $this->fit($this->encode($column['label']), $column['width'] - 2), 'TB', 0, $column['align'], true);
        }

        $this->Ln();
        $this->SetTextColor(...$this->palette['text']);
        $this->SetLineWidth(0.2);
    }

    /**
     * Pindah halaman manual agar baris tidak terpotong; header tabel otomatis diulang lewat Header().
     */
    private function ensureSpace(float $height): void
    {
        if ($this->GetY() + $height > $this->GetPageHeight() - 16) {
            $this->AddPage($this->CurOrientation);
        }
    }

    /**
     * Potong teks dengan "..." bila melebihi lebar sel.
     */
    private function fit(string $text, float $width): string
    {
        if ($this->GetStringWidth($text) <= $width) {
            return $text;
        }

        while ($text !== '' && $this->GetStringWidth($text.'...') > $width) {
            $text = substr($text, 0, -1);
        }

        return $text.'...';
    }
}
