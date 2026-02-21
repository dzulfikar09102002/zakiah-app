<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreemployeeRequest;
use App\Http\Requests\UpdateemployeeRequest;
use App\Services\EmployeeService;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeService $service
    ) {}

    public function index()
    {
        $pagination = $this->service->getEmployees();
        return Inertia::render('employees/index', compact('pagination'));
    }
 
    public function create()
    {
        return Inertia::render('employees/create', $this->service->getFormOptions());
    }

    public function store(StoreemployeeRequest $request)
    {
        $entityId = auth()->user()?->entity?->id;

        $this->service->store(
            $request->validated(),
            $entityId,
            auth()->id()
        );

        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil ditambahkan');
    }

    public function update(UpdateemployeeRequest $request, Employee $employee)
    {
        $this->service->update(
            $employee,
            $request->validated(),
            auth()->id()
        );

        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil diperbarui');
    }

    public function destroy(Employee $employee)
    {
        $this->service->delete($employee);

        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil dihapus');
    }
}
