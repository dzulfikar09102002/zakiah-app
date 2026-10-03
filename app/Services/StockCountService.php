<?php

namespace App\Services;

use App\Helpers\UniqueCodeGenerator;
use App\Services\Concerns\HandlesStockDocuments;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Dasar untuk dokumen hitung stok (Stok Opname & Penyesuaian Stok) yang skemanya identik:
 * stok tercatat vs stok fisik per produk, lalu selisihnya jadi pergerakan stok saat disetujui.
 */
abstract class StockCountService
{
    use HandlesStockDocuments;

    /** @return class-string<Model> */
    abstract protected function documentClass(): string;

    /** @return class-string<Model> */
    abstract protected function detailClass(): string;

    /** Nama relasi detail pada model dokumen. */
    abstract protected function detailsRelation(): string;

    /** Kolom foreign key dokumen pada tabel detail. */
    abstract protected function foreignKey(): string;

    /** Jalankan helper backend lama yang membuat pergerakan stok dari selisih. */
    abstract protected function moveStock(Model $document): void;

    abstract protected function errorKey(): string;

    public function getDocuments(): LengthAwarePaginator
    {
        $search = request('search', '');
        $status = request('status');
        $startAt = request('start_at') ? Carbon::parse(request('start_at'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endAt = request('end_at') ? Carbon::parse(request('end_at'))->endOfDay() : null;

        $selectAll = request('select_all_location', '1') == '1';
        $locs = array_map('intval', (array) request('locs', []));
        $excludeLocs = array_map('intval', (array) request('exclude_locs', []));

        return $this->documentClass()::query()
            ->where('entity_id', $this->entityId())
            ->with([
                'location:id,name',
                'employeeRequestedBy:id,first_name,last_name',
                'employeeApprovedBy:id,first_name,last_name',
                'employeeRejectedBy:id,first_name,last_name',
            ])
            ->withCount($this->detailsRelation().' as details_count')
            ->when($search, fn ($query) => $query->whereLike('code', "%{$search}%"))
            ->when(in_array($status, ['requested', 'approved', 'rejected'], true), fn ($query) => $query->where('status', $status))
            ->when($startAt, fn ($query) => $query->where('local_requested_at', '>=', $startAt))
            ->when($endAt, fn ($query) => $query->where('local_requested_at', '<=', $endAt))
            ->when($selectAll && count($excludeLocs), fn ($query) => $query->whereNotIn('location_id', $excludeLocs))
            ->when(! $selectAll && count($locs), fn ($query) => $query->whereIn('location_id', $locs))
            ->latest('id')
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function find(int $id): Model
    {
        return $this->documentClass()::query()
            ->where('entity_id', $this->entityId())
            ->with([
                'location:id,name',
                'employeeRequestedBy:id,first_name,last_name',
                'employeeApprovedBy:id,first_name,last_name',
                'employeeRejectedBy:id,first_name,last_name',
                $this->detailsRelation().'.product:id,name,sku,barcode,sell_price',
            ])
            ->findOrFail($id);
    }

    public function store(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $userId = auth()->id();
            $employeeId = $this->employeeId();
            $localNow = $this->localNow($data['location_id']);

            $class = $this->documentClass();
            $document = new $class([
                'location_id' => $data['location_id'],
                'note' => $data['note'] ?? null,
                'auto_approve' => (bool) ($data['auto_approve'] ?? false),
            ]);
            $document->code = UniqueCodeGenerator::generateCode();
            $document->entity_id = $this->entityId();
            $document->employee_requested_by = $employeeId;
            $document->requested_at = now();
            $document->local_requested_at = $localNow;
            $document->status = 'requested';
            $document->created_by = $userId;
            $document->updated_by = $userId;
            $this->fillTotals($document, []);
            $document->save();

            $this->syncDetails($document, $data['products']);

            if ($document->auto_approve) {
                $this->markApproved($document, null);
            }

            return $document;
        });
    }

    public function update(Model $document, array $data): Model
    {
        $this->ensureStatus($document, 'requested', $this->errorKey());

        return DB::transaction(function () use ($document, $data) {
            $document->note = $data['note'] ?? null;
            $document->updated_by = auth()->id();
            $document->save();

            $this->syncDetails($document, $data['products']);

            return $document;
        });
    }

    public function destroy(Model $document): void
    {
        $this->ensureStatus($document, 'requested', $this->errorKey());

        DB::transaction(function () use ($document) {
            $document->{$this->detailsRelation()}()->delete();
            $document->delete();
        });
    }

    public function approve(Model $document, ?string $note): void
    {
        $this->ensureStatus($document, 'requested', $this->errorKey());

        DB::transaction(fn () => $this->markApproved($document, $note));
    }

    public function reject(Model $document, ?string $note): void
    {
        $this->ensureStatus($document, 'requested', $this->errorKey());

        $document->employee_rejected_by = $this->employeeId();
        $document->rejected_at = now();
        $document->local_rejected_at = $this->localNow($document->location_id);
        $document->rejected_note = $note;
        $document->status = 'rejected';
        $document->updated_by = auth()->id();
        $document->save();
    }

    public function getLocationOptions(): Collection
    {
        return (new LocationService)->getLocationOptions();
    }

    private function markApproved(Model $document, ?string $note): void
    {
        $document->employee_approved_by = $this->employeeId();
        $document->approved_at = now();
        $document->local_approved_at = $this->localNow($document->location_id);
        $document->approval_note = $note;
        $document->status = 'approved';
        $document->updated_by = auth()->id();
        $document->save();

        $this->moveStock($document);
    }

    /**
     * Simpan ulang seluruh baris detail. Stok tercatat diambil dari stok lokasi saat ini,
     * selisih = stok fisik - stok tercatat (sama seperti form backoffice lama).
     */
    private function syncDetails(Model $document, array $rows): void
    {
        $document->{$this->detailsRelation()}()->delete();

        $products = $this->productsById(array_column($rows, 'product_id'));
        $userId = auth()->id();
        $detailClass = $this->detailClass();
        $details = [];

        foreach ($rows as $row) {
            $product = $products[$row['product_id']] ?? null;
            if (! $product) {
                continue;
            }

            $recorded = $this->recordedStock($product->id, $document->location_id);
            $counted = (int) $row['counted_stock'];

            $detail = new $detailClass([
                'product_category_id' => $product->product_category_id,
                'recorded_stock' => $recorded,
                'counted_stock' => $counted,
                'difference_stock' => $counted - $recorded,
                'note' => $row['note'] ?? null,
            ]);
            $detail->{$this->foreignKey()} = $document->id;
            $detail->employee_id = $document->employee_requested_by;
            $detail->location_id = $document->location_id;
            $this->fillProductSnapshot($detail, $product);
            $detail->product_description = $product->description ?? '';
            $detail->product_category_name = $product->productCategory?->name ?? '';
            $detail->created_by = $userId;
            $detail->updated_by = $userId;
            $detail->save();

            $details[] = $detail;
        }

        $this->fillTotals($document, $details);
        $document->save();
    }

    /**
     * @param  array<int, Model>  $details
     */
    private function fillTotals(Model $document, array $details): void
    {
        $count = count($details);

        $document->recorded_product_count = $count;
        $document->counted_product_count = $count;
        $document->difference_product_count = count(array_filter($details, fn ($d) => (int) $d->difference_stock !== 0));
        $document->recorded_stock = array_sum(array_map(fn ($d) => (int) $d->recorded_stock, $details));
        $document->counted_stock = array_sum(array_map(fn ($d) => (int) $d->counted_stock, $details));
        $document->difference_stock = array_sum(array_map(fn ($d) => (int) $d->difference_stock, $details));
    }
}
