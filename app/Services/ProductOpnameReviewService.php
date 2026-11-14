<?php

namespace App\Services;

use App\Enums\OpnameReviewDecision;
use App\Enums\OpnameSessionStatus;
use App\Helpers\Services\ProductOpname\ProductOpnameApprovalService;
use App\Models\ProductOpnameService as ProductOpnameDocument;
use App\Models\ProductOpnameServiceDetail;
use App\Models\ProductOpnameSession;
use App\Models\ProductOpnameSessionScan;
use App\Services\Concerns\HandlesStockDocuments;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Review PJ per produk. Daftarnya gabungan barang ter-scan dan barang berstok yang
 * tidak ter-scan (dianggap stok SO 0). Untuk tiap selisih PJ memilih pakai stok SO atau
 * stok sistem beserta alasannya lalu menguncinya. Baris terkunci adalah draf yang bisa
 * dibuka lagi. Saat sesi ditutup semuanya diselesaikan sekaligus supaya fisik dan sistem
 * klop: stok SO menyesuaikan stok sistem, stok sistem menyesuaikan catatan SO.
 */
class ProductOpnameReviewService
{
    use HandlesStockDocuments;

    /** Jeda maksimal antar scan orang yang sama agar ditandai mungkin dobel. */
    private const RAPID_REPEAT_SECONDS = 2;

