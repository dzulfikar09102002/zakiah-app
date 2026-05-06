import { useRef, useState } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
    DialogClose,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { SharedData, Option } from '@/types';
import { Spinner } from './ui/spinner';
import { toast } from 'sonner';
import products from '@/routes/products';
import { Upload, Download } from 'lucide-react';

// ─── Types ─────────────────────────────────────────────────────────────────────

/**
 * Struktur satu baris dari file Excel yang diimpor.
 *
 * Kolom kategori/lokasi/satuan berisi NAMA (bukan ID) karena pengguna
 * memilih via dropdown di Excel. Resolusi nama → ID dilakukan di buildPayload.
 *
 * Kolom stok per-lokasi: satu kolom per lokasi, e.g. "Stok - Gudang Utama".
 */
interface ImportRow {
    Nama: string;
    SKU: string;
    Barcode: string;
    Deskripsi: string;
    'Harga Jual': number;
    'Harga Beli': number;
    Kategori: string;       // nama → di-resolve ke product_category_id
    'Lokasi Input': string; // nama → di-resolve ke location_id
    Satuan: string;         // nama → di-resolve ke product_unit_id
    [stockCol: string]: string | number; // "Stok - <nama lokasi>"
}

// ─── Helpers ───────────────────────────────────────────────────────────────────

/** Nama header kolom stok — harus konsisten antara generate & parse. */
const stockColName = (locationLabel: string) => `Stok - ${locationLabel}`;

/** Konversi nama opsi ke ID-nya. Fallback 0 jika tidak ditemukan. */
function resolveId(options: Option[], name: string): number {
    const found = options.find(
        (o) => o.label.trim().toLowerCase() === String(name).trim().toLowerCase(),
    );
    return found ? (found.value as unknown as number) : 0;
}

/**
 * Generate dan unduh template .xlsx dengan dropdown validation di kolom
 * Kategori, Lokasi Input, dan Satuan menggunakan exceljs.
 *
 * Strategi dropdown:
 *   - Daftar pilihan ditulis di sheet tersembunyi "_Ref"
 *   - Named range dibuat untuk tiap kolom referensi
 *   - dataValidation type="list" mengacu pada named range tersebut
 *   Ini diperlukan karena Excel membatasi formula list validation
 *   hanya 255 karakter jika ditulis inline.
 */
async function downloadTemplate(
    locations: Option[],
    categories: Option[],
    units: Option[],
) {
    const ExcelJS = (await import('exceljs')).default;
    const wb = new ExcelJS.Workbook();

    const catLabels = categories.map((c) => c.label);
    const locLabels = locations.map((l) => l.label);
    const unitLabels = units.map((u) => u.label);

    // ── Sheet tersembunyi "_Ref" — berisi daftar pilihan ───────────────────
    const refSheet = wb.addWorksheet('_Ref');
    refSheet.state = 'veryHidden'; // tidak terlihat di tab Excel

    const maxRef = Math.max(catLabels.length, locLabels.length, unitLabels.length);
    for (let i = 0; i < maxRef; i++) {
        refSheet.getCell(i + 1, 1).value = catLabels[i] ?? null;
        refSheet.getCell(i + 1, 2).value = locLabels[i] ?? null;
        refSheet.getCell(i + 1, 3).value = unitLabels[i] ?? null;
    }

    // Named ranges supaya formula validation tetap bersih
    wb.definedNames.add(`'_Ref'!$A$1:$A$${catLabels.length}`, 'KategoriList');
    wb.definedNames.add(`'_Ref'!$B$1:$B$${locLabels.length}`, 'LokasiList');
    wb.definedNames.add(`'_Ref'!$C$1:$C$${unitLabels.length}`, 'SatuanList');

    // ── Sheet utama "Data Produk" ───────────────────────────────────────────
    const ws = wb.addWorksheet('Data Produk');

    const stockCols = locations.map((l) => stockColName(l.label));

    const columns = [
        { header: 'Nama', key: 'Nama', width: 28 },
        { header: 'SKU', key: 'SKU', width: 18 },
        { header: 'Barcode', key: 'Barcode', width: 18 },
        { header: 'Deskripsi', key: 'Deskripsi', width: 32 },
        { header: 'Harga Jual', key: 'Harga Jual', width: 16 },
        { header: 'Harga Beli', key: 'Harga Beli', width: 16 },
        { header: 'Kategori', key: 'Kategori', width: 24 },
        { header: 'Lokasi Input', key: 'Lokasi Input', width: 24 },
        { header: 'Satuan', key: 'Satuan', width: 18 },
        ...stockCols.map((col) => ({ header: col, key: col, width: Math.max(col.length + 4, 20) })),
    ];

    ws.columns = columns;

    // Style baris header
    ws.getRow(1).eachCell((cell) => {
        cell.font = { bold: true };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE2E8F0' } };
    });

    // Baris contoh
    const exampleStockCols: Record<string, number> = {};
    stockCols.forEach((col) => { exampleStockCols[col] = 0; });

    ws.addRow({
        Nama: 'Contoh Produk',
        SKU: 'SKU-001',
        Barcode: '1234567890',
        Deskripsi: 'Deskripsi produk',
        'Harga Jual': 15000,
        'Harga Beli': 10000,
        Kategori: catLabels[0] ?? '',
        'Lokasi Input': locLabels[0] ?? '',
        Satuan: unitLabels[0] ?? '',
        ...exampleStockCols,
    });

    // Kolom mana yang mendapat dropdown
    const dropdownMap: Record<string, string> = {
        Kategori: 'KategoriList',
        'Lokasi Input': 'LokasiList',
        Satuan: 'SatuanList',
    };

    // Terapkan dropdown ke baris 2–1001 (1000 baris data)
    const DATA_ROWS = 1000;
    columns.forEach((col, colIdx) => {
        const namedRange = dropdownMap[col.header as string];
        if (!namedRange) return;

        const excelCol = colIdx + 1; // 1-based
        for (let row = 2; row <= DATA_ROWS + 1; row++) {
            ws.getCell(row, excelCol).dataValidation = {
                type: 'list',
                allowBlank: true,
                formulae: [namedRange],
                showErrorMessage: true,
                errorTitle: 'Pilihan tidak valid',
                error: `Pilih salah satu dari daftar yang tersedia.`,
            };
        }
    });

    // Unduh
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], {
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'template-impor-produk.xlsx';
    a.click();
    URL.revokeObjectURL(url);
}

