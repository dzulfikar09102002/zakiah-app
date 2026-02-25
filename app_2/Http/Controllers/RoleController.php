<?php

namespace App\Http\Controllers;

use App\Helpers\Constants\PageConstants;
use App\Http\Requests\DestroyRoleRequest;
use App\Http\Requests\IndexRoleRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Role;
use Illuminate\Contracts\Database\Query\Builder;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexRoleRequest $request)
    {
        //
        $params = $request->validated();

        $datas = Role::with(['parentRole:id,name'])
            ->where('name', 'like', "%" . $request->keyword . "%");

        if (array_key_exists('parent_ids', $params)) {
            $datas->whereIn('parent_id', $params['parent_ids']);
        }

        if (array_key_exists('show_system', $params) && $params['show_system'] == 'true') {
            $datas->where(function (Builder $query) use($request) {
                $query->where('entity_id', $request->entity->id)
                    ->orWhere('entity_id', null);
            });
        } else {
            $datas->where('entity_id', $request->entity->id);
        }

        return $datas->paginate($request->limit ?? PageConstants::DefaultLimit)->appends($params);
    }

    public function parent(IndexRoleRequest $request)
    {
        $params = $request->validated();

        $datas = Role::where('name', 'like', "%" . $request->keyword . "%")->where('entity_id', null);

        if (array_key_exists('parent_ids', $params)) {
            $datas->whereIn('id', $params['parent_ids']);
        }
        return $datas->paginate($request->limit ?? PageConstants::DefaultLimit)->appends($params);
    }
    public function store(StoreRoleRequest $request)
    {
        
        $params = $request->validated();

        $parentRole = Role::find($params['parent_id']);

        $role = new Role();
        $role->entity_id = $request->entity->id;
        $role->tier = $parentRole->tier;
        $role->level = $parentRole->level + 1;
        $role->fill($params);
        $role->entity_permission = array_merge($parentRole->entity_permission, $role->entity_permission);
        $role->location_permission = array_merge($parentRole->location_permission, $role->location_permission);
        $role->created_by = $request->user()->id;
        $role->updated_by = $request->user()->id;
        $role->save();

        $response = new BaseJsonResponse($role);
        return $response->response();
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $params = $request->validated();

        $parentRole = Role::find($params['parent_id']);

        $role->tier = $parentRole->tier;
        $role->level = $parentRole->level + 1;
        $role->fill($params);
        $role->entity_permission = array_merge($parentRole->entity_permission, $role->entity_permission);
        $role->location_permission = array_merge($parentRole->location_permission, $role->location_permission);
        $role->updated_by = $request->user()->id;
        $role->save();

        $response = new BaseJsonResponse($role->load((['parentRole:id,name'])));
        return $response->response();
    }

    public function destroy(DestroyRoleRequest $request, Role $role)
    {
        //
        # validate onlye entity_id != null

        $role->delete();

        $response = new BaseJsonResponse($role);
        return $response->response();
    }
}
