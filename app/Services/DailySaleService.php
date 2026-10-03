<?php

namespace App\Services;

use App\Models\DailySale;
use App\Models\Location;
use App\Models\SaleTransaction;
use App\Models\Taking;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DailySaleService
{
    public function getLocationOptions(): Collection
    {
        return Location::where('entity_id', auth()->user()?->entity?->id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Location $location) => [
                'id' => $location->id,
                'name' => Str::title(Str::lower($location->name)),
            ]);
    }

    public function getDailySales(?int $locationId): LengthAwarePaginator
    {
        $startAt = request('start_at')
            ? Carbon::parse(request('start_at'))->startOfDay()
            : Carbon::now()->subDays(7)->startOfDay();

        $endAt = request('end_at')
            ? Carbon::parse(request('end_at'))->endOfDay()
            : Carbon::now()->endOfDay();

        return DailySale::where('entity_id', auth()->user()?->entity?->id)
            ->where('location_id', $locationId)
            ->whereBetween('local_sales_at', [$startAt, $endAt])
            ->select(['id', 'taking_id', 'local_sales_at', 'sales_amount', 'refund_amount', 'employee_id', 'employee_first_name', 'employee_last_name'])
            ->orderByDesc('id')
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function findDailySale(int $id): ?DailySale
    {
        return DailySale::with(['location:id,name'])
            ->where('entity_id', auth()->user()?->entity?->id)
            ->where('id', $id)
            ->first();
    }

    public function getDetail(DailySale $dailySale, Collection $takings): array
    {
        return array_merge(
            $dailySale->toArray(),
            ['takingAll' => $this->takingAll($dailySale, $takings)],
            ['takings' => $this->takingResponse($takings)],
        );
    }

    /**
     * Shift options for filtering the transaction table: only the shifts of this
     * day plus its own end-of-day closing.
     */
    public function getShiftOptions(DailySale $dailySale, Collection $takings): Collection
    {
        return $takings
            ->filter(fn (Taking $taking) => $taking->is_shift || $taking->id === $dailySale->taking_id)
            ->values()
            ->map(fn (Taking $taking) => [
                'value' => (string) $taking->id,
                'label' => $this->shiftLabel($taking),
            ]);
    }

    /**
     * Sale transactions closed in this day's takings, each with its sold items.
     */
    public function getSaleTransactions(DailySale $dailySale, Collection $takings): LengthAwarePaginator
    {
        $shiftOptions = $this->getShiftOptions($dailySale, $takings);
        $takingIds = $shiftOptions->pluck('value')->map(fn ($id) => (int) $id)->all();

        $selectedTakingId = (int) request('taking_id');
        if (in_array($selectedTakingId, $takingIds)) {
            $takingIds = [$selectedTakingId];
        }

        $shiftLabels = $shiftOptions->pluck('label', 'value');

        return SaleTransaction::with([
            'saleTransactionDetails:id,sale_transaction_id,product_name,product_sku,product_unit_name,quantity,sell_price,promo_amount,discount_amount,notes,status',
        ])
            ->where('entity_id', $dailySale->entity_id)
            ->whereIn('taking_id', $takingIds)
            ->orderByDesc('local_sales_at')
            ->paginate(request('per_page', 10))
            ->through(fn (SaleTransaction $t) => [
                'id' => $t->id,
                'sales_no' => $t->sales_no,
                'shift' => $shiftLabels[(string) $t->taking_id] ?? '-',
                'date' => $t->local_sales_at,
                'order_type' => $t->order_type_name,
                'customer' => trim($t->customer_first_name.' '.$t->customer_last_name),
                'cashier' => trim(Str::replace('_', ' ', $t->cashier_first_name.' '.($t->cashier_last_name === 'Kasir' ? '' : $t->cashier_last_name))),
                'subtotal' => $t->subtotal,
                'discount' => $t->discount_amount + $t->promo_amount,
                'net_sales_after_tax' => $t->net_sales_after_tax,
                'status' => $t->status,
                'details' => $t->saleTransactionDetails,
            ])
            ->withQueryString();
    }

    private function shiftLabel(Taking $taking): string
    {
        return $taking->is_shift ? 'Shift '.($taking->shift_number ?? '-') : 'Tutup Shift';
    }

    /**
     * Takings (shift closings) that make up a daily sale. When the day is not
     * closed yet, only the open shifts are included.
     */
    public function getTakings(DailySale $dailySale): Collection
    {
        $parentId = $dailySale->taking_id;

        $takings = Taking::with([
            'location:id,name',
            'takingPaymentDetails.paymentMethod:id,name',
            'takingTaxDetails',
        ])
            ->where('entity_id', $dailySale->entity_id)
            ->where('checkpoint_device_id', $dailySale->checkpoint_device_id)
            ->where('location_id', $dailySale->location_id)
            ->orderBy('id');

        if ($parentId == null) {
            $takings->where('parent_id', $parentId)->where('is_shift', true);
        } else {
            $takings->where(function (Builder $builder) use ($parentId) {
                $builder
                    ->where(function (Builder $builder) use ($parentId) {
                        $builder->where('parent_id', $parentId)->where('is_shift', true);
                    })
                    ->orWhere(function (Builder $builder) {
                        $builder->where('parent_id', null)->where('is_shift', false);
                    });
            });
        }

        return $takings->get();
    }

    private function takingResponse(Collection $takings): Collection
    {
        return $takings->map(fn (Taking $taking) => array_merge($taking->toArray(), [
            'salesSummaries' => $this->getSalesSummaries($taking),
        ]));
    }

    private function getSalesSummaries(Taking $taking): array
    {
        return [
            'grossSales' => $taking->gross_sales,
            'discountBeforeTax' => $taking->discount_amount,
            'promoBeforeTax' => $taking->promo_amount,
            'surchargeBeforeTax' => $taking->surcharge_amount,
            'freeOfChargeBeforeTax' => $taking->free_of_charge_amount,
            'netSales' => $taking->net_sales,
            'serviceCharge' => $taking->service_charge,
            'taxAmount' => $taking->tax_amount,
            'roundingAmount' => $taking->rounding_amount,
            'netSalesAfterTax' => $taking->net_sales_after_tax,
        ];
    }

    private function takingAll(DailySale $dailySale, Collection $takings): array
    {
        $sumFields = [
            'counted_amount',
            'discount_amount',
            'discount_amount_refund',
            'free_of_charge_amount',
            'free_of_charge_amount_refund',
            'gross_refund',
            'gross_sales',
            'net_sales',
            'net_sales_refund',
            'net_sales_after_tax',
            'net_sales_after_tax_refund',
            'promo_amount',
            'promo_amount_refund',
            'recorded_amount',
            'refund_count',
            'sales_count',
            'service_charge',
            'service_charge_refund',
            'surcharge_amount',
            'surcharge_amount_refund',
            'tax_amount',
            'tax_amount_refund',
        ];

        $all = array_merge(
            [
                'local_taking_at' => $dailySale->local_sales_at,
                'location' => $dailySale->location,
                'employee_id' => $dailySale->employee_id,
                'employee_first_name' => $dailySale->employee_first_name,
                'employee_last_name' => $dailySale->employee_last_name,
            ],
            array_fill_keys($sumFields, 0),
        );

        $payments = [];

        foreach ($takings as $taking) {
            foreach ($sumFields as $field) {
                $all[$field] += $taking[$field];
            }

            foreach ($taking->takingPaymentDetails as $detail) {
                $paymentMethodId = $detail->payment_method_id;

                $payments[$paymentMethodId] ??= [
                    'counted_amount' => 0,
                    'recorded_amount' => 0,
                    'difference_amount' => 0,
                    'sales_amount' => 0,
                ];

                $payments[$paymentMethodId]['counted_amount'] += $detail->counted_amount;
                $payments[$paymentMethodId]['recorded_amount'] += $detail->recorded_amount;
                $payments[$paymentMethodId]['difference_amount'] += $detail->difference_amount;
                $payments[$paymentMethodId]['sales_amount'] += $detail->sales_amount;
                $payments[$paymentMethodId]['payment_method'] = $detail->paymentMethod;
            }
        }

        $all['taking_payment_details'] = array_values($payments);

        return $all;
    }
}
