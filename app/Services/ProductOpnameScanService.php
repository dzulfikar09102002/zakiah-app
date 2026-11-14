<?php

namespace App\Services;

use App\Enums\OpnameSessionStatus;
use App\Models\Product;
use App\Models\ProductOpnameSession;
use App\Models\ProductOpnameSessionScan;
use App\Services\Concerns\HandlesStockDocuments;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Setiap scan disimpan sebagai satu baris baru, tidak pernah mengubah baris lama,
 * sehingga banyak karyawan bisa scan bersamaan tanpa saling menimpa.
 */
class ProductOpnameScanService
{
    use HandlesStockDocuments;

    /** Batas tambah scan manual sekali simpan, supaya salah ketik tidak menambah ribuan baris. */
    public const MAX_MANUAL_QTY = 500;

    /** Penanda baris tambah manual, supaya bisa dibedakan dari scan barcode di rincian. */
    public const MANUAL_REASON = 'Tambah scan manual';

    /**
     * Awalan baris koreksi PJ saat sesi ditutup: PJ memilih stok sistem, artinya fisiknya
     * dinyatakan sama dengan stok sistem, jadi catatan SO disamakan. Diikuti alasan PJ.
     */
    public const PJ_CORRECTION_PREFIX = 'Koreksi PJ: ';

    /**
     * @param  array{uuid: string, barcode?: string|null, product_id?: int|null}  $data
     * @return array{product_id: int, name: string, counted: int}
     */
    public function record(ProductOpnameSession $session, array $data): array
    {
        $existing = ProductOpnameSessionScan::query()
            ->where('uuid', $data['uuid'])
            ->where('session_id', $session->id)
            ->first();

        // Kiriman ulang dari perangkat: jangan dihitung dua kali.
        if ($existing) {
            return $this->result($session, $existing->product_id);
        }

        if ($session->currentStatus() !== OpnameSessionStatus::Started || $session->parent_session_id !== null) {
            $this->fail('Sesi scan sudah ditutup.');
        }

        $product = $this->resolveProduct($data);

        $this->ensureNotDecided($session, $product);

        try {
            ProductOpnameSessionScan::query()->create([
                'uuid' => $data['uuid'],
                'session_id' => $session->id,
                'product_id' => $product->id,
                'barcode' => $data['barcode'] ?? $product->barcode ?? '',
                'qty' => 1,
                'recorded_stock' => $this->liveStock($product->id, $session->location_id),
                'ins_date' => now(),
                'ins_by' => $this->employeeId(),
            ]);
        } catch (UniqueConstraintViolationException) {
            // Dua kiriman uuid yang sama tiba bersamaan: yang pertama sudah tersimpan.
        }

        return $this->result($session, $product->id);
    }

    /**
     * Rincian setiap scan satu produk, terbaru di atas.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function productScans(ProductOpnameSession $session, int $productId): Collection
    {
        return DB::table('product_opname_session_scans as s')
            ->leftJoin('employees as e', 'e.id', '=', 's.ins_by')
            ->leftJoin('product_opname_session_scans as c', 'c.cancelled_scan_id', '=', 's.id')
            ->leftJoin('employees as ce', 'ce.id', '=', 'c.ins_by')
            ->where('s.session_id', $session->id)
            ->where('s.product_id', $productId)
            ->where('s.qty', '>', 0)
            ->orderByDesc('s.id')
            ->get([
                's.id', 's.barcode', 's.ins_date', 's.reason as scan_reason', 'e.first_name', 'e.last_name',
                'c.id as correction_id', 'c.reason', 'c.ins_date as cancelled_at',
                'ce.first_name as cancelled_first_name', 'ce.last_name as cancelled_last_name',
            ])
            ->map(fn (object $row) => [
                'id' => (int) $row->id,
                'barcode' => $row->barcode,
                'scanned_at' => Carbon::parse($row->ins_date)->toIso8601String(),
                'employee' => trim($row->first_name.' '.$row->last_name),
                'manual' => $row->scan_reason === self::MANUAL_REASON,
                'cancelled' => $row->correction_id !== null,
                'reason' => $row->reason,
                'cancelled_at' => $row->cancelled_at ? Carbon::parse($row->cancelled_at)->toIso8601String() : null,
                'cancelled_by' => $row->correction_id !== null ? trim($row->cancelled_first_name.' '.$row->cancelled_last_name) : null,
            ]);
    }

    /**
     * Tambah scan manual: dicatat sebagai $qty baris qty 1, sama seperti scan barcode,
     * supaya jumlah tetap SUM(qty) dan tiap baris tetap bisa dibatalkan satu per satu.
     * Hak aksesnya sama dengan batalkan scan.
     */
    public function addScans(ProductOpnameSession $session, int $productId, int $qty, bool $isPic): void
    {
        $employeeId = $this->employeeId();
        $product = $this->resolveProduct(['product_id' => $productId]);

        DB::transaction(function () use ($session, $product, $qty, $employeeId, $isPic) {
            $locked = ProductOpnameSession::query()->lockForUpdate()->findOrFail($session->id);
            $status = $locked->currentStatus();

            if (! in_array($status, [OpnameSessionStatus::Started, OpnameSessionStatus::Review], true)) {
                $this->fail('Scan hanya bisa ditambah sebelum sesi selesai.');
            }

            // Dicek ulang di dalam kunci: PJ bisa saja menutup scan tepat sebelum ini.
            if (! $isPic && $status !== OpnameSessionStatus::Started) {
                $this->fail('Scan sudah ditutup, hanya PJ SO yang bisa menambah scan.');
            }

            $this->ensureNotDecided($locked, $product);

            $now = now();
            $recordedStock = $this->liveStock($product->id, $locked->location_id);

            ProductOpnameSessionScan::query()->insert(array_map(fn () => [
                'uuid' => (string) Str::uuid(),
                'session_id' => $locked->id,
                'product_id' => $product->id,
                'barcode' => $product->barcode ?? $product->sku ?? '',
                'qty' => 1,
                'recorded_stock' => $recordedStock,
                'reason' => self::MANUAL_REASON,
                'ins_date' => $now,
                'ins_by' => $employeeId,
            ], range(1, $qty)));
        });
    }

