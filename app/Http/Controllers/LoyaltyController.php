<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoyaltyRequest;
use App\Http\Requests\UpdateLoyaltyRequest;
use App\Services\LoyaltyService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LoyaltyController extends Controller
{
    public function __construct(
        protected LoyaltyService $service
    ) {}

    public function index(): Response
    {
        $pagination = $this->service->getLoyalties();

        return Inertia::render('loyalties/index', compact('pagination'));
    }

    public function create(): Response
    {
        return Inertia::render('loyalties/form', ['loyalty' => null, 'mode' => 'create']);
    }

    public function store(StoreLoyaltyRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('loyalties.index')->with('success', 'Loyalty berhasil ditambahkan');
    }

    public function show(int $loyalty): Response
    {
        return Inertia::render('loyalties/form', [
            'loyalty' => $this->service->find($loyalty),
            'mode' => 'show',
        ]);
    }

    public function edit(int $loyalty): Response
    {
        return Inertia::render('loyalties/form', [
            'loyalty' => $this->service->find($loyalty),
            'mode' => 'edit',
        ]);
    }

    public function update(UpdateLoyaltyRequest $request, int $loyalty): RedirectResponse
    {
        $this->service->update($this->service->find($loyalty), $request->validated());

        return to_route('loyalties.index')->with('success', 'Loyalty berhasil diperbarui');
    }

    public function destroy(int $loyalty): RedirectResponse
    {
        $this->service->destroy($this->service->find($loyalty));

        return back()->with('success', 'Loyalty berhasil dihapus');
    }

    public function activate(int $id): RedirectResponse
    {
        $this->service->activate($this->service->find($id));

        return back()->with('success', 'Loyalty berhasil diaktifkan');
    }

    public function deactivate(int $id): RedirectResponse
    {
        $this->service->deactivate($this->service->find($id));

        return back()->with('success', 'Loyalty berhasil dinonaktifkan');
    }

    public function archive(int $id): RedirectResponse
    {
        $this->service->archive($this->service->find($id));

        return back()->with('success', 'Loyalty berhasil diarsipkan');
    }
}
