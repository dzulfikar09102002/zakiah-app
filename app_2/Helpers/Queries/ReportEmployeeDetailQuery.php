<?php

namespace App\Helpers\Queries;

use App\Models\EmployeeSalesSummary;

class ReportEmployeeDetailQuery extends ReportSaleBaseQuery
{
    protected array $employees, $excludeEmployees;
    protected bool $selectAllEmployee;

    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->selectAllEmployee = ($this->params['select_all_employee'] ?? 'false') == 'true';
        if (array_key_exists('employees', $this->params)) {
            $this->employees = $this->params['employees'];
        } else {
            $this->employees = [];
        }

        if (array_key_exists('exclude_employees', $this->params)) {
            $this->excludeEmployees = $this->params['exclude_employees'];
        } else {
            $this->excludeEmployees = [];
        }
    }

    protected function generateBody(): array
    {
        $bodies = array();
        foreach ($this->getData() as $data) {
            array_push($bodies, [
                'employee_sales_name' => $data['employee_sales_name'],
                'location_name' => $data['location_name'],
                'local_sales_date' => $data['local_sales_date'],
                'sales_amount' => $data['sales_amount'],
                'refund_amount' => $data['refund_amount'],
                'net_sales_amount' => $data['net_sales_amount'],
                'sales_count' => $data['sales_count'],
                'refund_count' => $data['refund_count'],
                'net_count' => $data['net_count'],
                'sales_quantity' => $data['sales_quantity'],
                'refund_quantity' => $data['refund_quantity'],
                'net_quantity' => $data['net_quantity'],
            ]);
        }

        return $bodies;
    }

    private function baseQuery()
    {
        $query = EmployeeSalesSummary::where('employee_sales_id', '>', 0)->where('location_id', '>', 0);

        return $this->buildQuery($query);
    }

    private function buildQuery($query) {
        $this->buildWhereLocation($query);
        $this->buildWhereSalesTime($query);
        $this->buildWhereEmployeeSales($query);

        return $query;
    }

    protected function buildTotal(): int
    {
        return $this->baseQuery()->count('id');
    }

    private function getData()
    {
        $query = $this->baseQuery()
            ->select(
                'employee_sales_name', 'location_name', 'local_sales_date',
                'sales_amount', 'refund_amount', 'net_sales_amount',
                'sales_count', 'refund_count', 'net_count',
                'sales_quantity', 'refund_quantity', 'net_quantity',
            );

       return $this->queryLimitOffset($query)->get();
    }

    protected function buildWhereSalesTime($query)
    {
        $query
            ->where('local_sales_date', '>=', $this->startAt)
            ->where('local_sales_date', '<=', $this->endAt);
    }

    protected function buildWhereEmployeeSales($query)
    {
        if ($this->selectAllEmployee && count($this->excludeEmployees) > 0) {
            $query->whereNotIn('employee_sales_id', $this->excludeEmployees);
        } else if ($this->selectAllEmployee == false) {
            $query->whereIn('employee_sales_id', $this->employees);
        }
    }
}