/**
 * Payload final yang dikirim ke server per-baris import.
 * Strukturnya identik dengan payload di ProductFormDialog.
 *
 * Nama kategori/lokasi/satuan di-resolve ke ID menggunakan daftar Option
 * yang sama yang dipakai saat generate template.
 */
function buildPayload(
    row: ImportRow,
    locations: Option[],
    categories: Option[],
    units: Option[],
) {
    const unitId = resolveId(units, row['Satuan']);

    return {
        name: row['Nama'],
        sku: row['SKU'],
        barcode: row['Barcode'],
        description: row['Deskripsi'],
        sell_price: Number(row['Harga Jual']),
        last_buying_price: Number(row['Harga Beli']),
        product_category_id: resolveId(categories, row['Kategori']),
        location_id: resolveId(locations, row['Lokasi Input']),
        product_unit_id: unitId,
        product_sell_unit_id: unitId,
        image_url: null,
        sell_to_customer: true,
        service: false,
        modifier: false,
        allow_custom_price: false,
        select_all_location: true,
        location_ids: [],
        exclude_location_ids: [],
        tax_id: 0,
        tax_setting: null,
        stock_movements: locations.map((loc) => ({
            location_id: loc.value as unknown as number,
            buying_price: Number(row['Harga Beli']),
            stock: Number(row[stockColName(loc.label)] ?? 0),
        })),
        product_unit_conversions: [],
        product_sell_prices: [],
    };
}

// ─── Component ─────────────────────────────────────────────────────────────────

interface ProductImportDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    locations: Option[];
    categories: Option[];
    units: Option[];
    onSuccess?: () => void;
}

