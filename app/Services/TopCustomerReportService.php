<?php

namespace App\Services;

use App\Models\Location;
use App\Models\SaleTransaction;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TopCustomerReportService
{
    public const TOP_OPTIONS = [10, 25, 50, 100];

    public const DEFAULT_TOP = 10;

    public function getLocationOptions(): Collection
    {
        return (new LocationService)->getLocations()->get()->map(fn (Location $location) => [
            'label' => Str::title(Str::lower($location->name)),
            'value' => $location->id,
        ]);
    }

    public function top(): int
    {
        $top = (int) request('top', self::DEFAULT_TOP);

        return in_array($top, self::TOP_OPTIONS, true) ? $top : self::DEFAULT_TOP;
    }

    /**
     * Top pelanggan untuk halaman laporan (filter lokasi: select_all_location = '1', default semua).
     */
    public function getTopCustomers(): Collection
    {
        $startAt = request('start_at')
            ? Carbon::parse(request('start_at'))->startOfDay()
            : Carbon::now()->subDays(7)->startOfDay();

        $endAt = request('end_at')
            ? Carbon::parse(request('end_at'))->endOfDay()
            : Carbon::now()->endOfDay();

        $selectAll = request()->has('select_all_location')
            ? request('select_all_location') == '1'
            : true;

        return $this->topCustomers($this->top(), $startAt, $endAt, $selectAll);
    }

    /**
     * Total spend = net_sales_after_tax - refunded_amount dari transaksi berstatus ok
     * yang punya pelanggan, diurutkan dari spend terbesar.
     */
    public function topCustomers(int $limit, CarbonInterface $startAt, CarbonInterface $endAt, bool $selectAll): Collection
    {
        $locationIds = auth()->user()
            ->entity
            ->locations()
            ->pluck('id')
            ->toArray();

        $locs = array_map('intval', (array) request('locs', []));
        $excludeLocs = array_map('intval', (array) request('exclude_locs', []));

        $query = SaleTransaction::query()
            ->whereIn('location_id', $locationIds)
            ->whereNotNull('customer_id')
            ->where('status', 'ok')
            ->whereBetween('local_sales_at', [$startAt, $endAt]);

        if ($selectAll && count($excludeLocs) > 0) {
            $query->whereNotIn('location_id', $excludeLocs);
        } elseif (! $selectAll && count($locs) > 0) {
            $query->whereIn('location_id', $locs);
        }

        $totals = $query
            ->select('customer_id')
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('
                SUM(
                    CAST(net_sales_after_tax AS SIGNED)
                    - CAST(refunded_amount AS SIGNED)
                ) as total_spend
            ')
            ->selectRaw('MAX(local_sales_at) as last_sales_at')
            ->groupBy('customer_id')
            ->orderByDesc('total_spend')
            ->limit($limit);

        return DB::query()
            ->fromSub($totals, 'totals')
            ->leftJoin('customers', 'customers.id', '=', 'totals.customer_id')
            ->leftJoin('customer_categories', 'customer_categories.id', '=', 'customers.customer_category_id')
            ->select(
                'totals.customer_id',
                'totals.transaction_count',
                'totals.total_spend',
                'totals.last_sales_at',
                'customers.first_name',
                'customers.last_name',
                'customers.phone_number',
                'customers.phone_number_country_code',
                'customer_categories.name as category_name',
            )
            ->orderByDesc('totals.total_spend')
            ->get()
            ->values()
            ->map(fn ($row, int $index) => $this->transformRow($row, $index));
    }

    private function transformRow(object $row, int $index): array
    {
        $transactionCount = (int) $row->transaction_count;
        $totalSpend = (int) $row->total_spend;
        $phone = $row->phone_number
            ? trim(($row->phone_number_country_code ? '+'.ltrim((string) $row->phone_number_country_code, '+').' ' : '').$row->phone_number)
            : null;

        return [
            'rank' => $index + 1,
            'customer_id' => (int) $row->customer_id,
            'customer_name' => trim(($row->first_name ?? '').' '.($row->last_name ?? '')) ?: '-',
            'phone_number' => $phone ?? '-',
            'category' => $row->category_name ?? '-',
            'transaction_count' => $transactionCount,
            'total_spend' => $totalSpend,
            'average_spend' => $transactionCount > 0 ? (int) round($totalSpend / $transactionCount) : 0,
            'last_sales_at' => $row->last_sales_at,
        ];
    }
}
