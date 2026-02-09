<?php

namespace App\Http\Controllers;

use App\Models\employee;
use App\Http\Requests\StoreemployeeRequest;
use App\Http\Requests\UpdateemployeeRequest;
use App\Models\Location;
use App\Models\Role;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = request('per_page', 10);
    
        $employees = Employee::paginate($perPage)->withQueryString();
    
        $roles = Role::select('id','name')->get()->keyBy('id');
    
        $employees->getCollection()->transform(function($employee) use ($roles) {
            $employee->role_name = $roles[$employee->role_id]->name ?? "-";
            return $employee;
        });
    
        return Inertia::render('employee/index', compact('employees'));
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('employee/create', [
            'roles' => Role::select('id', 'name')->get(), 'locations' => Location::select('id', 'name')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreemployeeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(employee $employee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(employee $employee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateemployeeRequest $request, employee $employee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(employee $employee)
    {
        //
    }
}
