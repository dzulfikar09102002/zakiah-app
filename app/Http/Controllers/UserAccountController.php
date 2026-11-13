<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserAccountRequest;
use App\Http\Requests\UpdateUserAccountRequest;
use App\Services\UserAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Akun login karyawan, terpisah dari data karyawan (nama, toko, role).
 * Tab Terhapus memakai ?status=deleted di route index, supaya tetap satu route
 * yang dilindungi Hak Akses "Lihat".
 */
class UserAccountController extends Controller
{
    public function __construct(
        private UserAccountService $service
    ) {}

    public function index(Request $request): Response
    {
        $onlyTrashed = $request->query('status') === 'deleted';

        return Inertia::render('user-accounts/index', [
            'pagination' => Inertia::defer(fn () => $this->service->getAccounts($onlyTrashed)),
            'employeeOptions' => $onlyTrashed ? [] : $this->service->employeeOptions(),
            'onlyTrashed' => $onlyTrashed,
            'search' => (string) $request->query('search', ''),
        ]);
    }

    public function store(StoreUserAccountRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return back()->with('success', 'Akun login dibuat');
    }

    public function update(UpdateUserAccountRequest $request, int $id): RedirectResponse
    {
        $this->service->update($this->service->find($id), $request->validated());

        return back()->with('success', 'Akun login diperbarui');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($this->service->find($id));

        return back()->with('success', 'Akun login dihapus');
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate(['email' => ['nullable', 'email', 'max:255']]);

        $this->service->restore($id, isset($data['email']) ? strtolower(trim($data['email'])) : null);

        return back()->with('success', 'Akun login dipulihkan');
    }
}
