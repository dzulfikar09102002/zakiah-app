import AppLayout from '@/layouts/app-layout';
import { Head, router, usePage } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Download,
    Sheet,
    Check,
    X,
    ChevronDown,
    ChevronRight,
    AlertTriangle,
    ChevronLeft,
} from 'lucide-react';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import { ChangeEvent, DragEvent, Fragment, useRef, useState } from 'react';
import type { BreadcrumbItem, Option, SharedData } from '@/types';
import products from '@/routes/products';
import { toast } from 'sonner';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

const title = 'Impor Produk';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: products.index().url,
    },
];

const XLSX_MIME =
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

// Endpoint dan Credentials static sesuai Controller C#
const API_URL = 'https://backend-dotnet.secacastore.com/api/products/import';
const API_USERNAME = 'K9#mX2$pL7!vR4@zQ8_w';
const API_PASSWORD = 'xT5$mN8!vP2#qR9@zL4_k';

type Props = {
    categoryOptions: Option[];
    locationOptions: Option[];
    unitOptions: Option[];
    suppliers: string[];
    userId: number;
    entityId?: number;
};

interface ImportRow {
    Nama: string;
    SKU: string;
    Barcode: string;
    Deskripsi: string;
    'Harga Jual': number;
    'Harga Beli': number;
    Kategori: string;
    'Lokasi Input': string;
    Satuan: string;
    Supplier: string;
    [stockCol: string]: string | number;
}

/** sku -> location_id -> stok saat ini di sistem */
type StockMap = Record<string, Record<number, number>>;

const stockColName = (locationLabel: string) => `Stok - ${locationLabel}`;

function resolveId(options: Option[], name: string): number {
    const found = options.find(
        (o) =>
            o.label.trim().toLowerCase() === String(name).trim().toLowerCase(),
    );
    return found ? (found.value as unknown as number) : 0;
}

function parseSafeNumber(val: any): number {
    const num = Number(val);
    return isNaN(num) ? 0 : Math.max(0, num);
}

function buildPayload(
    row: ImportRow,
    locations: Option[],
    categories: Option[],
    units: Option[],
) {
    const unitId = resolveId(units, row['Satuan']);

    // 1. Tangkap teks kategori mentah dari input/Excel
    const rawCategoryName = row['Kategori']
        ? String(row['Kategori']).trim()
        : '';

    // 2. Cari ID-nya (akan bernilai 0 jika ini kategori baru)
    const categoryId = resolveId(categories, rawCategoryName);

    return {
        name: String(row['Nama'] || '').trim(),
        sku: String(row['SKU'] || '').trim(),
        barcode: String(row['Barcode'] || '').trim(),
        description: row['Deskripsi'] ? String(row['Deskripsi']) : null,
        sell_price: parseSafeNumber(row['Harga Jual']),
        last_buying_price: parseSafeNumber(row['Harga Beli']),

        product_category_id: categoryId,
        product_category_name: rawCategoryName,
        location_id: resolveId(locations, row['Lokasi Input']),
        product_unit_id: unitId,
        product_sell_unit_id: unitId,
        supplier_name: row['Supplier']?.toString().trim() ?? '',
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
        stock_movements: locations
            .map((loc) => ({
                location_id: loc.value as unknown as number,
                buying_price: parseSafeNumber(row['Harga Beli']),
                stock: parseSafeNumber(row[stockColName(loc.label)]),
            }))
            .filter((sm) => sm.stock !== 0),
        product_unit_conversions: [],
        product_sell_prices: [],
    };
}

function toRupiah(value: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
}

function isRowValid(
    row: ImportRow,
    locationOptions: Option[],
    unitOptions: Option[],
) {
    if (!row['Nama']?.toString().trim()) return false;
    if (!row['SKU']?.toString().trim()) return false;
    if (resolveId(locationOptions, row['Lokasi Input']) === 0) return false;
    if (resolveId(unitOptions, row['Satuan']) === 0) return false;
    return true;
}

function getCookie(name: string): string | null {
    const match = document.cookie.match(
        new RegExp('(?:^|; )' + name + '=([^;]*)'),
    );
    return match ? decodeURIComponent(match[1]) : null;
}

