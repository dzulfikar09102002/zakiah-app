<?php

namespace App\Http\Controllers;

use App\Services\EmployeeReportSummaryService;
use App\Services\ReportPdfService;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EmployeeReportSummaryController extends Controller
{
    public function __construct(
        protected EmployeeReportSummaryService $employeeSummaryService
    ) {}

    public function index()
    {
        return Inertia::render('reports/employees/sales-perform', [
            'employeeSalesSummary' => fn () => $this->employeeSummaryService->getEmployeeSalesSummary(),
            'locationOptions' => fn () => $this->employeeSummaryService->getLocationOptions(),
        ]);
    }

    public function pdf(ReportPdfService $pdf): Response
    {
        return $pdf->stream(
            'Laporan Ringkasan Performa Sales',
            [
                ReportPdfService::column('employee_sales_name', 'Nama Sales'),
                ReportPdfService::column('sales_count', 'Jml Transaksi', 'number', true),
                ReportPdfService::column('refund_count', 'Jml Pengembalian', 'number', true),
                ReportPdfService::column('net_count', 'Transaksi Bersih', 'number', true),
                ReportPdfService::column('sales_quantity', 'Item Terjual', 'number', true),
                ReportPdfService::column('refund_quantity', 'Item Pengembalian', 'number', true),
                ReportPdfService::column('net_quantity', 'Item Bersih', 'number', true),
                ReportPdfService::column('sales_amount', 'Total Penjualan', 'currency', true),
                ReportPdfService::column('refund_amount', 'Total Pengembalian', 'currency', true),
                ReportPdfService::column('net_sales_amount', 'Penjualan Bersih', 'currency', true),
            ],
            $this->employeeSummaryService->getEmployeeSalesSummaryForPdf(),
            [
                'Periode' => $pdf->periodLabel(),
                'Lokasi' => $pdf->locationsLabel(false),
            ],
        );
    }
}
