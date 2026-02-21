<?php

namespace App\Http\Controllers\Kasir;

use App\Helpers\Constants\PageConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\IndexKasirEmployeeRequest;
use App\Models\Employee;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

class KasirEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexKasirEmployeeRequest $request)
    {
        //
        $params = $request->validated();

        $entityId = $request->entity->id;
        $keyword = $request->keyword;
        $locationId = $request->loc_id;

        $sales = Role::where('entity_id', null)->where('parent_id', null)->where('level', 1)->where('name', 'Sales')->first();

        $datas = Employee::where(function (Builder $query) use($keyword) {
            $query->where('first_name', 'like', "%" . $keyword . "%")->orWhere('last_name', 'like', "%" . $keyword . "%");
        })
        ->where('entity_id', $entityId)
        ->join('employee_locations', function (JoinClause $join) use($locationId, $sales) {
            $join->on('employees.id', '=', 'employee_locations.employee_id')
                ->where('employee_locations.location_Id', $locationId)
                ->where('employee_locations.role_id', $sales->id);
        })
        ->select('employees.id', 'first_name', 'last_name');
        
        return $datas->paginate($request->limit ?? PageConstants::DefaultLimit)->appends($params);
    }
}
