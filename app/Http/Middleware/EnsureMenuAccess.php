<?php

namespace App\Http\Middleware;

use App\Services\MenuAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menolak route backoffice yang tidak dicentang di Hak Akses karyawan.
 * Hanya aktif bila ACCESS_CONTROL_ENFORCED=true dan tabel menu sudah ada.
 */
class EnsureMenuAccess
{
    public function __construct(
        private MenuAccessService $access,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            $this->access->canRoute($request->route()?->getName()),
            403,
            'Hak Akses Anda tidak mencakup menu ini.',
        );

        return $next($request);
    }
}