export function ProductImportDialog({
    open,
    onOpenChange,
    locations,
    categories,
    units,
    onSuccess,
}: ProductImportDialogProps) {
    const employee_code = usePage<SharedData>().props.auth.user.employee.code;

    const { setData, post, processing } = useForm<{ products: ReturnType<typeof buildPayload>[] }>({
        products: [],
    });

    const [generating, setGenerating] = useState(false);

    const handleDownloadTemplate = async () => {
        setGenerating(true);
        try {
            await downloadTemplate(locations, categories, units);
        } finally {
            setGenerating(false);
        }
    };

    const fileInputRef = useRef<HTMLInputElement>(null);
    const [fileName, setFileName] = useState<string | null>(null);
    const [rowCount, setRowCount] = useState(0);
    const [parseError, setParseError] = useState<string | null>(null);

    /** Parse file Excel, build semua payload sekaligus, lalu simpan ke form via setData */
    const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;

        setFileName(file.name);
        setParseError(null);
        setRowCount(0);
        setData('products', []);

        try {
            const XLSX = await import('xlsx');
            const buffer = await file.arrayBuffer();
            const workbook = XLSX.read(buffer, { type: 'array' });
            const sheet = workbook.Sheets[workbook.SheetNames[1]];
            const json = XLSX.utils.sheet_to_json<ImportRow>(sheet, { defval: '' });

            if (json.length === 0) {
                setParseError('File tidak memiliki data. Pastikan baris pertama adalah header.');
                return;
            }

            const payloads = json.map((row) => buildPayload(row, locations, categories, units));
            setData('products', payloads);
            setRowCount(payloads.length);
        } catch {
            setParseError('Gagal membaca file. Pastikan format file adalah .xlsx atau .xls.');
        }
    };

    const handleSubmit = () => {
        if (rowCount === 0) return;

        post(products.import().url, {
            headers: { 'x-employee-code': employee_code },
            onSuccess: () => {
                onOpenChange(false);
                onSuccess?.();
            },
            onError: () => {
                toast.error('Import gagal. Periksa kembali data pada file.');
            },
        });
    };

    const reset = () => {
        setFileName(null);
        setRowCount(0);
        setData('products', []);
        setParseError(null);
        if (fileInputRef.current) fileInputRef.current.value = '';
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(v) => {
                if (!v) reset();
                onOpenChange(v);
            }}
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Impor Produk</DialogTitle>
                    <DialogDescription>
                        Unggah file Excel sesuai format template. Setiap baris
                        akan diproses sebagai produk baru.
                    </DialogDescription>
                </DialogHeader>

                <div className="grid gap-4 py-2">
                    {/* Download template */}
                    <div className="flex items-center justify-between rounded-lg border p-3">
                        <div className="grid gap-0.5">
                            <p className="text-sm font-medium">Template Excel</p>
                            <p className="text-xs text-muted-foreground">
                                Isi data produk mengikuti kolom pada template.
                            </p>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            type="button"
                            disabled={generating}
                            onClick={handleDownloadTemplate}
                        >
                            {generating ? <Spinner /> : <Download />}
                            Unduh Template
                        </Button>
                    </div>

                    <Separator />

                    {/* Upload file */}
                    <div className="grid gap-2">
                        <Label htmlFor="import-file">File Excel</Label>
                        <Input
                            id="import-file"
                            ref={fileInputRef}
                            type="file"
                            accept=".xlsx,.xls"
                            onChange={handleFileChange}
                        />
                        {parseError && (
                            <p className="text-xs text-destructive">{parseError}</p>
                        )}
                    </div>

                    {/* Preview jumlah baris */}
                    {rowCount > 0 && (
                        <p className="text-sm text-muted-foreground">
                            <span className="font-medium text-foreground">
                                {rowCount}
                            </span>{' '}
                            produk siap diimpor dari{' '}
                            <span className="font-medium text-foreground">
                                {fileName}
                            </span>
                            .
                        </p>
                    )}
                </div>

                <DialogFooter>
                    <DialogClose asChild>
                        <Button variant="outline" type="button" disabled={processing}>
                            Batal
                        </Button>
                    </DialogClose>
                    <Button
                        type="button"
                        onClick={handleSubmit}
                        disabled={processing || rowCount === 0}
                    >
                        {processing && <Spinner />}
                        {processing ? 'Mengimpor…' : `Impor${rowCount > 0 ? ` (${rowCount})` : ''}`}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

// ─── Trigger button ────────────────────────────────────────────────────────────

interface ProductImportButtonProps
    extends Omit<ProductImportDialogProps, 'open' | 'onOpenChange'> { }

/**
 * Tombol siap pakai yang mengontrol state open/close dialog secara internal.
 * Cukup render <ProductImportButton locations={...} categories={...} units={...} onSuccess={...} />.
 */
export function ProductImportButton(props: ProductImportButtonProps) {
    const [open, setOpen] = useState(false);

    return (
        <>
            <Button variant="outline" type="button" onClick={() => setOpen(true)}>
                <Upload />
                Impor Produk
            </Button>

            <ProductImportDialog
                {...props}
                open={open}
                onOpenChange={setOpen}
            />
        </>
    );
}