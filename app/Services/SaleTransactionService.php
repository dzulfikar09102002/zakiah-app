<?php

namespace App\Services;

use App\Enums\StatusEnum;
use App\Models\Employee;
use App\Models\OrderType;
use App\Models\ProductStockMovement;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionDetail;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleTransactionService
{
    public function getOrderTypeOptions(): Collection
    {
        return OrderType::where('entity_id', auth()->user()?->entity?->id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (OrderType $orderType) => [
                'label' => $orderType->name,
                'value' => (string) $orderType->id,
            ]);
    }

    public function getSaleTransactions(): LengthAwarePaginator
    {
        $search = request('search', '');

        $startAt = request('start_at')
            ? Carbon::parse(request('start_at'))->startOfDay()
            : Carbon::now()->subDays(7)->startOfDay();

        $endAt = request('end_at')
            ? Carbon::parse(request('end_at'))->endOfDay()
            : Carbon::now()->endOfDay();

        $query = SaleTransaction::query()
            ->where('entity_id', auth()->user()?->entity?->id)
            ->whereBetween('local_sales_at', [$startAt, $endAt]);

        $selectAll = request('select_all_location', '1') == '1';
        $locs = array_map('intval', (array) request()->input('locs', []));
        $excludeLocs = array_map('intval', (array) request()->input('exclude_locs', []));

        if ($selectAll && count($excludeLocs) > 0) {
            $query->whereNotIn('location_id', $excludeLocs);
        } elseif (! $selectAll && count($locs) > 0) {
            $query->whereIn('location_id', $locs);
        }

        $orderTypes = array_filter((array) request()->input('order_types', []), fn ($value) => $value !== 'all');
        if (count($orderTypes) > 0) {
            $query->whereIn('order_type_id', array_map('intval', $orderTypes));
        }

        $status = request('status', 'all');
        if (in_array($status, ['ok', StatusEnum::Void->value])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('sales_no', 'like', "%$search%")
                    ->orWhere('receipt_no', 'like', "%$search%")
                    ->orWhere('customer_first_name', 'like', "%$search%")
                    ->orWhere('customer_last_name', 'like', "%$search%");
            });
        }

        return $query
            ->orderByRaw("case status when 'ok' then 0 else 1 end")
            ->orderByDesc('local_sales_at')
            ->paginate(request('per_page', 10))
            ->through(fn (SaleTransaction $t) => [
                'id' => $t->id,
                'sales_no' => $t->sales_no,
                'receipt_no' => $t->receipt_no,
                'location' => $t->location_name,
                'order_type' => $t->order_type_name,
                'date' => $t->local_sales_at,
                'customer' => trim($t->customer_first_name.' '.$t->customer_last_name),
                'cashier' => $this->employeeName($t->cashier_first_name, $t->cashier_last_name, 'Kasir'),
                'sales' => $this->employeeName($t->employee_sales_first_name, $t->employee_sales_last_name, 'Sales'),
                'net_sales_after_tax' => $t->net_sales_after_tax,
                'refunded_amount' => $t->refunded_amount,
                'status' => $t->status,
            ])
            ->withQueryString();
    }

    public function findSaleTransaction(int $id): ?SaleTransaction
    {
        return SaleTransaction::where('entity_id', auth()->user()?->entity?->id)
            ->where('id', $id)
            ->first();
    }

    public function getDetail(SaleTransaction $saleTransaction): SaleTransaction
    {
        return $saleTransaction->load([
            'entity:id,name,image_url',
            'location',
            'customer:id,first_name,last_name',
            'employeeSales:id,first_name,last_name',
            'cashier:id,first_name,last_name',
            'orderType:id,name',
            'voidBy:id,first_name,last_name',
            'saleTransactionDetails',
            'saleTransactionPayments',
        ]);
    }

    /**
     * Void a sale and return every sold item back to stock through a stock movement.
     */
    public function void(SaleTransaction $saleTransaction, Employee $employee, array $data): void
    {
        DB::transaction(function () use ($saleTransaction, $employee, $data) {
            $timezone = new DateTimeZone($saleTransaction->location_timezone);

            $saleTransaction->update([
                'void_by' => $employee->id,
                'void_at' => new DateTime,
                'local_void_at' => (new DateTime)->setTimezone($timezone),
                'void_reason' => $data['reason'],
                'void_notes' => $data['notes'],
                'status' => StatusEnum::Void->value,
            ]);

            $details = SaleTransactionDetail::with(['product'])
                ->where('sale_transaction_id', $saleTransaction->id)
                ->get();

            foreach ($details as $detail) {
                $detail->status = 'void';
                $detail->save();

                $movement = new ProductStockMovement;

                $movement->product_id = $detail->product_id;
                $movement->location_id = $saleTransaction->location_id;
                $movement->product_unit_id = $detail->product_unit_id;
                $movement->original_product_unit_id = $detail->product_unit_id;

                $movement->resource_id = $detail->id;
                $movement->resource_type = $detail::class;

                $movement->original_stock_out = 0;
                $movement->original_stock_in = $detail->quantity;
                $movement->original_buying_price = $detail->product->cost_of_goods_sold;
                $movement->conversion_stock = 1;

                $movement->stock_in = $movement->original_stock_in * $movement->conversion_stock;
                $movement->stock_out = $movement->original_stock_out * $movement->conversion_stock;
                $movement->buying_price = $movement->original_buying_price * $movement->conversion_stock;

                $movement->save();
            }
        });
    }

    private function employeeName(?string $firstName, ?string $lastName, string $placeholderLastName): string
    {
        $lastName = $lastName === $placeholderLastName ? '' : $lastName;

        return trim(Str::replace('_', ' ', $firstName.' '.$lastName));
    }
}
