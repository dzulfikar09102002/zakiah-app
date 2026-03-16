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

        // $startAt = request('start_at') ?? Carbon::now()->subDays(7)->startOfDay();
        // $endAt = request('end_at') ?? Carbon::now()->endOfDay();

        // simulasi tanggal 1 januari 2026 sampai 8 januari 2026
        $startAt = request('start_at') ?? (env('APP_ENV') !== 'production' ? Carbon::create(2026, 1, 1)->startOfDay() : Carbon::now()->subDays(7)->startOfDay());
        $endAt = request('end_at') ?? (env('APP_ENV') !== 'production' ? Carbon::create(2026, 1, 8)->endOfDay() : Carbon::now()->endOfDay());

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
            ->whereBetween('created_at', [$startAt, $endAt])
            ->whereIn('status', $statuses);

        // lokasi milik entity
        $locationIds = auth()->user()
            ->entity
            ->locations()
            ->pluck('id')
            ->toArray();

        $query->whereIn('location_id', $locationIds);

        // filter lokasi tertentu
        if (! empty(request('locs')) && request('select_all_location') === false) {
            $query->whereIn('location_id', request('locs'));
        }

        // exclude lokasi
        if (! empty(request('exclude_locs'))) {
            $query->whereNotIn('location_id', request('exclude_locs'));
        }

        // search
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
                    'cashier' => Str::replace('_', ' ', $t->cashier_first_name.($t->cashier_last_name === 'Kasir' ? '' : $t->cashier_last_name)),
                    'sales' => Str::replace('_', '', $t->employee_sales_first_name.($t->employee_sales_last_name === 'Sales' ? '' : $t->employee_sales_last_name)),
                    'member' => $t->customer_first_name.' - '.$t->customer_last_name,
                    'subtotal' => $t->subtotal,
                    'discount' => $t->discount_amount,
                    'adjustment' => $t->surcharge_amount,
                    'total' => $t->net_sales_after_tax,
                    'profit' => $t->net_profit,
                ];
            })
            ->withQueryString();
    }
}