async function fetchStockMap(skus: string[]): Promise<StockMap> {
    if (skus.length === 0) return {};

    const res = await fetch('/products/import/stock-lookup', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') ?? '',
        },
        body: JSON.stringify({ skus }),
    });

    if (!res.ok) {
        throw new Error('Gagal mengambil data stok saat ini.');
    }

    return res.json();
}

export default function ImportProductPage({
    categoryOptions,
    locationOptions,
    unitOptions,
    suppliers,
    userId,
    entityId = 1,
}: Props) {
    const sharedData = usePage<SharedData>().props;
    const employee_code = sharedData.auth.user.employee.code;
    const employeeId = sharedData.auth.user.employee.id ?? 1;

    const [rows, setRows] = useState<ImportRow[]>([]);
    const [perPage, setPerPage] = useState<number | 'all'>(10);
    const [currentPage, setCurrentPage] = useState(1);

    const totalRows = rows.length;

    const totalPages =
        perPage === 'all' ? 1 : Math.max(1, Math.ceil(totalRows / perPage));

    const paginatedRows =
        perPage === 'all'
            ? rows
            : rows.slice((currentPage - 1) * perPage, currentPage * perPage);

    const handlePerPageChange = (value: string) => {
        if (value === 'all') {
            setPerPage('all');
        } else {
            setPerPage(Number(value));
        }

        setCurrentPage(1);
    };

    const inputRef = useRef<HTMLInputElement>(null);

    const [generating, setGenerating] = useState(false);
    const [saving, setSaving] = useState(false);
    const [fileName, setFileName] = useState<string | null>(null);
    const [parseError, setParseError] = useState<string | null>(null);
    const [isDragging, setIsDragging] = useState(false);

    const [previewVisible, setPreviewVisible] = useState(false);
    const [loadingReview, setLoadingReview] = useState(false);
    const [stockMap, setStockMap] = useState<StockMap>({});
    const [expandedRows, setExpandedRows] = useState<Set<number>>(new Set());

    const hasRows = rows.length > 0;

    const invalidRows = rows.filter(
        (row) => !isRowValid(row, locationOptions, unitOptions),
    );

    // ── Download Template ───────────────────────────────────────────────────
    const handleDownloadTemplate = async () => {
        if (
            !categoryOptions?.length ||
            !locationOptions?.length ||
            !unitOptions?.length
        ) {
            toast.error(
                'Data kategori/lokasi/satuan belum tersedia. Coba muat ulang halaman.',
            );
            return;
        }

        setGenerating(true);
        try {
            const ExcelJS = (await import('exceljs')).default;
            const wb = new ExcelJS.Workbook();

            const catLabels = categoryOptions.map((c) => c.label);
            const locLabels = locationOptions.map((l) => l.label);
            const unitLabels = unitOptions.map((u) => u.label);
            const supplierLabels = suppliers ?? [];
            const refSheet = wb.addWorksheet('_Ref');
            refSheet.state = 'veryHidden';

            const maxRef = Math.max(
                catLabels.length,
                locLabels.length,
                unitLabels.length,
                supplierLabels.length,
            );
            for (let i = 0; i < maxRef; i++) {
                refSheet.getCell(i + 1, 1).value = catLabels[i] ?? null;
                refSheet.getCell(i + 1, 2).value = locLabels[i] ?? null;
                refSheet.getCell(i + 1, 3).value = unitLabels[i] ?? null;
                refSheet.getCell(i + 1, 4).value = supplierLabels[i] ?? null;
            }

            wb.definedNames.add(
                `'_Ref'!$A$1:$A$${catLabels.length}`,
                'KategoriList',
            );
            wb.definedNames.add(
                `'_Ref'!$B$1:$B$${locLabels.length}`,
                'LokasiList',
            );
            wb.definedNames.add(
                `'_Ref'!$C$1:$C$${unitLabels.length}`,
                'SatuanList',
            );
            wb.definedNames.add(
                `'_Ref'!$D$1:$D$${supplierLabels.length}`,
                'SupplierList',
            );

            const ws = wb.addWorksheet('Data Produk');
            const stockCols = locationOptions.map((l) => stockColName(l.label));

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
                { header: 'Supplier', key: 'Supplier', width: 24 },
                ...stockCols.map((col) => ({
                    header: col,
                    key: col,
                    width: Math.max(col.length + 4, 20),
                })),
            ];

            ws.columns = columns;

            ws.getRow(1).eachCell((cell) => {
                cell.font = { bold: true };
                cell.fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: { argb: 'FFE2E8F0' },
                };
            });

            const exampleStockCols: Record<string, number> = {};
            stockCols.forEach((col) => {
                exampleStockCols[col] = 0;
            });

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
                Supplier: 'Zakiah',
                ...exampleStockCols,
            });

            const dropdownMap: Record<string, string> = {
                Kategori: 'KategoriList',
                'Lokasi Input': 'LokasiList',
                Satuan: 'SatuanList',
                Supplier: 'SupplierList',
            };

            const DATA_ROWS = 1000;
            columns.forEach((col, colIdx) => {
                const namedRange = dropdownMap[col.header as string];
                if (!namedRange) return;

                const excelCol = colIdx + 1;

                // --- PERUBAHAN: Tentukan kolom mana yang bebas diketik (tidak muncul error popup) ---
                const isFreeTextAllowed =
                    col.header === 'Kategori' || col.header === 'Supplier';

                for (let row = 2; row <= DATA_ROWS + 1; row++) {
                    ws.getCell(row, excelCol).dataValidation = {
                        type: 'list',
                        allowBlank: true,
                        formulae: [namedRange],
                        // Jika kolom Kategori atau Supplier, showErrorMessage di set false
                        showErrorMessage: !isFreeTextAllowed,
                        errorTitle: isFreeTextAllowed
                            ? undefined
                            : 'Pilihan tidak valid',
                        error: isFreeTextAllowed
                            ? undefined
                            : 'Pilih salah satu dari daftar yang tersedia.',
                    };
                }
            });

            const buf = await wb.xlsx.writeBuffer();
            const blob = new Blob([buf], { type: XLSX_MIME });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'template-impor-produk.xlsx';
            a.click();
            URL.revokeObjectURL(url);
        } catch (err) {
            console.error('Gagal generate template:', err);
            toast.error('Gagal membuat template. Coba lagi.');
        } finally {
            setGenerating(false);
        }
    };

    // ── Upload & Parse ──────────────────────────────────────────────────────
    const processFile = async (file: File) => {
        setParseError(null);
        setRows([]);

        if (file.type !== XLSX_MIME) {
            setParseError('File harus berformat .xlsx');
            setFileName(null);
            return;
        }

        setFileName(file.name);

        try {
            const XLSX = await import('xlsx');
            const buffer = await file.arrayBuffer();
            const workbook = XLSX.read(buffer, { type: 'array' });
            const sheet =
                workbook.Sheets[workbook.SheetNames[1]] ??
                workbook.Sheets[workbook.SheetNames[0]];
            const json = XLSX.utils.sheet_to_json<ImportRow>(sheet, {
                defval: '',
            });

            if (json.length === 0) {
                setParseError(
                    'File tidak memiliki data. Pastikan baris pertama adalah header.',
                );
                return;
            }

            setRows(json);
            setCurrentPage(1);
        } catch {
            setParseError(
                'Gagal membaca file. Pastikan format file sesuai template.',
            );
        }
    };

    const handleFileChange = (e: ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;
        processFile(file);
    };

    const handleDrop = (e: DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        setIsDragging(false);
        const file = e.dataTransfer.files?.[0];
        if (!file) return;
        processFile(file);
    };

    // ── Klik "Tinjau & Import" ──────────────────────────────────────────────
    const handleTinjau = async () => {
        if (rows.length === 0) return;

        setLoadingReview(true);
        try {
            const skus = rows
                .map((r) => r['SKU']?.toString().trim())
                .filter((sku): sku is string => Boolean(sku));

            const map = await fetchStockMap(skus);
            setStockMap(map);

            setExpandedRows(new Set());
            setPreviewVisible(true);
        } catch (err) {
            console.error(err);
            toast.error('Gagal memuat data stok saat ini. Coba lagi.');
        } finally {
            setLoadingReview(false);
        }
    };

    // ── Reset ───────────────────────────────────────────────────────────────
    const handleBatalkan = () => {
        setRows([]);
        setFileName(null);
        setParseError(null);
        setStockMap({});
        setExpandedRows(new Set());
        setPreviewVisible(false);
        if (inputRef.current) inputRef.current.value = '';
        setCurrentPage(1);
    };

    const handleGantiFile = () => {
        setRows([]);
        setFileName(null);
        setParseError(null);
        if (inputRef.current) inputRef.current.value = '';
        setCurrentPage(1);
    };

    const toggleExpand = (idx: number) => {
        setExpandedRows((prev) => {
            const next = new Set(prev);
            if (next.has(idx)) {
                next.delete(idx);
            } else {
                next.add(idx);
            }
            return next;
        });
    };

    const totalStokSetelahImport = (row: ImportRow) => {
        const sku = row['SKU']?.toString().trim();
        const currentBySku = sku ? (stockMap[sku] ?? {}) : {};

        return locationOptions.reduce((sum, loc) => {
            const locId = loc.value as unknown as number;
            const stokSekarang = Number(currentBySku[locId] ?? 0);
            const stokBaru = Number(row[stockColName(loc.label)] ?? 0);
            return sum + stokSekarang + stokBaru;
        }, 0);
    };
    const [importSuccess, setImportSuccess] = useState(false);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const handleKlikSimpan = () => {
        if (rows.length === 0) return;

        if (invalidRows.length > 0) {
            toast.error(
                `${invalidRows.length} produk memiliki data tidak valid (Lokasi/Satuan tidak dikenal). Periksa kembali sebelum menyimpan.`,
            );
            return;
        }

        setConfirmOpen(true);
    };
    const handleSimpan = async () => {
        setConfirmOpen(false);

        const payloads = rows.map((row) =>
            buildPayload(row, locationOptions, categoryOptions, unitOptions),
        );

        const requestData = {
            entity_id: entityId || 1,
            employee_id: employeeId || 1,
            user_id: userId || 1,
            products: payloads,
        };

        const basicAuthToken = btoa(`${API_USERNAME}:${API_PASSWORD}`);

        setSaving(true);

        try {
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    Authorization: `Basic ${basicAuthToken}`,
                    'x-employee-code': employee_code ?? '',
                },
                body: JSON.stringify(requestData),
            });

            const result = await res.json().catch(() => null);

            if (res.ok && result?.success) {
                toast.success(
                    result.message || `${rows.length} produk berhasil diimpor.`,
                );

                setImportSuccess(true);

                setTimeout(() => {
                    router.visit(products.index().url);
                }, 1500);
            } else {
                const errorMessage =
                    result?.message ||
                    'Terjadi kesalahan saat memproses impor produk.';
                toast.error(errorMessage);
            }
        } catch (err) {
            console.error('Fetch error:', err);
            toast.error('Gagal terhubung ke server.');
        } finally {
            setSaving(false);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />
            <div className="mx-auto flex max-w-7xl flex-col items-center gap-6 py-10">
                {!previewVisible && (
                    <Card className="w-[520px]">
                        <CardContent className="p-6">
                            <div className="mb-6">
                                <h1 className="text-2xl font-semibold">
                                    Import Produk
                                </h1>

                                <p className="mt-2 text-muted-foreground">
                                    Menambahkan banyak data produk dengan lebih
                                    mudah.
                                </p>

                                <div className="mt-6 text-justify leading-7 text-muted-foreground">
                                    Silahkan Unduh Template terlebih dahulu dan
                                    perhatikan untuk mengisi data sesuai
                                    ketentuan untuk memastikan data dapat dibaca
                                    dengan benar.
                                </div>
                            </div>

                            <div className="mb-4 flex items-center justify-between rounded-lg border p-3">
                                <div className="grid gap-0.5">
                                    <p className="text-sm font-medium">
                                        Template Excel
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Isi data produk mengikuti kolom pada
                                        template.
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

                            <div className="mt-4 space-y-4">
                                {!hasRows ? (
                                    <div
                                        className={`flex cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed px-8 py-10 transition ${
                                            isDragging
                                                ? 'border-primary bg-muted/40'
                                                : 'border-primary hover:bg-muted/30'
                                        }`}
                                        onClick={() =>
                                            inputRef.current?.click()
                                        }
                                        onDragOver={(e) => {
                                            e.preventDefault();
                                            setIsDragging(true);
                                        }}
                                        onDragLeave={() => setIsDragging(false)}
                                        onDrop={handleDrop}
                                    >
                                        <Sheet className="mb-4 h-10 w-10 text-gray-700" />

                                        <div className="text-center">
                                            <span className="font-medium text-primary">
                                                Click to replace
                                            </span>{' '}
                                            <span className="text-gray-500">
                                                or drag and drop
                                            </span>
                                        </div>

                                        <p className="mt-1 text-sm text-gray-500">
                                            xlsx
                                        </p>

                                        <input
                                            ref={inputRef}
                                            type="file"
                                            accept=".xlsx"
                                            className="hidden"
                                            onChange={handleFileChange}
                                        />
                                    </div>
                                ) : (
                                    <div className="space-y-3">
                                        <div className="flex items-center justify-between rounded-lg border p-3">
                                            <div className="grid gap-0.5">
                                                <p className="text-sm font-medium">
                                                    {fileName}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {rows.length} produk terbaca
                                                    dari file
                                                </p>
                                            </div>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                type="button"
                                                onClick={handleGantiFile}
                                            >
                                                Ganti File
                                            </Button>
                                        </div>

                                        <Button
                                            className="w-full"
                                            type="button"
                                            disabled={loadingReview}
                                            onClick={handleTinjau}
                                        >
                                            {loadingReview ? (
                                                <Spinner />
                                            ) : (
                                                <Check />
                                            )}
                                            {loadingReview
                                                ? 'Memuat…'
                                                : 'Pratinjau Produk'}
                                        </Button>
                                    </div>
                                )}

                                {parseError && (
                                    <p className="text-sm text-destructive">
                                        {parseError}
                                    </p>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
            {previewVisible && (
                <Card>
                    <CardContent className="p-6">
                        <div className="mb-4 flex items-center justify-between">
                            <div>
                                <h2 className="text-lg font-semibold">
                                    Pratinjau Produk yang Akan Diimpor
                                </h2>
                                <p className="text-sm text-muted-foreground">
                                    Periksa kembali data di bawah sebelum
                                    menyimpan. Klik ikon panah untuk melihat
                                    rincian stok per lokasi.
                                </p>
                            </div>
                        </div>

                        {invalidRows.length > 0 && (
                            <div className="mb-4 flex items-center gap-2 rounded-md border border-destructive/30 bg-destructive/5 p-3 text-sm text-destructive">
                                <AlertTriangle className="h-4 w-4 shrink-0" />
                                {invalidRows.length} dari {rows.length} produk
                                memiliki Lokasi/Satuan yang tidak dikenal.
                                Perbaiki file Excel lalu unggah ulang sebelum
                                menyimpan.
                            </div>
                        )}

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-10" />
                                    <TableHead>No.</TableHead>
                                    <TableHead>Nama</TableHead>
                                    <TableHead>Kategori</TableHead>
                                    <TableHead>SKU</TableHead>
                                    <TableHead>Barcode</TableHead>
                                    <TableHead>Harga Beli</TableHead>
                                    <TableHead>Harga Jual</TableHead>
                                    <TableHead>Total Stok</TableHead>
                                    <TableHead>Supplier</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {paginatedRows.map((row, index) => {
                                    const idx =
                                        perPage === 'all'
                                            ? index
                                            : (currentPage - 1) *
                                                  (perPage as number) +
                                              index;
                                    const kategoriValid = !!row['Kategori']
                                        ?.toString()
                                        .trim();
                                    const lokasiValid =
                                        resolveId(
                                            locationOptions,
                                            row['Lokasi Input'],
                                        ) !== 0;

                                    const satuanValid =
                                        resolveId(
                                            unitOptions,
                                            row['Satuan'],
                                        ) !== 0;

                                    const isExpanded = expandedRows.has(idx);
                                    const sku = row['SKU']?.toString().trim();
                                    const currentBySku = sku
                                        ? (stockMap[sku] ?? {})
                                        : {};

                                    return (
                                        /* Wajib menggunakan Fragment key={...} sebagai elemen terluar di map */
                                        <Fragment
                                            key={`row-group-${idx}-${row['SKU'] || index}`}
                                        >
                                            <TableRow>
                                                <TableCell>
                                                    <button
                                                        type="button"
                                                        className="text-muted-foreground hover:text-foreground"
                                                        onClick={() =>
                                                            toggleExpand(idx)
                                                        }
                                                        title="Lihat rincian stok per lokasi"
                                                    >
                                                        {isExpanded ? (
                                                            <ChevronDown className="h-4 w-4" />
                                                        ) : (
                                                            <ChevronRight className="h-4 w-4" />
                                                        )}
                                                    </button>
                                                </TableCell>
                                                <TableCell>
                                                    {idx + 1}.
                                                </TableCell>
                                                <TableCell>
                                                    {row['Nama']}
                                                </TableCell>
                                                <TableCell>
                                                    {kategoriValid ? (
                                                        <Badge variant="secondary">
                                                            {row['Kategori']}
                                                        </Badge>
                                                    ) : (
                                                        <Badge variant="destructive">
                                                            {row['Kategori'] ||
                                                                '-'}{' '}
                                                            (tidak dikenal)
                                                        </Badge>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    {row['SKU']}
                                                </TableCell>
                                                <TableCell>
                                                    {row['Barcode']}
                                                </TableCell>
                                                <TableCell>
                                                    {toRupiah(
                                                        Number(
                                                            row['Harga Beli'],
                                                        ),
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    {toRupiah(
                                                        Number(
                                                            row['Harga Jual'],
                                                        ),
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    {totalStokSetelahImport(
                                                        row,
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    {row['Supplier']}
                                                </TableCell>
                                            </TableRow>

                                            {isExpanded && (
                                                <TableRow>
                                                    <TableCell
                                                        colSpan={9}
                                                        className="bg-muted/30 p-0"
                                                    >
                                                        <div className="p-4">
                                                            {!lokasiValid ||
                                                            !satuanValid ? (
                                                                <p className="mb-2 text-xs text-destructive">
                                                                    {!lokasiValid &&
                                                                        'Lokasi Input tidak dikenal. '}
                                                                    {!satuanValid &&
                                                                        'Satuan tidak dikenal.'}
                                                                </p>
                                                            ) : null}

                                                            <Table>
                                                                <TableHeader>
                                                                    <TableRow>
                                                                        <TableHead>
                                                                            Nama
                                                                            Lokasi
                                                                        </TableHead>
                                                                        <TableHead>
                                                                            Stok
                                                                            Sekarang
                                                                        </TableHead>
                                                                        <TableHead>
                                                                            Stok
                                                                            Akan
                                                                            Diinput
                                                                        </TableHead>
                                                                        <TableHead>
                                                                            Total
                                                                            Stok
                                                                        </TableHead>
                                                                    </TableRow>
                                                                </TableHeader>
                                                                <TableBody>
                                                                    {locationOptions.map(
                                                                        (
                                                                            loc,
                                                                        ) => {
                                                                            const locId =
                                                                                loc.value as unknown as number;
                                                                            const stokSekarang =
                                                                                Number(
                                                                                    currentBySku[
                                                                                        locId
                                                                                    ] ??
                                                                                        0,
                                                                                );
                                                                            const stokBaru =
                                                                                Number(
                                                                                    row[
                                                                                        stockColName(
                                                                                            loc.label,
                                                                                        )
                                                                                    ] ??
                                                                                        0,
                                                                                );

                                                                            return (
                                                                                <TableRow
                                                                                    key={`loc-${loc.value}`}
                                                                                >
                                                                                    <TableCell>
                                                                                        {
                                                                                            loc.label
                                                                                        }
                                                                                    </TableCell>
                                                                                    <TableCell>
                                                                                        {
                                                                                            stokSekarang
                                                                                        }
                                                                                    </TableCell>
                                                                                    <TableCell>
                                                                                        {
                                                                                            stokBaru
                                                                                        }
                                                                                    </TableCell>
                                                                                    <TableCell className="font-medium">
                                                                                        {stokSekarang +
                                                                                            stokBaru}
                                                                                    </TableCell>
                                                                                </TableRow>
                                                                            );
                                                                        },
                                                                    )}
                                                                </TableBody>
                                                            </Table>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            )}
                                        </Fragment>
                                    );
                                })}
                            </TableBody>
                        </Table>
                        <div className="mt-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div className="flex items-center gap-2 text-sm">
                                <span>Menampilkan</span>

                                <Select
                                    value={String(perPage)}
                                    onValueChange={handlePerPageChange}
                                >
                                    <SelectTrigger className="w-28">
                                        <SelectValue />
                                    </SelectTrigger>

                                    <SelectContent>
                                        <SelectItem value="10">10</SelectItem>
                                        <SelectItem value="25">25</SelectItem>
                                        <SelectItem value="50">50</SelectItem>
                                        <SelectItem value="100">100</SelectItem>
                                        <SelectItem value="all">
                                            Semua
                                        </SelectItem>
                                    </SelectContent>
                                </Select>

                                <span>
                                    dari <b>{rows.length}</b> baris data
                                </span>
                            </div>

                            <div className="flex items-center gap-2">
                                <Button
                                    size="icon"
                                    variant="outline"
                                    disabled={
                                        currentPage === 1 || perPage === 'all'
                                    }
                                    onClick={() =>
                                        setCurrentPage((p) =>
                                            Math.max(1, p - 1),
                                        )
                                    }
                                >
                                    <ChevronLeft className="h-4 w-4" />
                                </Button>

                                <Select
                                    value={String(currentPage)}
                                    onValueChange={(value) =>
                                        setCurrentPage(Number(value))
                                    }
                                    disabled={perPage === 'all'}
                                >
                                    <SelectTrigger className="w-36">
                                        <SelectValue />
                                    </SelectTrigger>

                                    <SelectContent>
                                        {Array.from(
                                            { length: totalPages },
                                            (_, i) => i + 1,
                                        ).map((page) => (
                                            <SelectItem
                                                key={page}
                                                value={String(page)}
                                            >
                                                {page}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                <Button
                                    size="icon"
                                    variant="outline"
                                    disabled={
                                        currentPage === totalPages ||
                                        perPage === 'all'
                                    }
                                    onClick={() =>
                                        setCurrentPage((p) =>
                                            Math.min(totalPages, p + 1),
                                        )
                                    }
                                >
                                    <ChevronRight className="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                        {!importSuccess && (
                            <div className="mt-6 flex justify-end gap-2">
                                <Button
                                    variant="destructive"
                                    type="button"
                                    disabled={saving}
                                    onClick={handleBatalkan}
                                >
                                    <X /> Batalkan
                                </Button>

                                <Button
                                    type="button"
                                    disabled={
                                        saving ||
                                        rows.length === 0 ||
                                        invalidRows.length > 0
                                    }
                                    onClick={handleKlikSimpan}
                                >
                                    {saving ? <Spinner /> : <Check />}
                                    {saving
                                        ? 'Menyimpan…'
                                        : `Simpan (${rows.length})`}
                                </Button>
                            </div>
                        )}
                    </CardContent>
                </Card>
            )}
            <AlertDialog open={confirmOpen} onOpenChange={setConfirmOpen}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            Konfirmasi Impor Produk
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            Anda akan mengimpor <b>{rows.length}</b> produk ke
                            dalam sistem. Data yang sudah tersimpan tidak dapat
                            dibatalkan secara otomatis. Apakah Anda yakin ingin
                            melanjutkan?
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel className="border-destructive text-destructive hover:bg-destructive/10 hover:text-destructive">
                            Batal
                        </AlertDialogCancel>
                        <AlertDialogAction onClick={handleSimpan}>
                            Ya, Simpan
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}
