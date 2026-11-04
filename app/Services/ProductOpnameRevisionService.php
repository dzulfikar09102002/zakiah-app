<?php

namespace App\Services;

use App\Enums\OpnameRevisionReason;
use App\Enums\OpnameSessionStatus;
use App\Helpers\Services\ProductAdjustmentStock\ProductAdjustmentStockApprovalService;
use App\Models\ProductAdjustmentStock;
use App\Models\ProductAdjustmentStockDetail;
use App\Models\ProductOpnameSession;
use App\Services\Concerns\HandlesStockDocuments;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Revisi SO: hasil pencarian barang dari lampiran laporan. Setiap baris langsung
 * menggerakkan stok lewat dokumen Penyesuaian Stok.
 */
class ProductOpnameRevisionService
{
    use HandlesStockDocuments;

    public function __construct(
        protected ProductOpnameReviewService $reviews,
    ) {}

    /**
     * @return Collection<int, object>
     */
    public function eligibleProducts(ProductOpnameSession $session): Collection
    {
        return $this->reviews->attachmentRows($session->parent);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function rows(ProductOpnameSession $session): Collection
    {
        return DB::table('product_opname_session_revisions as v')
            ->join('products as p', 'p.id', '=', 'v.product_id')
            ->where('v.session_id', $session->id)
            ->orderBy('p.name')
            ->orderBy('v.seq')
            ->get(['v.product_id', 'v.seq', 'v.adjustment_qty', 'v.reason', 'v.note', 'p.name', 'p.barcode', 'p.sku'])
            ->map(fn (object $row) => [
                'product_id' => (int) $row->product_id,
                'seq' => (int) $row->seq,
                'name' => $row->name,
                'barcode' => $row->barcode,
                'sku' => $row->sku,
                'adjustment_qty' => (int) $row->adjustment_qty,
                'reason' => (int) $row->reason,
                'reason_label' => OpnameRevisionReason::from((int) $row->reason)->label(),
                'note' => $row->note,
            ]);
    }

    /**
     * @param  array{product_id: int, adjustment_qty: int, reason: int, note?: string|null}  $data
     */
    public function store(ProductOpnameSession $session, array $data): void
    {
        $employeeId = $this->employeeId();

        DB::transaction(function () use ($session, $data, $employeeId) {
            $locked = ProductOpnameSession::query()->lockForUpdate()->findOrFail($session->id);

            if ($locked->parent_session_id === null || $locked->currentStatus() !== OpnameSessionStatus::Started) {
                $this->fail('Sesi revisi sudah ditutup.');
            }

            $productId = (int) $data['product_id'];
            $qty = (int) $data['adjustment_qty'];
            $reason = OpnameRevisionReason::from((int) $data['reason']);
            $note = $data['note'] ?? null;

            $eligible = $this->reviews->attachmentRows($locked->parent)->contains('product_id', $productId);

            if (! $eligible) {
                $this->fail('Produk ini tidak ada di lampiran hasil SO.');
            }

            // Stok barang hilang sudah dikurangi saat SO diterima, jadi tidak ada perubahan lagi.
            if ($reason === OpnameRevisionReason::Lost && $qty !== 0) {
                $this->fail('Barang hilang tidak mengubah stok, jumlah penyesuaian harus 0.');
            }

            $detailId = $qty !== 0 ? $this->applyToStock($locked, $productId, $qty, $note, $employeeId) : null;

            $seq = (int) DB::table('product_opname_session_revisions')
                ->where('session_id', $locked->id)
                ->where('product_id', $productId)
                ->max('seq') + 1;

            DB::table('product_opname_session_revisions')->insert([
                'session_id' => $locked->id,
                'product_id' => $productId,
                'seq' => $seq,
                'adjustment_qty' => $qty,
                'reason' => $reason->value,
                'product_adjustment_stock_detail_id' => $detailId,
                'note' => $note,
                'ins_date' => now(),
                'ins_by' => $employeeId,
            ]);
        });
    }

    private function applyToStock(ProductOpnameSession $session, int $productId, int $qty, ?string $note, int $employeeId): int
    {
        $product = $this->productsById([$productId])[$productId] ?? null;

        if (! $product) {
            $this->fail('Produk tidak ditemukan.');
        }

        $recorded = (int) DB::table('product_location_stocks')
            ->where('product_id', $productId)
            ->where('location_id', $session->location_id)
            ->whereNull('deleted_at')
            ->sum('stock');

        if ($recorded + $qty < 0) {
            $this->fail("Penyesuaian melebihi stok saat ini ({$recorded}).");
        }

        $document = $this->documentFor($session, $employeeId);
        $userId = auth()->id();

        $detail = new ProductAdjustmentStockDetail([
            'product_category_id' => $product->product_category_id,
            'recorded_stock' => $recorded,
            'counted_stock' => $recorded + $qty,
            'difference_stock' => $qty,
            'note' => $note,
        ]);
        $detail->product_adjustment_stock_id = $document->id;
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
        $document->recorded_stock += $recorded;
        $document->counted_stock += $recorded + $qty;
        $document->difference_stock += $qty;
        $document->updated_by = $userId;
        $document->save();

        (new ProductAdjustmentStockApprovalService($document))->createStockMovement($detail);

        return $detail->id;
    }

    private function documentFor(ProductOpnameSession $session, int $employeeId): ProductAdjustmentStock
    {
        if ($session->product_adjustment_stock_id) {
            return ProductAdjustmentStock::query()->findOrFail($session->product_adjustment_stock_id);
        }

        $localNow = $this->localNow($session->location_id);
        $userId = auth()->id();

        $document = new ProductAdjustmentStock([
            'location_id' => $session->location_id,
            'note' => 'Hasil revisi SO '.$session->code,
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

        $session->update(['product_adjustment_stock_id' => $document->id]);

        return $document;
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['revision' => $message]);
    }
}
