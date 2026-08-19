<?php

namespace App\Services;

use App\Models\EmployeeSalesSummary;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EmployeeReportDetailService
{
    public function getLocationOptions()
    {
        return (new LocationService)->getLocationOptions();
    }
    public function getEmployeeSalesDetail()
    {
        $startAt = request('start_at')
            ? Carbon::parse(request('start_at'))->startOfDay()
            : Carbon::now()->subDays(7)->startOfDay();

        $endAt = request('end_at')
            ? Carbon::parse(request('end_at'))->endOfDay()
            : Carbon::now()->endOfDay();

        $query = EmployeeSalesSummary::query();

        $locationIds = auth()->user()
            ->entity
            ->locations()
            ->pluck('id')
            ->toArray();

        $query->whereIn('location_id', $locationIds);

        $selectAllLocation = request('select_all_location') == '1';
        $locs = array_map('intval', (array) request('locs', []));
        $excludeLocs = array_map('intval', (array) request('exclude_locs', []));

        if ($selectAllLocation && count($excludeLocs) > 0) {
            $query->whereNotIn('location_id', $excludeLocs);
        } elseif (!$selectAllLocation && count($locs) > 0) {
            $query->whereIn('location_id', $locs);
        }

        $selectAllEmployee = request('select_all_employee') == '1';

        $employees = array_map(
            'intval',
            (array) request('employees', [])
        );

        $excludeEmployees = array_map(
            'intval',
            (array) request('exclude_employees', [])
        );

        if ($selectAllEmployee && count($excludeEmployees) > 0) {
            $query->whereNotIn('employee_sales_id', $excludeEmployees);
        } elseif (!$selectAllEmployee && count($employees) > 0) {
            $query->whereIn('employee_sales_id', $employees);
        }

        $query
            ->where('employee_sales_id', '>', 0)
            ->where('location_id', '>', 0)
            ->whereBetween('local_sales_date', [
                $startAt,
                $endAt,
            ]);

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('employee_sales_name', 'like', "%{$search}%")
                    ->orWhere('location_name', 'like', "%{$search}%");
            });
        }

        return $query
            ->select(
                'employee_sales_name',
                'location_name',
                'local_sales_date',
                'sales_amount',
                'refund_amount',
                'net_sales_amount',
                'sales_count',
                'refund_count',
                'net_count',
                'sales_quantity',
                'refund_quantity',
                'net_quantity',
            )
            ->orderByDesc('local_sales_date')
            ->orderBy('employee_sales_name')
            ->paginate(request('per_page', 10))
            ->through(fn ($row) => [
                'employee_sales_name' => $row->employee_sales_name,
                'location_name' => $row->location_name,
                'local_sales_date' => $row->local_sales_date,

                'sales_amount' => (int) $row->sales_amount,
                'refund_amount' => (int) $row->refund_amount,
                'net_sales_amount' => (int) $row->net_sales_amount,

                'sales_count' => (int) $row->sales_count,
                'refund_count' => (int) $row->refund_count,
                'net_count' => (int) $row->net_count,

                'sales_quantity' => (int) $row->sales_quantity,
                'refund_quantity' => (int) $row->refund_quantity,
                'net_quantity' => (int) $row->net_quantity,
            ])
            ->withQueryString();
    }
}

