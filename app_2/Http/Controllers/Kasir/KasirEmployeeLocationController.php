<?php

namespace App\Http\Controllers\Kasir;

use App\Helpers\Constants\PageConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\IndexKasirEmployeeLocationRequest;
use App\Models\EmployeeLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

class KasirEmployeeLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexKasirEmployeeLocationRequest $request)
    {
        //
        $params = $request->validated();

        $entityId = $request->entity->id;
        $employeeId = $request->employee->id;
        $keyword = $request->keyword;
        
        $datas = EmployeeLocation::with([
            'location:id,name',
            'role:id,name',
        ])
        ->join('locations', function (JoinClause $join) use($entityId) {
            $join->on('locations.id', '=', 'employee_locations.location_id')
                 ->where('locations.entity_id', '=', $entityId);
        })
        ->join('employees', function (JoinClause $join) use($entityId, $employeeId) {
            $join->on('employees.id', '=', 'employee_locations.employee_id')
                 ->where('employees.entity_id', '=', $entityId)
                 ->where('employees.id', '=', $employeeId);
        })
        ->where(function (Builder $query) use($keyword) {
            $query->where('locations.name', 'like', "%" . $keyword . "%");
        })
        ->select('employee_locations.id', 'employee_locations.code', 'employee_locations.location_id', 'employee_locations.role_id');

        return $datas->paginate($request->limit ?? PageConstants::DefaultLimit)->appends($params);
    }
}
