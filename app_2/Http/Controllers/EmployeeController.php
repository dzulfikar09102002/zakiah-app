<?php

namespace App\Http\Controllers;

use App\Helpers\Constants\PageConstants;
use App\Helpers\Exceptions\NotFoundException;
use App\Helpers\Services\Employee\EmployeeSaver;
use App\Http\Requests\DestroyEmployeeRequest;
use App\Http\Requests\IndexEmployeeRequest;
use App\Http\Requests\ShowEmployeeRequest;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexEmployeeRequest $request)
    {
        //
        $params = $request->validated();

        $datas = Employee::with(['role:id,name', 'user:id,email', 'employeeLocations:id,employee_id,location_id,role_id,entity_permission'])
            ->where('entity_id', $request->entity->id);

        if (array_key_exists('keyword', $params)) {
            $keyword =  "%" . $params['keyword'] . "%";

            $datas->where(function (Builder $builder) use($keyword) {
                $builder
                    ->where('first_name', 'like', $keyword)
                    ->orWhere('last_name', 'like', $keyword);
            });
        }

        if (array_key_exists('roles', $params)) {
            $datas->whereIn('role_id', $params['roles']);
        }

        return $datas->paginate($request->limit ?? PageConstants::DefaultLimit)->appends($params);
    }

    public function dropdown(IndexEmployeeRequest $request)
    {
        $params = $request->validated();

        $datas = Employee::where('entity_id', $request->entity->id)
            ->where(function (Builder $query) use ($request) {
                $query->orWhere('first_name', 'like', "%" . $request->keyword . "%")
                    ->orWhere('last_name', $request->keyword);
            })
            ->select('id')
            ->selectRaw("concat(first_name, ' ', last_name) as name");

        if ($request->exists('selected_ids')) {
            $datas = $datas->whereIn('id', $request->selected_ids);
        }

        if ($request->exists('exclude_ids')) {
            $datas = $datas->whereNotIn('id', $request->exclude_ids);
        }

        if ($request->exists('roles')) {
            $datas = $datas->whereIn('role_id', $request->Not);
        }

        return $datas->cursorPaginate($request->limit)->appends($params);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request)
    {
        //
        $employee = (new EmployeeSaver($request->entity, (new Employee()), $request->validated()))->create();

        $response = new BaseJsonResponse(['id' => $employee->id]);
        return $response->response();
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowEmployeeRequest $request, int $id)
    {
        //
        $employee = Employee::where('entity_id', $request->entity->id)
            ->where('id', $id)
            ->first();

        if ($employee == null) {
            throw NotFoundException::withMessages([
                'employee' => __('general.not_found'),
            ]);
        }

        $response = new BaseJsonResponse($this->detailResponse($employee));
        return $response->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, int $id)
    {
        //
        $employee = Employee::where('entity_id', $request->entity->id)
                    ->where('id', $id)
                    ->first();

        if ($employee == null) {
            throw NotFoundException::withMessages([
                'employee' => __('general.not_found'),
            ]);
        }

        $employee = (new EmployeeSaver($request->entity, $employee, $request->validated()))->create();

        $response = new BaseJsonResponse($this->detailResponse($employee));
        return $response->response();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyEmployeeRequest $request, int $id)
    {
        //
        $employee = Employee::where('entity_id', $request->entity->id)
                    ->where('id', $id)
                    ->first();

        if ($employee == null) {
            throw NotFoundException::withMessages([
                'employee' => __('general.not_found'),
            ]);
        }

        $employee->delete();

        $response = new BaseJsonResponse(['id' => $employee->id]);
        return $response->response();
    }

    private function detailResponse(Employee $employee)
    {
        return $employee->load([
            'role:id,name',
        ]);
    }
}
