<?php

namespace App\Services;

use App\Models\SaleTransaction;
use Carbon\Carbon;

class SaleReportService
{
    public function getSaleReports()
    {
        $search = request('search', '');
        $entityId = auth()->user()?->entity?->id;

        // $startAt = request('start_at') ?? Carbon::now()->subDays(7)->startOfDay();
        // $endAt = request('end_at') ?? Carbon::now()->endOfDay();

        // simulasi tanggal 1 januari 2026 sampai 8 januari 2026
        $startAt = request('start_at') ?? Carbon::create(2026, 1, 1)->startOfDay();
        $endAt = request('end_at') ?? Carbon::create(2026, 1, 8)->endOfDay();

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
                    'member' => $t->customer_first_name,
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
