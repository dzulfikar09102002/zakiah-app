<?php

namespace App\Http\Controllers;

use App\Services\SsoTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SsoController extends Controller
{
    public function __construct(
        private SsoTicketService $service
    ) {}

    /**
     * Pindah ke NewZakicaPOS tanpa login ulang.
     */
    public function toNewZakica(Request $request): RedirectResponse
    {
        abort_if($this->service->newZakicaUrl() === null, 503, 'Alamat NewZakicaPOS belum dikonfigurasi (NEWZAKICA_URL).');

        return redirect()->away($this->service->newZakicaSignInUrl(
            $request->user(),
            $this->service->safeRedirectPath($request->query('redirect')),
        ));
    }

    /**
     * Masuk dari NewZakicaPOS dengan tiket SSO.
     */
    public function signIn(Request $request): RedirectResponse
    {
        $user = $this->service->consume((string) $request->query('ticket', ''));

        if ($user === null) {
            return to_route('login')->with('status', 'Tautan masuk tidak valid atau sesi sudah berakhir. Silakan login.');
        }

        if (! $this->service->canSignIn($user)) {
            return to_route('login')->with('status', 'Akun ini tidak terdaftar sebagai karyawan aktif.');
        }

        // Sudah login sebagai user yang sama: cukup lanjut, sesi tidak perlu diganti.
        if (Auth::id() !== $user->id) {
            if (Auth::check()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
            }

            Auth::login($user);
            $request->session()->regenerate();
        }

        return redirect($this->service->safeRedirectPath($request->query('redirect')) ?? route('dashboard.index'));
    }
}
