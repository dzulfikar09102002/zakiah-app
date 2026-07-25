<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreemployeeRequest;
use App\Http\Requests\UpdateemployeeRequest;
use App\Models\Employee;
use App\Services\EmployeeService;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeService $service
    ) {}

    public function index()
    {
        $pagination = $this->service->getEmployees();
        $roles = $this->service->getRoles();
        $locations = $this->service->getLocations();
        $existingEmails = $this->service->getEmailsByEntity();

        return Inertia::render('employees/index', compact('pagination', 'roles', 'locations', 'existingEmails'));
    }
    public function deleted()
    {
        $pagination = $this->service->getDeletedEmployees();
        $roles = $this->service->getRoles();
        $locations = $this->service->getLocations();
        $onlyTrashed = true;
        return Inertia::render('employees/index', compact('pagination', 'roles', 'locations', 'onlyTrashed'));
    }
    public function store(StoreemployeeRequest $request)
    {
        $this->service->store($request->validated());

        return to_route('employees.index')->with('success', 'Karyawan berhasil ditambahkan');
    }

    public function update(UpdateemployeeRequest $request, Employee $employee)
    {
        $this->service->update($employee, $request->validated());

        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil diperbarui');
    }

    public function destroy(Employee $employee)
    {
        $this->service->delete($employee);
        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil dihapus');
    }

    public function restore (int $id)
    {
        $this->service->restore($id);
        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil dipulihkan');
    }
}
