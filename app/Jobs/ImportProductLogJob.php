<?php

namespace App\Jobs;

use App\Helpers\UniqueCodeGenerator;
use App\Models\Entity;
use App\Models\Employee;
use App\Models\Location;
use App\Models\OrderType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImportService;
use App\Models\ProductImportServiceDetail;
use App\Models\ProductUnit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class ImportProductLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    /** Folder penyimpanan snapshot file import, di-scope per entity di dalam handle(). */
    private const STORAGE_DIRECTORY = 'public/productImport';

    /** Maksimal file snapshot yang disimpan per entity. Lebih dari ini, yang paling lama dihapus. */
    private const FILE_RETENTION_LIMIT = 10;

    /**
     * @param  int  $entityId
     * @param  int  $employeeId  ID Employee (konteks POS) yang melakukan import.
     * @param  int  $userId      ID User (akun auth) yang melakukan import — dipakai untuk
     *                           created_by/updated_by, KARENA kolom itu FK ke tabel `users`,
     *                           bukan `employees`. WAJIB dikirim dari $request->user()->id
     *                           di controller, bukan employeeId.
     * @param  array<int, array<string, mixed>>  $products  Payload 'products' dari ImportProductRequest,
     *                                                       masing-masing baris HARUS sudah menyertakan
     *                                                       key '_import_created' (bool) yang diisi controller
     *                                                       pas dia tau persis produk itu baru dibuat atau update.
     */
    public function __construct(
        public int $entityId,
        public int $employeeId,
        public int $userId,
        public array $products,
    ) {}

    public function handle(): void
    {
        $entity = Entity::findOrFail($this->entityId);
        $employee = Employee::findOrFail($this->employeeId);

        $orderTypeImport = $this->importDefaultOrderType($entity);
        $orderType = $orderTypeImport['data'];

        $now = now();

        $directory = self::STORAGE_DIRECTORY.'/'.$entity->id;
        $relativeFilePath = $directory.'/'.Str::random(40).'.xlsx';

        $importService = ProductImportService::create([
            'entity_id' => $entity->id,
            'employee_id' => $employee->id,
            'code' => UniqueCodeGenerator::generateCode(),
            'employee_requested_by' => $employee->id,
            'requested_at' => $now,
            'local_requested_at' => now('Asia/Jakarta'),
            'employee_approved_by' => $employee->id,
            'approved_at' => $now,
            'local_approved_at' => now('Asia/Jakarta'),
            'file_url' => $relativeFilePath,
            'imported_product_count' => count($this->products),
            'status' => 'approved',
            'auto_approve' => true,
            'note' => 'Import produk sistem baru',
            'created_by' => $this->userId,
            'updated_by' => $this->userId,
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ]);

        $detailRows = [];
        $totalQuantity = 0;
        $productCreatedCount = 0;

        foreach ($this->products as $index => $productData) {
            $row = $this->buildDetailRow(
                $entity,
                $importService,
                $orderType,
                $orderTypeImport['created'],
                $index,
                $productData,
                $now,
            );

            if ($row === null) {
                continue;
            }

            $detailRows[] = $row;
            $totalQuantity += $row['stok'];

            if ($row['product_created']) {
                $productCreatedCount++;
            }
        }

        foreach (array_chunk($detailRows, 200) as $chunk) {
            ProductImportServiceDetail::insert($chunk);
        }

        $this->writeSnapshotFile($relativeFilePath, $detailRows);
        $this->enforceFileRetentionLimit($directory);

        $importService->update([
            'imported_product_quantity' => $totalQuantity,
            'product_created_count' => $productCreatedCount,
            'product_unit_created_count' => 0,
            'product_category_created_count' => 0,
            'order_type_created_count' => $orderTypeImport['created'] ? 1 : 0,
            'updated_by' => $this->userId,
        ]);
    }

    private function writeSnapshotFile(string $relativePath, array $detailRows): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Produk');

        $headers = ['No', 'SKU', 'Nama', 'Barcode', 'Kategori', 'Satuan', 'Lokasi', 'Harga Beli', 'Harga Jual', 'Stok Masuk'];
        $sheet->fromArray($headers, null, 'A1');

        $rowNum = 2;
        foreach ($detailRows as $i => $row) {
            $sheet->fromArray([
                $i + 1,
                $row['kode'],
                $row['nama'],
                $row['barcode'],
                $row['kategori'],
                $row['satuan'],
                $row['nama_lokasi'],
                $row['harga_pokok'],
                $row['harga_jual_ecer'],
                $row['stok'],
            ], null, "A{$rowNum}");
            $rowNum++;
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'import_').'.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        Storage::put($relativePath, file_get_contents($tmpPath));

        @unlink($tmpPath);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    private function enforceFileRetentionLimit(string $directory): void
    {
        $files = collect(Storage::files($directory))
            ->filter(fn ($file) => str_ends_with($file, '.xlsx'))
            ->sortBy(fn ($file) => Storage::lastModified($file))
            ->values();

        $excess = $files->count() - self::FILE_RETENTION_LIMIT;

        if ($excess > 0) {
            Storage::delete($files->take($excess)->all());
        }
    }

    private function buildDetailRow(
        Entity $entity,
        ProductImportService $importService,
        OrderType $orderType,
        bool $orderTypeCreated,
        int $index,
        array $productData,
        $now,
    ): ?array {
        $sku = $productData['sku'] ?? null;

        $product = $sku
            ? Product::where('entity_id', $entity->id)->where('sku', $sku)->first()
            : null;

        if (! $product) {
            Log::warning('ImportProductLogJob: produk tidak ditemukan saat logging, baris dilewati.', [
                'import_service_id' => $importService->id,
                'row' => $index + 1,
                'sku' => $sku,
            ]);

            return null;
        }

        $unit = ProductUnit::find($productData['product_unit_id'] ?? null);
        $category = ProductCategory::find($productData['product_category_id'] ?? null);
        $location = Location::find($productData['location_id'] ?? null);

        $stockIn = collect($productData['stock_movements'] ?? [])
            ->firstWhere('location_id', $productData['location_id'] ?? null)['stock'] ?? 0;

        $productCreated = (bool) ($productData['_import_created'] ?? false);

        return [
            'product_import_service_id' => $importService->id,
            'imported_line_row' => $index + 1,
            'product_id' => $product->id,
            'product_code' => (string) ($product->code ?? ''),
            'product_name' => (string) $product->name,
            'product_barcode' => (string) ($product->barcode ?? ''),
            'product_description' => $product->description,
            'product_created' => $productCreated,
            'product_unit_id' => $unit->id ?? 0,
            'product_unit_name' => $unit->name ?? '',
            'product_unit_created' => false,
            'order_type_id' => $orderType->id,
            'order_type_name' => $orderType->name,
            'order_type_created' => $orderTypeCreated,
            'product_category_id' => $category->id ?? 0,
            'product_category_name' => $category->name ?? '',
            'product_category_created' => false,
            'buying_price' => $productData['last_buying_price'] ?? null,
            'selling_price' => $productData['sell_price'] ?? null,
            'location_id' => $productData['location_id'] ?? 0,
            'stock_in' => $stockIn,
            'status' => 'ok',
            'status_message' => null,
            'kode' => (string) ($product->code ?? ''),
            'nama' => (string) $product->name,
            'deskripsi' => (string) ($product->description ?? ''),
            'satuan' => $unit->name ?? '',
            'berat' => null,
            'harga_pokok' => (int) ($productData['last_buying_price'] ?? 0),
            'harga_jual_ecer' => (int) ($productData['sell_price'] ?? 0),
            'harga_jual_grosir' => (int) ($productData['sell_price'] ?? 0),
            'kategori' => $category->name ?? '',
            'stok_minimum' => 0,
            'barcode' => $product->barcode,
            'nama_lokasi' => $location->name ?? '',
            'stok' => $stockIn,
            'created_at' => $now('Asia/Jakarta'),
            'updated_at' => $now('Asia/Jakarta'),
            'created_by' => $this->userId,
            'updated_by' => $this->userId,
        ];
    }

    private function importDefaultOrderType(Entity $entity): array
    {
        $name = 'Ecer';
        $searchName = UniqueCodeGenerator::generateSearchName($name);

        $orderType = OrderType::where('entity_id', $entity->id)
            ->where('search_name', $searchName)
            ->first();

        $created = false;

        if (! $orderType) {
            $created = true;
            $orderType = new OrderType();
            $orderType->entity_id = $entity->id;
            $orderType->name = $name;
            $orderType->search_name = $searchName;
            $orderType->fixed_fee = 0;
            $orderType->variable_fee = 0;
            $orderType->save();
        }

        return [
            'data' => $orderType,
            'created' => $created,
        ];
    }

    public function failed(Throwable $exception): void
    {
        Log::error('ImportProductLogJob gagal permanen setelah semua percobaan.', [
            'entity_id' => $this->entityId,
            'employee_id' => $this->employeeId,
            'total_products' => count($this->products),
            'exception' => $exception->getMessage(),
        ]);
    }
}