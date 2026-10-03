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

    private const ROW_HEIGHT = 6;

    /*
     * Pengaturan logo di kop laporan (satuan milimeter).
     * - LOGO_HEIGHT   : tinggi logo; lebar mengikuti proporsi gambar.
     * - LOGO_OFFSET_Y : geser logo ke bawah (+) / ke atas (-) dari posisi nama entity.
     * - LOGO_TEXT_GAP : jarak antara logo dan teks nama entity.
     * File logo sendiri: public/assets/images/{nama-entity}.png (lihat EntityBrandingService).
     */
    private const LOGO_HEIGHT = 12;

    private const LOGO_OFFSET_Y = 3;

    private const LOGO_TEXT_GAP = 4;

    /** @var array<int, array{label: string, width: float, align: string}> */
    private array $tableColumns = [];

    private bool $repeatTableHeader = false;

    private float $fontSize = 8;

    private string $footerText = '';

    public function __construct(string $orientation)
    {
        parent::__construct($orientation === 'portrait' ? 'P' : 'L', 'mm', 'A4');

        $this->SetMargins(self::MARGIN, self::MARGIN, self::MARGIN);
        $this->SetAutoPageBreak(true, 16);
        $this->AliasNbPages();
        $this->SetTextColor(31, 41, 55);
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
     * Kop: logo, nama entity, alamat & kontak, lalu garis ganda.
     */
    public function drawLetterhead(?string $logoPath, string $name, string $address, string $contacts): void
    {
        $top = $this->GetY();
        $textX = self::MARGIN;
        $logoBottom = $top;

        if ($logoPath) {
            [$pixelWidth, $pixelHeight] = getimagesize($logoPath) ?: [1, 1];
            $logoWidth = self::LOGO_HEIGHT * $pixelWidth / max($pixelHeight, 1);
            $logoTop = $top + self::LOGO_OFFSET_Y;

            $this->Image($logoPath, self::MARGIN, $logoTop, $logoWidth, self::LOGO_HEIGHT);
            $textX = self::MARGIN + $logoWidth + self::LOGO_TEXT_GAP;
            $logoBottom = $logoTop + self::LOGO_HEIGHT;
        }

        $this->SetXY($textX, $top);
        $this->SetFont('Helvetica', 'B', 13);
        $this->Cell(0, 7, $this->encode(mb_strtoupper($name)), 0, 2);

        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(75, 85, 99);

        if ($address !== '') {
            $this->Cell(0, 4.5, $this->encode($address), 0, 2);
        }

        if ($contacts !== '') {
            $this->Cell(0, 4.5, $this->encode($contacts), 0, 2);
        }

        $this->SetTextColor(31, 41, 55);

        $lineY = max($this->GetY(), $logoBottom) + 3;
        $this->SetDrawColor(17, 24, 39);
        $this->SetLineWidth(0.7);
        $this->Line(self::MARGIN, $lineY, $this->GetPageWidth() - self::MARGIN, $lineY);
        $this->SetLineWidth(0.2);
        $this->Line(self::MARGIN, $lineY + 1, $this->GetPageWidth() - self::MARGIN, $lineY + 1);

        $this->SetY($lineY + 5);
    }

    public function drawTitle(string $title): void
    {
        $this->SetFont('Helvetica', 'B', 13);
        $this->Cell(0, 7, $this->encode(mb_strtoupper($title)), 0, 1, 'C');
        $this->Ln(2);
    }

    /**
     * @param  array<string, string>  $filters
     */
    public function drawFilters(array $filters): void
    {
        foreach ($filters as $label => $value) {
            $this->SetFont('Helvetica', '', 8);
            $this->SetTextColor(107, 114, 128);
            $this->Cell(24, 4.5, $this->encode($label));
            $this->Cell(4, 4.5, ':');
            $this->SetTextColor(31, 41, 55);
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

        $this->SetDrawColor(229, 231, 235);
        $this->SetFillColor(249, 250, 251);

        foreach ($summary as $label => $value) {
            $this->Rect($x, $y, $width, 13, 'DF');

            $this->SetXY($x + 2.5, $y + 2);
            $this->SetFont('Helvetica', '', 6.5);
            $this->SetTextColor(107, 114, 128);
            $this->Cell($width - 5, 3.5, $this->encode(mb_strtoupper($label)));

            $this->SetXY($x + 2.5, $y + 6.5);
            $this->SetFont('Helvetica', 'B', 10);
            $this->SetTextColor(31, 41, 55);
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
        $this->SetFillColor(249, 250, 251);
        $this->SetDrawColor(229, 231, 235);
        $this->SetLineWidth(0.1);

        foreach ($this->tableColumns as $index => $column) {
            $text = $this->fit($this->encode($cells[$index] ?? ''), $column['width'] - 2);
            $this->Cell($column['width'], self::ROW_HEIGHT, $text, 'B', 0, $column['align'], $striped);
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
        $this->SetFillColor(243, 244, 246);
        $this->SetDrawColor(31, 41, 55);
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
        $this->SetLineWidth(0.2);
    }

    public function drawEmptyRow(string $message): void
    {
        $this->SetFont('Helvetica', '', $this->fontSize);
        $this->SetTextColor(107, 114, 128);
        $this->SetDrawColor(229, 231, 235);
        $this->SetLineWidth(0.1);
        $this->Cell($this->contentWidth(), 12, $this->encode($message), 'B', 1, 'C');
        $this->SetTextColor(31, 41, 55);
    }

    public function drawNote(string $note): void
    {
        $this->Ln(2);
        $this->SetFont('Helvetica', 'I', 7.5);
        $this->SetTextColor(180, 83, 9);
        $this->MultiCell(0, 4, $this->encode($note));
        $this->SetTextColor(31, 41, 55);
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
        $this->SetDrawColor(209, 213, 219);
        $this->SetLineWidth(0.1);
        $this->Line(self::MARGIN, $this->GetY(), $this->GetPageWidth() - self::MARGIN, $this->GetY());

        $this->SetFont('Helvetica', '', 7);
        $this->SetTextColor(107, 114, 128);
        $this->Cell($this->contentWidth() / 2, 6, $this->encode($this->footerText));
        $this->Cell($this->contentWidth() / 2, 6, 'Halaman '.$this->PageNo().' dari {nb}', 0, 0, 'R');
        $this->SetTextColor(31, 41, 55);
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
        $this->SetFillColor(31, 41, 55);
        $this->SetTextColor(255, 255, 255);

        foreach ($this->tableColumns as $column) {
            $this->Cell($column['width'], 7, $this->fit($this->encode($column['label']), $column['width'] - 2), 0, 0, $column['align'], true);
        }

        $this->Ln();
        $this->SetTextColor(31, 41, 55);
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
