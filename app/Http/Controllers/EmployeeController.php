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
        $roles = $this->service->getRoles();
        $locations = $this->service->getLocations();
        return Inertia::render('employees/index', compact('pagination', 'roles','locations'));
    }
 
    public function store(StoreemployeeRequest $request)
    {
        dd($request->validated());
        $this->service->store($request->validated());
        return to_route('employees.index')->with('success', 'Karyawan berhasil ditambahkan');
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
