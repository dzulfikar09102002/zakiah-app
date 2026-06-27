<?php

namespace App\Services;
use App\Models\EmployeeSalesSummary;
use Carbon\Carbon;

class SalesPerformService
{
    public function getEmployeeSalesSummary()
    {
        $query = EmployeeSalesSummary::query();

        $locationIds = auth()->user()
            ->entity
            ->locations()
            ->pluck('id')
            ->toArray();

        $query->whereIn(
            'location_id',
            $locationIds
        );

        $query->where(
            'employee_sales_id',
            '>',
            0
        );

        $query->where(
            'location_id',
            '>',
            0
        );

        $query->where(
            'local_sales_date',
            '>=',
            request('start_at')
                ? Carbon::parse(request('start_at'))->startOfDay()
                : today()->startOfDay()
        );

        $query->where(
            'local_sales_date',
            '<=',
            request('end_at')
                ? Carbon::parse(request('end_at'))->endOfDay()
                : today()->endOfDay()
        );

        $selectAll = request('select_all_location') == 'true';

        $locs = array_map(
            'intval',
            (array) request('locs', [])
        );

        $excludeLocs = array_map(
            'intval',
            (array) request('exclude_locs', [])
        );

        if ($selectAll && count($excludeLocs) > 0) {
            $query->whereNotIn(
                'location_id',
                $excludeLocs
            );
        } elseif (!$selectAll && count($locs) > 0) {
            $query->whereIn(
                'location_id',
                $locs
            );
        }

        $selectAllEmployee = request('select_all_employee') == 'true';

        $employees = array_map(
            'intval',
            (array) request('employees', [])
        );

        $excludeEmployees = array_map(
            'intval',
            (array) request('exclude_employees', [])
        );

        if ($selectAllEmployee && count($excludeEmployees) > 0) {
            $query->whereNotIn(
                'employee_sales_id',
                $excludeEmployees
            );
        } elseif (!$selectAllEmployee && count($employees) > 0) {
            $query->whereIn(
                'employee_sales_id',
                $employees
            );
        }

        return $query
            ->select(
                'employee_sales_name'
            )
            ->selectRaw('
                SUM(sales_amount)
                as sales_amount
            ')
            ->selectRaw('
                SUM(refund_amount)
                as refund_amount
            ')
            ->selectRaw('
                SUM(net_sales_amount)
                as net_sales_amount
            ')
            ->selectRaw('
                SUM(sales_count)
                as sales_count
            ')
            ->selectRaw('
                SUM(refund_count)
                as refund_count
            ')
            ->selectRaw('
                SUM(net_count)
                as net_count
            ')
            ->selectRaw('
                SUM(sales_quantity)
                as sales_quantity
            ')
            ->selectRaw('
                SUM(refund_quantity)
                as refund_quantity
            ')
            ->selectRaw('
                SUM(net_quantity)
                as net_quantity
            ')
            ->groupBy(
                'employee_sales_name'
            )
            ->orderByDesc(
                'net_sales_amount'
            )
            ->get();
    }
}