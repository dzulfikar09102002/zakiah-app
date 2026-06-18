<?php

namespace App\Services;

use App\Models\Location;
use App\Models\SaleTransaction;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SaleReportService
{
    public function getLocationOptions()
    {
        return (new LocationService)->getLocations()->get()->map(fn (Location $location) => [
            'label' => Str::title(Str::lower($location->name)),
            'value' => $location->id,
        ]);
    }

    public function getSaleReports()
    {
        $search = request('search', '');
        $entityId = auth()->user()?->entity?->id;

        $startAt = request('start_at')
            ? Carbon::parse(request('start_at'))->startOfDay()
            : Carbon::now()->subDays(7)->startOfDay();

        $endAt = request('end_at')
            ? Carbon::parse(request('end_at'))->endOfDay()
            : Carbon::now()->endOfDay();

        $statuses = request('statuses', ['ok']);

        $query = SaleTransaction::query()
            ->with([
                'location',
                'customer',
                'orderType',
                'saleTransactionPayments.paymentMethod',
                'saleTransactionDetails',
                'saleTransactionPromos.promo',
            ])
            ->where('entity_id', $entityId)
            ->whereBetween('local_sales_at', [$startAt, $endAt])
            ->whereIn('status', $statuses);

        // semua lokasi milik entity
        $locationIds = auth()->user()
            ->entity
            ->locations()
            ->pluck('id')
            ->toArray();

        $query->whereIn('location_id', $locationIds);

        $selectAll = request('select_all_location') == '1';
        $locs = request()->input('locs', []);
        $excludeLocs = request()->input('exclude_locs', []);

        $locs = array_map('intval', (array) $locs);
        $excludeLocs = array_map('intval', (array) $excludeLocs);

        if ($selectAll && count($excludeLocs) > 0) {
            $query->whereNotIn('location_id', $excludeLocs);
        } elseif (!$selectAll && count($locs) > 0) {
            $query->whereIn('location_id', $locs);
        }

        if (request('discount') === 'available') {
            $query->where(function ($q) {
                $q->where('promo_amount_before_tax', '>', 0)
                ->orWhere('discount_amount_before_tax', '>', 0);
            });
        }

        if (request('discount') === 'none') {
            $query->where('promo_amount_before_tax', 0)
                ->where('discount_amount_before_tax', 0);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%$search%")
                ->orWhere('invoice_number', 'like', "%$search%");
            });
        }

        return $query
            ->orderBy('created_at')
            ->paginate(request('per_page', 10))
            ->through(function ($t) {
                return [
                    'transaction_no' => $t->sales_no,
                    'location' => $t->location_name,
                    'date' => $t->local_sales_at,
                    'cashier' => trim(
                        Str::replace(
                            '_',
                            ' ',
                            $t->cashier_first_name . ' ' .
                            ($t->cashier_last_name === 'Kasir'
                                ? ''
                                : $t->cashier_last_name)
                        )
                    ),

                    'sales' => trim(
                        Str::replace(
                            '_',
                            ' ',
                            $t->employee_sales_first_name . ' ' .
                            ($t->employee_sales_last_name === 'Sales'
                                ? ''
                                : $t->employee_sales_last_name)
                        )
                    ),
                    'member' => $t->customer_first_name . ' - ' . $t->customer_last_name,
                    'subtotal' => $t->subtotal,
                    'discount' => $t->promo_amount_before_tax + $t->discount_amount_before_tax,
                    'adjustment' => $t->surcharge_amount_before_tax,
                    'total' => $t->net_sales_after_tax,
                    'profit' => $t->net_profit,
                ];
            })
            ->withQueryString();
    }
}
