<?php

namespace App\Services;

use App\Models\SaleTransaction;

class SaleReportService
{
    public function getSaleReports()
    {
        $search = request('search', '');
        $entityId = auth()->user()?->entity?->id;

        $startAt = request('start_at');
        $endAt = request('end_at');

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
            ->orderByDesc('created_at')
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }
}