    /**
     * Batalkan $qty scan sekaligus, mulai dari scan terbaru yang masih berlaku. Setiap scan
     * mendapat satu baris qty -1 yang menunjuknya, sama seperti membatalkan satu per satu.
     * Hak aksesnya sama dengan tambah scan.
     */
    public function cancelScans(ProductOpnameSession $session, int $productId, int $qty, string $reason, bool $isPic): void
    {
        $employeeId = $this->employeeId();
        $product = $this->resolveProduct(['product_id' => $productId]);

        DB::transaction(function () use ($session, $product, $qty, $reason, $employeeId, $isPic) {
            $locked = ProductOpnameSession::query()->lockForUpdate()->findOrFail($session->id);
            $status = $locked->currentStatus();

            if (! in_array($status, [OpnameSessionStatus::Started, OpnameSessionStatus::Review], true)) {
                $this->fail('Scan hanya bisa dibatalkan sebelum sesi selesai.');
            }

            if (! $isPic && $status !== OpnameSessionStatus::Started) {
                $this->fail('Scan sudah ditutup, hanya PJ SO yang bisa membatalkan scan.');
            }

            $this->ensureNotDecided($locked, $product);

            $targets = DB::table('product_opname_session_scans as s')
                ->leftJoin('product_opname_session_scans as c', 'c.cancelled_scan_id', '=', 's.id')
                ->where('s.session_id', $locked->id)
                ->where('s.product_id', $product->id)
                ->where('s.qty', '>', 0)
                ->whereNull('c.id')
                ->orderByDesc('s.id')
                ->limit($qty)
                ->get(['s.id', 's.barcode', 's.recorded_stock']);

            if ($targets->count() < $qty) {
                $this->fail("Scan berlaku hanya {$targets->count()}, tidak bisa dibatalkan {$qty}.");
            }

            $now = now();

            try {
                ProductOpnameSessionScan::query()->insert($targets->map(fn (object $scan) => [
                    'uuid' => (string) Str::uuid(),
                    'session_id' => $locked->id,
                    'product_id' => $product->id,
                    'barcode' => $scan->barcode,
                    'qty' => -1,
                    'recorded_stock' => $scan->recorded_stock,
                    'cancelled_scan_id' => $scan->id,
                    'reason' => $reason,
                    'ins_date' => $now,
                    'ins_by' => $employeeId,
                ])->all());
            } catch (UniqueConstraintViolationException) {
                // Orang lain membatalkan scan yang sama di saat bersamaan.
                $this->fail('Sebagian scan baru saja dibatalkan orang lain, silakan coba lagi.');
            }
        });
    }

