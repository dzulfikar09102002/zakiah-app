<?php

namespace App\Http\Controllers;

use App\Services\EmployeeReportDetailService;
use App\Services\LocationService;
use App\Services\ReportPdfService;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EmployeeReportDetailController extends Controller
{
    public function __construct(
        protected EmployeeReportDetailService $employeeReportDetailService,
        protected LocationService $locationService,
    ) {}

    public function index()
    {
        return Inertia::render('reports/employees/sales-perform-detail', [
            'employeeSalesDetail' => fn () => $this->employeeReportDetailService->getEmployeeSalesDetail(),
            'locationOptions' => fn () => $this->employeeReportDetailService->getLocationOptions(),
        ]);
    }

    public function pdf(ReportPdfService $pdf): Response
    {
        return $pdf->stream(
            'Laporan Detail Performa Sales',
            [
                ReportPdfService::column('local_sales_date', 'Tanggal', 'date'),
                ReportPdfService::column('employee_sales_name', 'Nama Sales'),
                ReportPdfService::column('location_name', 'Lokasi'),
                ReportPdfService::column('sales_count', 'Transaksi', 'number', true),
                ReportPdfService::column('refund_count', 'Pengembalian', 'number', true),
                ReportPdfService::column('net_count', 'Transaksi Bersih', 'number', true),
                ReportPdfService::column('sales_quantity', 'Item Terjual', 'number', true),
                ReportPdfService::column('refund_quantity', 'Item Pengembalian', 'number', true),
                ReportPdfService::column('net_quantity', 'Item Bersih', 'number', true),
                ReportPdfService::column('sales_amount', 'Total Penjualan', 'currency', true),
                ReportPdfService::column('refund_amount', 'Total Pengembalian', 'currency', true),
                ReportPdfService::column('net_sales_amount', 'Penjualan Bersih', 'currency', true),
            ],
            $this->employeeReportDetailService->getEmployeeSalesDetailForPdf(),
            [
                'Periode' => $pdf->periodLabel(),
                'Lokasi' => $pdf->locationsLabel(false),
            ],
        );
    }
}
