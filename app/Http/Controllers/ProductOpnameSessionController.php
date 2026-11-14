<?php

namespace App\Http\Controllers;

use App\Enums\OpnameReviewDecision;
use App\Enums\OpnameSessionStatus;
use App\Http\Requests\AddProductOpnameScansRequest;
use App\Http\Requests\CancelProductOpnameScansRequest;
use App\Http\Requests\FinishProductOpnameSessionRequest;
use App\Http\Requests\LockProductOpnameReviewRequest;
use App\Http\Requests\StoreProductOpnameScanRequest;
use App\Http\Requests\StoreProductOpnameSessionRequest;
use App\Http\Requests\UnlockProductOpnameReviewRequest;
use App\Models\ProductOpnameSession;
use App\Models\ProductOpnameSessionScan;
use App\Services\DocumentSignatureService;
use App\Services\ProductOpnameReportService;
use App\Services\ProductOpnameReviewService;
use App\Services\ProductOpnameScanService;
use App\Services\ProductOpnameSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ProductOpnameSessionController extends Controller
{
    public function __construct(
        protected ProductOpnameSessionService $sessions,
        protected ProductOpnameScanService $scans,
        protected ProductOpnameReviewService $reviews,
    ) {}

    public function index(): Response
    {
        return Inertia::render('stock-opname-sessions/index', [
            'pagination' => Inertia::defer(fn () => $this->sessions->getSessions()),
            'locationOptions' => $this->sessions->getLocationOptions(),
            'legacyDocuments' => $this->sessions->getPendingLegacyDocuments(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('stock-opname-sessions/form', [
            'session' => null,
            'locationOptions' => $this->sessions->getLocationOptions(),
            'employeeOptions' => $this->sessions->getEmployeeOptions(),
            'currentEmployeeId' => $this->sessions->currentEmployeeOption(),
        ]);
    }

    public function store(StoreProductOpnameSessionRequest $request): RedirectResponse
    {
        $session = $this->sessions->store($request->validated());

        return to_route('product-opname-sessions.show', $session->id)
            ->with('success', $session->start_at ? 'Sesi SO dimulai' : 'Sesi SO dijadwalkan');
    }

    public function show(Request $request, int $id): Response
    {
        $session = $this->sessions->find($id);
        $this->ensureMember($session);

        $status = $session->currentStatus();
        $isPic = $this->sessions->isPic($session);
        $canSeeReview = ($status === OpnameSessionStatus::Finished || ($status === OpnameSessionStatus::Review && $isPic));

        $filter = in_array($request->query('filter'), ['all', 'difference', 'matched'], true)
            ? (string) $request->query('filter')
            : 'difference';
        $search = (string) $request->query('search', '');

        return Inertia::render('stock-opname-sessions/show', [
            'session' => $session,
            'viewer' => [
                'employee_id' => $request->user()?->employee?->id,
                'is_pic' => $isPic,
                'can_manage_schedule' => $this->sessions->canManageSchedule($session),
                'can_cancel' => $this->sessions->canCancel($session),
                'can_correct' => ProductOpnameSessionScan::supportsCorrections() && $this->sessions->canCorrect($session),
            ],
            'review' => $canSeeReview ? [
                'pagination' => $this->reviews->paginate($session, $filter, $search),
                'summary' => $this->reviews->summary($session),
                'suspect_count' => $this->reviews->suspectCount($session),
                'filter' => $filter,
                'search' => $search,
            ] : null,
        ]);
    }

    public function edit(int $id): Response
    {
        $session = $this->sessions->find($id);
        $this->ensureCanManageSchedule($session);

        return Inertia::render('stock-opname-sessions/form', [
            'session' => $session,
            'locationOptions' => $this->sessions->getLocationOptions(),
            'employeeOptions' => $this->sessions->getEmployeeOptions(),
            'currentEmployeeId' => $this->sessions->currentEmployeeOption(),
        ]);
    }

    public function update(StoreProductOpnameSessionRequest $request, int $id): RedirectResponse
    {
        $session = $this->sessions->find($id);
        $this->ensureCanManageSchedule($session);

        $this->sessions->update($session, $request->validated());

        return to_route('product-opname-sessions.show', $id)->with('success', 'Sesi SO diperbarui');
    }

    public function destroy(int $id): RedirectResponse
    {
        $session = $this->sessions->find($id);
        $this->ensureCanManageSchedule($session);

        $this->sessions->destroy($session);

        return to_route('product-opname-sessions.index')->with('success', 'Sesi SO dihapus');
    }

    /**
     * Layar QR untuk PJ. QR berisi alamat sesi, jadi pemeriksaan login dan daftar karyawan
     * tetap dilakukan saat alamat itu dibuka.
     */
    public function share(Request $request, DocumentSignatureService $signatures, int $id): Response|RedirectResponse
    {
        $session = $this->picSession($id);

        if ($session->currentStatus() === OpnameSessionStatus::Finished) {
            return to_route('product-opname-sessions.show', $id);
        }

        $url = route('product-opname-sessions.show', $id);

        return Inertia::render('stock-opname-sessions/share', [
            'session' => [
                'id' => $session->id,
                'code' => $session->code,
                'location' => $session->location?->name,
                'opname_date' => $session->opname_date->toDateString(),
                'pic' => trim($session->pic?->first_name.' '.$session->pic?->last_name),
                'employees_count' => $session->employees->count(),
            ],
            'url' => $url,
            'qr' => $signatures->qrSvg($url),
            'isLocalAddress' => in_array($request->getHost(), ['localhost', '127.0.0.1', '::1'], true),
        ]);
    }

    public function start(int $id): RedirectResponse
    {
        $this->sessions->start($this->picSession($id));

        return back()->with('success', 'Sesi SO dimulai');
    }

    public function closeScan(int $id): RedirectResponse
    {
        $this->sessions->closeScan($this->picSession($id));

        return back()->with('success', 'Scan ditutup, silakan review hasil SO');
    }

    public function reopenScan(int $id): RedirectResponse
    {
        $this->sessions->reopenScan($this->picSession($id));

        return back()->with('success', 'Scan dibuka kembali');
    }

    public function finish(FinishProductOpnameSessionRequest $request, int $id): RedirectResponse
    {
        $this->sessions->finish($this->picSession($id));

        return back()->with('success', 'Hasil review disimpan, sesi SO ditutup');
    }

    public function cancel(int $id): RedirectResponse
    {
        $this->sessions->cancel($this->picSession($id));

        return to_route('product-opname-sessions.index')->with('success', 'Sesi SO dibatalkan');
    }

    public function storeScan(StoreProductOpnameScanRequest $request, int $id): JsonResponse
    {
        $session = $this->sessions->find($id);
        $this->ensureMember($session);

        // Tanpa barcode berarti tanpa bukti barang fisik, jadi hanya PJ yang boleh.
        abort_if(
            $request->filled('product_id') && ! $this->sessions->isPic($session),
            403,
            'Input produk manual hanya untuk PJ SO.',
        );

        return response()->json($this->scans->record($session, $request->validated()));
    }

    public function productScans(int $id, int $productId): JsonResponse
    {
        $session = $this->correctableSession($id);

        return response()->json($this->scans->productScans($session, $productId));
    }

    public function addScans(AddProductOpnameScansRequest $request, int $id, int $productId): JsonResponse
    {
        $session = $this->correctableSession($id);

        $this->scans->addScans(
            $session,
            $productId,
            (int) $request->validated('qty'),
            $this->sessions->isPic($session),
        );

        return response()->json(['ok' => true]);
    }

    public function cancelScans(CancelProductOpnameScansRequest $request, int $id, int $productId): JsonResponse
    {
        $session = $this->correctableSession($id);

        $this->scans->cancelScans(
            $session,
            $productId,
            (int) $request->validated('qty'),
            $request->validated('reason'),
            $this->sessions->isPic($session),
        );

        return response()->json(['ok' => true]);
    }

    public function live(Request $request, int $id): JsonResponse
    {
        $session = $this->sessions->find($id);
        $this->ensureMember($session);

        return response()->json($this->scans->live($session, (int) $request->query('since', 0)));
    }

    public function lockReview(LockProductOpnameReviewRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();
        $decision = OpnameReviewDecision::from((int) $data['decision']);

        $this->reviews->lock($this->picSession($id), (int) $data['product_id'], $decision, trim($data['note']));

        return back()->with('success', $decision === OpnameReviewDecision::Accepted
            ? 'Dikunci: pakai stok SO'
            : 'Dikunci: pakai stok sistem');
    }

    public function unlockReview(UnlockProductOpnameReviewRequest $request, int $id): RedirectResponse
    {
        $this->reviews->unlock($this->picSession($id), (int) $request->validated('product_id'));

        return back()->with('success', 'Kunci dibuka, keputusan bisa diubah');
    }

    public function report(ProductOpnameReportService $report, int $id): HttpResponse
    {
        $session = $this->sessions->find($id);
        $this->ensureMember($session);

        abort_unless($session->currentStatus() === OpnameSessionStatus::Finished, 404);

        return $report->stream($session);
    }

    private function ensureCorrectionsAvailable(): void
    {
        abort_unless(ProductOpnameSessionScan::supportsCorrections(), 409, 'Fitur koreksi scan belum aktif.');
    }

    /**
     * Anggota sesi boleh koreksi selama scan dibuka; setelah ditutup hanya PJ.
     */
    private function correctableSession(int $id): ProductOpnameSession
    {
        $this->ensureCorrectionsAvailable();

        $session = $this->sessions->find($id);
        $this->ensureMember($session);

        abort_unless(
            $this->sessions->canCorrect($session),
            403,
            $this->sessions->isPic($session)
                ? 'Scan hanya bisa dibatalkan sebelum sesi selesai.'
                : 'Scan sudah ditutup, hanya PJ SO yang bisa membatalkan scan.',
        );

        return $session;
    }

    private function picSession(int $id): ProductOpnameSession
    {
        $session = $this->sessions->find($id);

        abort_unless($this->sessions->isPic($session), 403, 'Hanya PJ SO yang bisa melakukan aksi ini.');

        return $session;
    }

    private function ensureMember(ProductOpnameSession $session): void
    {
        abort_unless(
            $this->sessions->isMember($session),
            403,
            'Anda tidak terdaftar pada sesi SO ini.',
        );
    }

    private function ensureCanManageSchedule(ProductOpnameSession $session): void
    {
        abort_unless($this->sessions->canManageSchedule($session), 403, 'Anda tidak bisa mengubah sesi ini.');
    }
}
