<?php

namespace App\Http\Controllers;

use App\Services\DailySaleService;
use App\Services\SsoTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DailySaleController extends Controller
{
    public function __construct(
        private DailySaleService $service
    ) {}

    public function index()
    {
        $locationOptions = $this->service->getLocationOptions();
        $locationId = (int) request('loc', $locationOptions->first()['id'] ?? 0);
        $pagination = $this->service->getDailySales($locationId);

        return Inertia::render('revenue/daily-sales/index', compact('pagination', 'locationOptions', 'locationId'));
    }

    public function show(int $id)
    {
        $dailySale = $this->service->findDailySale($id);
        abort_if($dailySale == null, 404);

        $takings = $this->service->getTakings($dailySale);
        $saleTransactions = $this->service->getSaleTransactions($dailySale, $takings);
        $shiftOptions = $this->service->getShiftOptions($dailySale, $takings);
        $dailySale = $this->service->getDetail($dailySale, $takings);

        return Inertia::render('revenue/daily-sales/show', compact('dailySale', 'saleTransactions', 'shiftOptions'));
    }

    /**
     * Buka struk rekapan (taking penutup) di NewZakicaPOS lewat SSO, langsung cetak.
     */
    public function struk(Request $request, int $id, SsoTicketService $sso): RedirectResponse
    {
        $dailySale = $this->service->findDailySale($id);
        abort_if($dailySale == null, 404);
        abort_if($dailySale->taking_id == null, 422, 'Rekapan belum ditutup, struk belum tersedia.');
        abort_if($sso->newZakicaUrl() === null, 503, 'Alamat NewZakicaPOS belum dikonfigurasi (NEWZAKICA_URL).');

        return redirect()->away($sso->newZakicaStrukUrl(
            $request->user(),
            'rekapan',
            $dailySale->taking_id,
            $dailySale->location_id,
        ));
    }
}
