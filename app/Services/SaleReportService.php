<?php

namespace App\Services;

use App\Models\SaleTransaction;

class SaleReportService
{
    public function getSaleReports(array $params)
    {
        $search = request('search', '');
        $entityId = auth()->user()?->entity?->id;

        $startAt = $params['start_at'];
        $endAt = $params['end_at'];

        $statuses = $params['statuses'] ?? ['ok'];

        $query = SaleTransaction::query()
            ->with([
                'location',
                'customer',
                'orderType',
                'saleTransactionPayments.paymentMethod',
                'saleTransactionDetails',
                'saleTransactionPromos.promo'
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
        if (!empty($params['locs']) && $params['select_all_location'] === false) {
            $query->whereIn('location_id', $params['locs']);
        }

        // exclude lokasi
        if (!empty($params['exclude_locs'])) {
            $query->whereNotIn('location_id', $params['exclude_locs']);
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