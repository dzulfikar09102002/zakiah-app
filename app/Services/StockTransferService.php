<?php

namespace App\Services;

use App\Enums\StatusEnum;
use App\Helpers\Services\ProductTransfer\ProductTransferApprove;
use App\Helpers\Services\ProductTransfer\ProductTransferStockReserved;
use App\Helpers\Services\ProductTransfer\ProductTransferStockUnreserved;
use App\Helpers\UniqueCodeGenerator;
use App\Models\ProductTransferService;
use App\Models\ProductTransferServiceDetail;
use App\Services\Concerns\HandlesStockDocuments;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Pindah stok mengikuti backend lama: saat diajukan stok lokasi asal dicadangkan (keluar),
 * saat disetujui stok masuk ke lokasi tujuan, saat ditolak/dibatalkan stok dikembalikan.
 */
class StockTransferService
{
    use HandlesStockDocuments;

    public function getTransfers(): LengthAwarePaginator
    {
        $search = request('search', '');
        $status = request('status');
        $startAt = request('start_at') ? Carbon::parse(request('start_at'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endAt = request('end_at') ? Carbon::parse(request('end_at'))->endOfDay() : null;

        $selectAll = request('select_all_location', '1') == '1';
        $locs = array_map('intval', (array) request('locs', []));
        $excludeLocs = array_map('intval', (array) request('exclude_locs', []));

        return ProductTransferService::query()
            ->where('entity_id', $this->entityId())
            ->with([
                'fromLocation:id,name',
                'toLocation:id,name',
                'employeeRequestedBy:id,first_name,last_name',
            ])
            ->withCount('productTransferServiceDetails as details_count')
            ->withSum('productTransferServiceDetails as total_quantity', 'quantity')
            ->when($search, fn ($query) => $query->whereLike('code', "%{$search}%"))
            ->when(in_array($status, ['requested', 'approved', 'rejected', 'cancelled'], true), fn ($query) => $query->where('status', $status))
            ->when($startAt, fn ($query) => $query->where('local_requested_at', '>=', $startAt))
            ->when($endAt, fn ($query) => $query->where('local_requested_at', '<=', $endAt))
            // Filter lokasi berlaku untuk lokasi asal maupun tujuan.
            ->when($selectAll && count($excludeLocs), function ($query) use ($excludeLocs) {
                $query->whereNotIn('from_location_id', $excludeLocs)->whereNotIn('to_location_id', $excludeLocs);
            })
            ->when(! $selectAll && count($locs), function ($query) use ($locs) {
                $query->where(fn ($q) => $q->whereIn('from_location_id', $locs)->orWhereIn('to_location_id', $locs));
            })
            ->latest('id')
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function find(int $id): ProductTransferService
    {
        return ProductTransferService::query()
            ->where('entity_id', $this->entityId())
            ->with([
                'fromLocation:id,name',
                'toLocation:id,name',
                'employeeRequestedBy:id,first_name,last_name',
                'employeeApprovedBy:id,first_name,last_name',
                'employeeRejectedBy:id,first_name,last_name',
                'employeeCancelledBy:id,first_name,last_name',
                'productTransferServiceDetails.product:id,name,sku,barcode,sell_price',
            ])
            ->findOrFail($id);
    }

    public function store(array $data): ProductTransferService
    {
        return DB::transaction(function () use ($data) {
            $userId = auth()->id();
            $employeeId = $this->employeeId();

            $transfer = new ProductTransferService([
                'from_location_id' => $data['from_location_id'],
                'to_location_id' => $data['to_location_id'],
                'request_note' => $data['request_note'] ?? null,
            ]);
            $transfer->code = UniqueCodeGenerator::generateCode();
            $transfer->entity_id = $this->entityId();
            $transfer->employee_requested_by = $employeeId;
            $transfer->requested_at = now();
            $transfer->local_requested_at = $this->localNow($data['from_location_id']);
            // Backend lama tidak mengisi status padahal kolomnya wajib.
            $transfer->status = StatusEnum::Requested->value;
            $transfer->auto_approve = (bool) ($data['auto_approve'] ?? false);
            $transfer->created_by = $userId;
            $transfer->updated_by = $userId;
            $transfer->save();

            $products = $this->productsById(array_column($data['products'], 'product_id'));

            foreach ($data['products'] as $row) {
                $product = $products[$row['product_id']] ?? null;
                if (! $product) {
                    continue;
                }

                $detail = new ProductTransferServiceDetail;
                $detail->product_transfer_service_id = $transfer->id;
                $this->fillProductSnapshot($detail, $product);
                $detail->original_product_unit_id = $product->product_unit_id;
                $detail->smallest_product_unit_name = $detail->product_unit_name;
                $detail->conversion_quantity = 1;
                $detail->quantity = (int) $row['quantity'];
                $detail->transfered_quantity = $detail->quantity * $detail->conversion_quantity;
                $detail->buying_price = (int) ($product->last_buying_price ?? 0);
                $detail->transfered_buying_price = $detail->buying_price * $detail->conversion_quantity;
                $detail->save();
            }

            (new ProductTransferStockReserved($transfer->id))->reserved();

            if ($transfer->auto_approve) {
                (new ProductTransferApprove($transfer->id, $employeeId))->approve();
            }

            return $transfer;
        });
    }

    public function approve(ProductTransferService $transfer, ?string $note): void
    {
        $this->ensureStatus($transfer, StatusEnum::Requested->value, 'product_transfer_service');

        DB::transaction(function () use ($transfer, $note) {
            (new ProductTransferApprove($transfer->id, $this->employeeId()))->approve($note ?: null);
        });
    }

    public function reject(ProductTransferService $transfer, ?string $note): void
    {
        $this->ensureStatus($transfer, StatusEnum::Requested->value, 'product_transfer_service');

        DB::transaction(function () use ($transfer, $note) {
            $transfer->employee_rejected_by = $this->employeeId();
            $transfer->rejected_at = now();
            $transfer->local_rejected_at = $this->localNow($transfer->to_location_id);
            $transfer->rejected_note = $note;
            $transfer->status = StatusEnum::Rejected->value;
            $transfer->updated_by = auth()->id();
            $transfer->save();

            (new ProductTransferStockUnreserved($transfer->id))->unreserved();
        });
    }

    public function cancel(ProductTransferService $transfer, ?string $note): void
    {
        $this->ensureStatus($transfer, StatusEnum::Requested->value, 'product_transfer_service');

        DB::transaction(function () use ($transfer, $note) {
            $transfer->employee_cancelled_by = $this->employeeId();
            $transfer->cancelled_at = now();
            $transfer->local_cancelled_at = $this->localNow($transfer->from_location_id);
            $transfer->cancelled_note = $note;
            $transfer->status = StatusEnum::Cancelled->value;
            $transfer->updated_by = auth()->id();
            $transfer->save();

            (new ProductTransferStockUnreserved($transfer->id))->unreserved();
        });
    }

    public function getLocationOptions(): Collection
    {
        return (new LocationService)->getLocationOptions();
    }
}