    public function paginate(ProductOpnameSession $session, string $filter, string $search): LengthAwarePaginator
    {
        $suspects = $this->rapidRepeatProductIds($session);

        $page = $this->filtered($session, $filter)
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $q) => $q
                ->whereLike('name', "%{$search}%")
                ->orWhereLike('barcode', "%{$search}%")
                ->orWhereLike('sku', "%{$search}%")))
            // Selisih yang belum dikunci selalu di atas, lalu yang mungkin dobel, lalu selisih terbesar.
            ->orderByRaw('CASE WHEN decision IS NULL AND difference_stock <> 0 THEN 0 ELSE 1 END')
            ->when($suspects !== [], fn (Builder $query) => $query->orderByRaw(
                'CASE WHEN product_id IN ('.implode(',', array_fill(0, count($suspects), '?')).') THEN 0 ELSE 1 END',
                $suspects,
            ))
            ->orderByRaw('ABS(difference_stock) DESC')
            ->orderBy('name')
            // Harus salah satu pilihan di TablePagination, kalau tidak pilihannya tampil kosong.
            ->paginate(in_array((int) request('per_page'), [10, 25, 50, 100], true) ? (int) request('per_page') : 10)
            ->withQueryString();

        $insights = $this->scanInsights($session, $page->getCollection()->pluck('product_id')->map(fn ($id) => (int) $id)->all());

        return $page->through(function (object $row) use ($insights) {
            $row = $this->castRow($row);
            $insight = $insights[$row->product_id] ?? null;

            $row->scanners = $insight['scanners'] ?? [];
            $row->rapid_repeats = $insight['rapid_repeats'] ?? 0;
            $row->cancelled_scans = $insight['cancelled_scans'] ?? 0;

            return $row;
        });
    }

    /**
     * Jumlah produk yang ditandai mungkin dobel dan belum dikunci.
     */
    public function suspectCount(ProductOpnameSession $session): int
    {
        $suspects = $this->rapidRepeatProductIds($session);

        if ($suspects === []) {
            return 0;
        }

        $decided = DB::table('product_opname_session_reviews')
            ->where('session_id', $session->id)
            ->whereIn('product_id', $suspects)
            ->count();

        return count($suspects) - $decided;
    }

    public function correctionCount(ProductOpnameSession $session): int
    {
        if (! ProductOpnameSessionScan::supportsCorrections()) {
            return 0;
        }

        return DB::table('product_opname_session_scans')
            ->where('session_id', $session->id)
            ->whereNotNull('cancelled_scan_id')
            ->count();
    }

    /**
     * Produk yang di-scan orang yang sama dengan jarak sangat dekat. Hanya tanda untuk PJ,
     * bukan bukti salah: barang kembar yang di-scan cepat juga terlihat seperti ini.
     *
     * @return array<int, int>
     */
    private function rapidRepeatProductIds(ProductOpnameSession $session): array
    {
        $ids = [];
        $last = [];

        foreach ($this->validScans($session)->get(['s.product_id', 's.ins_by', 's.ins_date']) as $scan) {
            $key = $scan->product_id.'|'.$scan->ins_by;
            $at = strtotime($scan->ins_date);

            if (isset($last[$key]) && $at - $last[$key] <= self::RAPID_REPEAT_SECONDS) {
                $ids[(int) $scan->product_id] = (int) $scan->product_id;
            }

            $last[$key] = $at;
        }

        return array_values($ids);
    }

    /**
     * Rincian per petugas, jumlah scan berdekatan, dan jumlah koreksi untuk baris di halaman ini.
     *
     * @param  array<int, int>  $productIds
     * @return array<int, array{scanners: array<int, array{name: string, qty: int}>, rapid_repeats: int, cancelled_scans: int}>
     */
    private function scanInsights(ProductOpnameSession $session, array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        $insights = [];
        $last = [];

        $scans = $this->validScans($session)
            ->leftJoin('employees as e', 'e.id', '=', 's.ins_by')
            ->whereIn('s.product_id', $productIds)
            ->get(['s.product_id', 's.ins_by', 's.ins_date', 'e.first_name', 'e.last_name']);

        foreach ($scans as $scan) {
            $productId = (int) $scan->product_id;
            $insights[$productId] ??= ['people' => [], 'rapid_repeats' => 0, 'cancelled_scans' => 0];

            $name = trim($scan->first_name.' '.$scan->last_name);
            $insights[$productId]['people'][$name] = ($insights[$productId]['people'][$name] ?? 0) + 1;

            $key = $productId.'|'.$scan->ins_by;
            $at = strtotime($scan->ins_date);

            if (isset($last[$key]) && $at - $last[$key] <= self::RAPID_REPEAT_SECONDS) {
                $insights[$productId]['rapid_repeats']++;
            }

            $last[$key] = $at;
        }

        $corrections = ! ProductOpnameSessionScan::supportsCorrections() ? [] : DB::table('product_opname_session_scans')
            ->where('session_id', $session->id)
            ->whereIn('product_id', $productIds)
            ->whereNotNull('cancelled_scan_id')
            ->groupBy('product_id')
            ->selectRaw('product_id, COUNT(*) as total')
            ->pluck('total', 'product_id');

        foreach ($corrections as $productId => $total) {
            $insights[(int) $productId] ??= ['people' => [], 'rapid_repeats' => 0, 'cancelled_scans' => 0];
            $insights[(int) $productId]['cancelled_scans'] = (int) $total;
        }

        return array_map(fn (array $insight) => [
            'scanners' => collect($insight['people'])
                ->map(fn (int $qty, string $name) => ['name' => $name, 'qty' => $qty])
                ->values()
                ->all(),
            'rapid_repeats' => $insight['rapid_repeats'],
            'cancelled_scans' => $insight['cancelled_scans'],
        ], $insights);
    }

    /**
     * Scan petugas yang masih berlaku: bukan baris koreksi, bukan koreksi PJ, dan belum
     * dibatalkan, urut waktu.
     */
    private function validScans(ProductOpnameSession $session): Builder
    {
        return DB::table('product_opname_session_scans as s')
            ->where('s.session_id', $session->id)
            ->where('s.qty', '>', 0)
            ->when(ProductOpnameSessionScan::supportsCorrections(), fn (Builder $query) => $query
                ->leftJoin('product_opname_session_scans as c', 'c.cancelled_scan_id', '=', 's.id')
                ->whereNull('c.id')
                ->where(fn (Builder $q) => $q
                    ->whereNull('s.reason')
                    ->orWhere('s.reason', 'not like', ProductOpnameScanService::PJ_CORRECTION_PREFIX.'%')))
            ->orderBy('s.id');
    }

    /**
     * @return array{difference: int, matched: int, all: int, pending: int, accepted: int, rejected: int, unscanned: int, difference_stock: int}
     */
    public function summary(ProductOpnameSession $session): array
    {
        $row = DB::query()
            ->fromSub($this->rows($session), 'rows')
            ->selectRaw('
                SUM(CASE WHEN difference_stock <> 0 THEN 1 ELSE 0 END) as difference_count,
                SUM(CASE WHEN difference_stock = 0 THEN 1 ELSE 0 END) as matched,
                SUM(CASE WHEN difference_stock <> 0 AND decision IS NULL THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN decision = ? THEN 1 ELSE 0 END) as accepted,
                SUM(CASE WHEN decision = ? THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN scanned = 0 AND difference_stock <> 0 THEN 1 ELSE 0 END) as unscanned,
                SUM(CASE WHEN decision = ? THEN difference_stock ELSE 0 END) as difference_stock
            ', [
                OpnameReviewDecision::Accepted->value,
                OpnameReviewDecision::Rejected->value,
                OpnameReviewDecision::Accepted->value,
            ])
            ->first();

        $difference = (int) ($row->difference_count ?? 0);
        $matched = (int) ($row->matched ?? 0);

        return [
            'difference' => $difference,
            'matched' => $matched,
            'all' => $difference + $matched,
            'pending' => (int) ($row->pending ?? 0),
            'accepted' => (int) ($row->accepted ?? 0),
            'rejected' => (int) ($row->rejected ?? 0),
            'unscanned' => (int) ($row->unscanned ?? 0),
            'difference_stock' => (int) ($row->difference_stock ?? 0),
        ];
    }

    /**
     * Lampiran laporan: selisih yang memakai stok SO, stoknya berubah.
     *
     * @return Collection<int, object>
     */
    public function attachmentRows(ProductOpnameSession $session): Collection
    {
        return DB::query()
            ->fromSub($this->rows($session), 'rows')
            ->where('decision', OpnameReviewDecision::Accepted->value)
            ->where('difference_stock', '<>', 0)
            ->orderBy('name')
            ->get()
            ->map(fn (object $row) => $this->castRow($row));
    }

    /**
     * Ringkasan laporan per kelompok: sesuai, pakai stok SO, dan pakai stok sistem.
     *
     * @return array<string, array{products: int, recorded: int, counted: int, difference: int}>
     */
    public function reportTotals(ProductOpnameSession $session): array
    {
        $totals = [];

        foreach (['matched', 'accepted', 'rejected'] as $group) {
            $totals[$group] = ['products' => 0, 'recorded' => 0, 'counted' => 0, 'difference' => 0];
        }

        $rows = DB::query()
            ->fromSub($this->rows($session), 'rows')
            ->selectRaw('
                CASE
                    WHEN decision = ? THEN \'accepted\'
                    WHEN decision = ? THEN \'rejected\'
                    WHEN difference_stock = 0 THEN \'matched\'
                END as report_group,
                COUNT(*) as products,
                SUM(recorded_stock) as recorded,
                SUM(counted_stock) as counted,
                SUM(difference_stock) as difference
            ', [OpnameReviewDecision::Accepted->value, OpnameReviewDecision::Rejected->value])
            ->groupBy('report_group')
            ->get();

        foreach ($rows as $row) {
            if ($row->report_group !== null) {
                $totals[$row->report_group] = [
                    'products' => (int) $row->products,
                    'recorded' => (int) $row->recorded,
                    'counted' => (int) $row->counted,
                    'difference' => (int) $row->difference,
                ];
            }
        }

        return $totals;
    }

    /**
     * Kunci keputusan satu produk selisih sebagai draf. Stok belum bergerak.
     */
    public function lock(ProductOpnameSession $session, int $productId, OpnameReviewDecision $decision, string $note): void
    {
        $employeeId = $this->employeeId();

        DB::transaction(function () use ($session, $productId, $decision, $note, $employeeId) {
            $locked = $this->lockedReviewSession($session);

            $row = DB::query()
                ->fromSub($this->rows($locked), 'rows')
                ->where('product_id', $productId)
                ->first();

            if (! $row) {
                $this->fail('Produk tidak ada dalam daftar review.');
            }

            if ($row->decision !== null) {
                $this->fail('Produk ini sudah dikunci.');
            }

            if ((int) $row->difference_stock === 0) {
                $this->fail('Produk ini cocok, tidak perlu diputuskan.');
            }

            DB::table('product_opname_session_reviews')->insert([
                'session_id' => $locked->id,
                'product_id' => $productId,
                'recorded_stock' => (int) $row->recorded_stock,
                'counted_stock' => (int) $row->counted_stock,
                'difference_stock' => (int) $row->difference_stock,
                'decision' => $decision->value,
                'note' => $note,
                'ins_date' => now(),
                'ins_by' => $employeeId,
            ]);
        });
    }

    /**
     * Buka kunci supaya keputusan atau hitungannya bisa diubah lagi, selama sesi belum ditutup.
     */
    public function unlock(ProductOpnameSession $session, int $productId): void
    {
        DB::transaction(function () use ($session, $productId) {
            $locked = $this->lockedReviewSession($session);

            $deleted = DB::table('product_opname_session_reviews')
                ->where('session_id', $locked->id)
                ->where('product_id', $productId)
                ->delete();

            if (! $deleted) {
                $this->fail('Produk ini belum dikunci.');
            }
        });
    }

    /**
     * Saat sesi ditutup, dipanggil di dalam transaksi penutupan sesi:
     * - Pakai stok SO: stok sistem disesuaikan. Yang diterapkan selisihnya (stok SO dikurangi
     *   stok sistem saat scan), sehingga penjualan setelah scan tidak terhapus.
     * - Pakai stok sistem: fisiknya dinyatakan PJ sama dengan stok sistem, jadi catatan SO yang
     *   disamakan lewat satu baris koreksi atas nama PJ beserta alasannya. Keputusannya dilepas
     *   sehingga produk ini terhitung sesuai di laporan; jejaknya ada di baris koreksi itu.
     */
    public function applyLocked(ProductOpnameSession $session): void
    {
        $employeeId = $this->employeeId();

        $rows = DB::table('product_opname_session_reviews')
            ->where('session_id', $session->id)
            ->whereNull('product_opname_service_detail_id')
            ->where('difference_stock', '<>', 0)
            ->orderBy('product_id')
            ->get();

        foreach ($rows as $row) {
            if ((int) $row->decision === OpnameReviewDecision::Accepted->value) {
                $detailId = $this->applyToStock($session, $row, $row->note, $employeeId);

                DB::table('product_opname_session_reviews')
                    ->where('session_id', $session->id)
                    ->where('product_id', $row->product_id)
                    ->update(['product_opname_service_detail_id' => $detailId]);

                continue;
            }

            $this->recordPicCorrection($session, $row, $employeeId);

            DB::table('product_opname_session_reviews')
                ->where('session_id', $session->id)
                ->where('product_id', $row->product_id)
                ->delete();
        }
    }

    private function recordPicCorrection(ProductOpnameSession $session, object $row, int $employeeId): void
    {
        $product = DB::table('products')->where('id', $row->product_id)->first(['barcode', 'sku']);

        $data = [
            'uuid' => (string) Str::uuid(),
            'session_id' => $session->id,
            'product_id' => (int) $row->product_id,
            'barcode' => $product->barcode ?? $product->sku ?? '',
            // Stok sistem dikurangi stok SO: plus bila tidak ter-scan, minus bila kelebihan.
            'qty' => (int) $row->recorded_stock - (int) $row->counted_stock,
            'recorded_stock' => (int) $row->recorded_stock,
            'ins_date' => now(),
            'ins_by' => $employeeId,
        ];

        if (ProductOpnameSessionScan::supportsCorrections()) {
            $data['reason'] = Str::limit(ProductOpnameScanService::PJ_CORRECTION_PREFIX.$row->note, 255, '');
        }

        ProductOpnameSessionScan::query()->insert($data);
    }

    private function lockedReviewSession(ProductOpnameSession $session): ProductOpnameSession
    {
        $locked = ProductOpnameSession::query()->lockForUpdate()->findOrFail($session->id);

        if ($locked->currentStatus() !== OpnameSessionStatus::Review) {
            $this->fail('Sesi tidak sedang dalam tahap review.');
        }

        return $locked;
    }

    /**
     * Buat baris dokumen opname lalu gerakkan stok. Lewat dokumen yang sudah ada supaya
     * laporan kartu stok dan POS tetap melabelinya "Stok opname".
     */
    private function applyToStock(ProductOpnameSession $session, object $row, ?string $note, int $employeeId): int
    {
        $document = $this->documentFor($session, $employeeId);
        $product = $this->productsById([$row->product_id])[$row->product_id] ?? null;

        if (! $product) {
            $this->fail('Produk tidak ditemukan.');
        }

        $userId = auth()->id();

        $detail = new ProductOpnameServiceDetail([
            'product_category_id' => $product->product_category_id,
            'recorded_stock' => (int) $row->recorded_stock,
            'counted_stock' => (int) $row->counted_stock,
            'difference_stock' => (int) $row->difference_stock,
            'note' => $note,
        ]);
        $detail->product_opname_service_id = $document->id;
        $detail->employee_id = $employeeId;
        $detail->location_id = $session->location_id;
        $this->fillProductSnapshot($detail, $product);
        $detail->product_description = $product->description ?? '';
        $detail->product_category_name = $product->productCategory?->name ?? '';
        $detail->created_by = $userId;
        $detail->updated_by = $userId;
        $detail->save();

        $document->recorded_product_count += 1;
        $document->counted_product_count += 1;
        $document->difference_product_count += 1;
        $document->recorded_stock += (int) $row->recorded_stock;
        $document->counted_stock += (int) $row->counted_stock;
        $document->difference_stock += (int) $row->difference_stock;
        $document->updated_by = $userId;
        $document->save();

        (new ProductOpnameApprovalService($document))->createStockMovement($detail);

        return $detail->id;
    }

    private function documentFor(ProductOpnameSession $session, int $employeeId): ProductOpnameDocument
    {
        if ($session->product_opname_service_id) {
            return ProductOpnameDocument::query()->findOrFail($session->product_opname_service_id);
        }

        $localNow = $this->localNow($session->location_id);
        $userId = auth()->id();

        $document = new ProductOpnameDocument([
            'location_id' => $session->location_id,
            'note' => 'Hasil sesi SO '.$session->code,
            'auto_approve' => true,
            'recorded_product_count' => 0,
            'counted_product_count' => 0,
            'difference_product_count' => 0,
            'recorded_stock' => 0,
            'counted_stock' => 0,
            'difference_stock' => 0,
        ]);
        $document->code = $session->code;
        $document->entity_id = $session->entity_id;
        $document->employee_requested_by = $employeeId;
        $document->requested_at = now();
        $document->local_requested_at = $localNow;
        $document->employee_approved_by = $employeeId;
        $document->approved_at = now();
        $document->local_approved_at = $localNow;
        $document->status = 'approved';
        $document->created_by = $userId;
        $document->updated_by = $userId;
        $document->save();

        $session->update(['product_opname_service_id' => $document->id]);

        return $document;
    }

    private function filtered(ProductOpnameSession $session, string $filter): Builder
    {
        return DB::query()
            ->fromSub($this->rows($session), 'rows')
            ->when($filter === 'difference', fn (Builder $query) => $query->where('difference_stock', '<>', 0))
            ->when($filter === 'matched', fn (Builder $query) => $query->where('difference_stock', 0));
    }

    /**
     * Satu baris per produk. Stok sistem: nilai saat dikunci, lalu stok saat scan pertama,
     * lalu stok terkini untuk barang yang tidak ter-scan.
     */
    private function rows(ProductOpnameSession $session): Builder
    {
        $scans = DB::table('product_opname_session_scans')
            ->where('session_id', $session->id)
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(qty) as counted, MIN(id) as first_id');

        $stocks = DB::table('product_location_stocks')
            ->where('location_id', $session->location_id)
            ->whereNull('deleted_at')
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(stock) as stock');

        $recorded = 'COALESCE(r.recorded_stock, f.recorded_stock, st.stock, 0)';
        $counted = 'COALESCE(r.counted_stock, s.counted, 0)';

        return DB::table('products as p')
            ->leftJoinSub($scans, 's', 's.product_id', '=', 'p.id')
            ->leftJoin('product_opname_session_scans as f', 'f.id', '=', 's.first_id')
            ->leftJoinSub($stocks, 'st', 'st.product_id', '=', 'p.id')
            ->leftJoin('product_opname_session_reviews as r', function ($join) use ($session) {
                $join->on('r.product_id', '=', 'p.id')->where('r.session_id', '=', $session->id);
            })
            ->leftJoin('product_units as u', 'u.id', '=', 'p.product_unit_id')
            ->where('p.entity_id', $session->entity_id)
            ->whereNull('p.deleted_at')
            ->where(fn (Builder $query) => $query
                ->whereNotNull('r.product_id')
                ->orWhere('s.counted', '<>', 0)
                ->orWhere(fn (Builder $q) => $q
                    ->where('st.stock', '<>', 0)
                    ->where('p.service', 0)
                    ->where('p.modifier', 0)))
            ->selectRaw("
                p.id as product_id,
                p.name,
                p.barcode,
                p.sku,
                u.name as unit_name,
                {$recorded} as recorded_stock,
                {$counted} as counted_stock,
                {$counted} - {$recorded} as difference_stock,
                CASE WHEN COALESCE(s.counted, 0) <> 0 THEN 1 ELSE 0 END as scanned,
                r.decision,
                r.note
            ");
    }

    /**
     * MySQL mengembalikan hasil SUM sebagai teks ("0"), sedangkan tampilan membandingkan
     * angka secara ketat. Tanpa ini baris cocok ikut memunculkan pilihan stok SO dan sistem.
     */
    private function castRow(object $row): object
    {
        $row->product_id = (int) $row->product_id;
        $row->recorded_stock = (int) $row->recorded_stock;
        $row->counted_stock = (int) $row->counted_stock;
        $row->difference_stock = (int) $row->difference_stock;
        $row->scanned = (int) $row->scanned;
        $row->decision = $row->decision === null ? null : (int) $row->decision;

        return $row;
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['review' => $message]);
    }
}
