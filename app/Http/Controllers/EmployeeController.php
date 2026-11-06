<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreemployeeRequest;
use App\Http\Requests\UpdateemployeeRequest;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeService $service
    ) {}

    public function index(): Response
    {
        return $this->page(false);
    }

    public function deleted(): Response
    {
        return $this->page(true);
    }

    public function store(StoreemployeeRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('employees.index')->with('success', 'Karyawan berhasil ditambahkan');
    }

    public function update(UpdateemployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->ensureOwnedByEntity($employee);

        $this->service->update($employee, $request->validated());

        return back()->with('success', 'Karyawan berhasil diperbarui');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->ensureOwnedByEntity($employee);

        $this->service->delete($employee);

        return back()->with('success', 'Karyawan berhasil dihapus');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->service->restore($id);

        return back()->with('success', 'Karyawan berhasil dipulihkan');
    }

    private function page(bool $onlyTrashed): Response
    {
        return Inertia::render('employees/index', [
            'pagination' => $this->service->getEmployees($onlyTrashed),
            'roleOptions' => $this->service->getRoleOptions(),
            'accessRoleOptions' => $this->service->getAccessRoleOptions(),
            'locations' => $this->service->getLocations(),
            'onlyTrashed' => $onlyTrashed,
        ]);
    }
}