    /**
     * Data layar langsung: hanya produk yang berubah sejak $since.
     * Stok sistem sengaja tidak dikirim, supaya petugas menghitung apa adanya.
     *
     * @return array<string, mixed>
     */
    public function live(ProductOpnameSession $session, int $since): array
    {
        $changed = DB::table('product_opname_session_scans')
            ->where('session_id', $session->id)
            ->where('id', '>', $since)
            ->select('product_id')
            ->distinct();

        $items = DB::table('product_opname_session_scans as s')
            ->join('products as p', 'p.id', '=', 's.product_id')
            ->leftJoin('product_units as u', 'u.id', '=', 'p.product_unit_id')
            ->where('s.session_id', $session->id)
            ->whereIn('s.product_id', $changed)
            ->groupBy('s.product_id', 'p.name', 'p.barcode', 'p.sku', 'u.name')
            ->selectRaw('s.product_id, p.name, p.barcode, p.sku, u.name as unit_name, SUM(s.qty) as counted, MAX(s.id) as last_id')
            ->orderByDesc('last_id')
            ->get()
            ->map(fn (object $row) => [
                'product_id' => (int) $row->product_id,
                'name' => $row->name,
                'barcode' => $row->barcode,
                'sku' => $row->sku,
                'unit_name' => $row->unit_name,
                'counted' => (int) $row->counted,
                'last_id' => (int) $row->last_id,
            ]);

        $employees = DB::table('product_opname_session_scans as s')
            ->join('employees as e', 'e.id', '=', 's.ins_by')
            ->where('s.session_id', $session->id)
            // Baris koreksi dan scan yang dibatalkan tidak dihitung sebagai hasil kerja petugas.
            ->where('s.qty', '>', 0)
            ->when(ProductOpnameSessionScan::supportsCorrections(), fn ($query) => $query
                ->leftJoin('product_opname_session_scans as c', 'c.cancelled_scan_id', '=', 's.id')
                ->whereNull('c.id'))
            ->groupBy('s.ins_by', 'e.first_name', 'e.last_name')
            ->selectRaw('s.ins_by as employee_id, e.first_name, e.last_name, SUM(s.qty) as qty')
            ->orderByDesc('qty')
            ->get()
            ->map(fn (object $row) => [
                'employee_id' => (int) $row->employee_id,
                'name' => trim($row->first_name.' '.$row->last_name),
                'qty' => (int) $row->qty,
            ]);

        return [
            'status' => $session->status,
            'cursor' => (int) DB::table('product_opname_session_scans')->where('session_id', $session->id)->max('id'),
            'items' => $items,
            'employees' => $employees,
        ];
    }

    /**
     * Cocok persis dengan barcode, lalu SKU, lalu kode. Tidak boleh mirip-mirip
     * karena hasilnya menjadi dasar perubahan stok.
     *
     * @param  array{barcode?: string|null, product_id?: int|null}  $data
     */
    private function resolveProduct(array $data): Product
    {
        $query = Product::query()->where('entity_id', $this->entityId());

        if (! empty($data['product_id'])) {
            $product = $query->find($data['product_id']);
        } else {
            $code = trim((string) ($data['barcode'] ?? ''));

            $product = $code === '' ? null : (clone $query)->where('barcode', $code)->first()
                ?? (clone $query)->where('sku', $code)->first()
                ?? (clone $query)->where('code', $code)->first();
        }

        if (! $product) {
            $this->fail('Barcode '.($data['barcode'] ?? '').' tidak terdaftar.');
        }

        if ($product->service || $product->modifier) {
            $this->fail("{$product->name} adalah jasa atau modifier, tidak perlu dihitung.");
        }

        return $product;
    }

    private function ensureNotDecided(ProductOpnameSession $session, Product $product): void
    {
        $decided = DB::table('product_opname_session_reviews')
            ->where('session_id', $session->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($decided) {
            $this->fail("{$product->name} sudah dikunci di review. Buka kuncinya dulu untuk mengubah hitungan.");
        }
    }

    private function countedStock(ProductOpnameSession $session, int $productId): int
    {
        return (int) DB::table('product_opname_session_scans')
            ->where('session_id', $session->id)
            ->where('product_id', $productId)
            ->sum('qty');
    }

    private function liveStock(int $productId, int $locationId): int
    {
        return (int) DB::table('product_location_stocks')
            ->where('product_id', $productId)
            ->where('location_id', $locationId)
            ->whereNull('deleted_at')
            ->sum('stock');
    }

    /**
     * @return array{product_id: int, name: string, counted: int}
     */
    private function result(ProductOpnameSession $session, int $productId): array
    {
        return [
            'product_id' => $productId,
            'name' => (string) Product::query()->whereKey($productId)->value('name'),
            'counted' => $this->countedStock($session, $productId),
        ];
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['scan' => $message]);
    }
}
