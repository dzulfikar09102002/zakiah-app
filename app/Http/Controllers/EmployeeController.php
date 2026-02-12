<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreemployeeRequest;
use App\Http\Requests\UpdateemployeeRequest;
use App\Http\Services\EmployeeService;
use App\Models\Employee;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeService $service
    ) {}

    public function index()
    {
        $perPage = request('per_page', 10);
        $entityId = auth()->user()?->entity?->id;

        $employees = $this->service->paginateByEntity($entityId, $perPage);

        return Inertia::render('employee/index', compact('employees'));
    }

    public function create()
    {
        return Inertia::render('employee/create', $this->service->getFormOptions());
    }

    public function store(StoreemployeeRequest $request)
    {
        $entityId = auth()->user()?->entity?->id;

        $this->service->store(
            $request->validated(),
            $entityId,
            auth()->id()
        );

        return redirect()->route('employee.index')->with('success', 'Karyawan berhasil ditambahkan');
    }

    public function update(UpdateemployeeRequest $request, Employee $employee)
    {
        $this->service->update(
            $employee,
            $request->validated(),
            auth()->id()
        );

        return redirect()->route('employee.index')->with('success', 'Karyawan berhasil diperbarui');
    }

    public function destroy(Employee $employee)
    {
        $this->service->delete($employee);

        return redirect()->route('employee.index')->with('success', 'Karyawan berhasil dihapus');
    }
}
