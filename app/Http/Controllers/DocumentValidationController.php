<?php

namespace App\Http\Controllers;

use App\Enums\OpnameSessionStatus;
use App\Enums\OpnameSessionType;
use App\Models\ProductOpnameSession;
use App\Services\DocumentSignatureService;
use App\Services\EntityBrandingService;
use App\Services\ProductOpnameReviewService;
use App\Services\ProductOpnameRevisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman publik tujuan QR tanda tangan elektronik (tanpa login).
 */
class DocumentValidationController extends Controller
{
    public function __invoke(
        Request $request,
        DocumentSignatureService $signatures,
        ProductOpnameReviewService $reviews,
        ProductOpnameRevisionService $revisions,
        EntityBrandingService $branding,
    ): Response {
        $key = $signatures->readKey($request->query('k'));

        $session = $key
            ? ProductOpnameSession::query()->with(['location:id,name', 'pic:id,first_name,last_name'])->find($key['session_id'])
            : null;

        $valid = $session
            && $session->currentStatus() === OpnameSessionStatus::Finished
            && $session->type === $key['type']
            && $session->pic_employee_id === $key['employee_id'];

        if (! $valid) {
            return Inertia::render('validasi-dokumen/index', ['document' => null]);
        }

        $type = $session->sessionType();
        $entityName = DB::table('entities')->where('id', $session->entity_id)->value('name');

        return Inertia::render('validasi-dokumen/index', [
            'document' => [
                'entity' => $branding->displayName($entityName),
                'logo' => $branding->logoUrl($entityName),
                'type' => $type->label(),
                'code' => $session->code,
                'location' => $session->location?->name,
                'opname_date' => $session->opname_date->toDateString(),
                'signed_at' => $session->end_at->toIso8601String(),
                'signer' => str_replace('_', ' ', trim($session->pic?->first_name.' '.$session->pic?->last_name)),
                'signer_role' => 'PJ Stok Opname',
                'summary' => $this->summary($session, $type, $reviews, $revisions),
            ],
        ]);
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    private function summary(
        ProductOpnameSession $session,
        OpnameSessionType $type,
        ProductOpnameReviewService $reviews,
        ProductOpnameRevisionService $revisions,
    ): array {
        if ($type === OpnameSessionType::Revision) {
            $rows = $revisions->rows($session);

            return [
                ['label' => 'Baris revisi', 'value' => (string) $rows->count()],
                ['label' => 'Total penyesuaian', 'value' => (string) $rows->sum('adjustment_qty')],
            ];
        }

        $summary = $reviews->summary($session);

        return [
            ['label' => 'Produk cocok', 'value' => (string) $summary['matched']],
            ['label' => 'Selisih diterima', 'value' => (string) $summary['accepted']],
            ['label' => 'Selisih ditolak', 'value' => (string) $summary['rejected']],
            ['label' => 'Total selisih stok', 'value' => (string) $summary['difference_stock']],
        ];
    }
}
