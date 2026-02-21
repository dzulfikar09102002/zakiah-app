<?php

namespace App\Http\Controllers;

use App\Helpers\UniqueCodeGenerator;
use App\Http\Requests\IndexProductUnitRequest;
use App\Http\Requests\ShowProductUnitRequest;
use App\Http\Requests\StoreProductUnitRequest;
use App\Http\Requests\UpdateProductUnitRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexProductUnitRequest $request)
    {
        $params = $request->validated();

        $datas = ProductUnit::where('name', 'like', "%" . $request->search . "%")->where('entity_id', $request->entity->id);

        if ($request->exists('statuses')) {
            $datas = $datas->whereIn('status', $request->statuses);
        }

        return $datas->paginate($request->limit)->appends($params);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductUnitRequest $request)
    {
        $data = new ProductUnit();

        # start transcation
        DB::transaction(function () use ($request, $data) {
            $params = $request->validated();
            # not in fillable
            $data->entity_id = $request->entity->id;
            $data->search_name = UniqueCodeGenerator::generateSearchName($params['name']);
            $data->created_by = $request->user()->id;
            $data->updated_by = $request->user()->id;

            $data->fill($params);
            $data->save();
        });

        $response = new BaseJsonResponse($data);
        return $response->response();
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowProductUnitRequest $request, ProductUnit $productUnit)
    {
        if ($request->entity->id != $productUnit->entity_id) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('entity.invalid_entity'));
            return $response->response(422);
        }

        $response = new BaseJsonResponse($productUnit);
        return $response->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductUnitRequest $request, ProductUnit $productUnit)
    {
        if ($request->entity->id != $productUnit->entity_id) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('entity.invalid_entity'));
            return $response->response(422);
        }

        # start transcation
        DB::transaction(function () use ($request, $productUnit) {
            $params = $request->validated();
            if ($request->exists('name')) {
                $productUnit->search_name = UniqueCodeGenerator::generateSearchName($params['name']);
            }
            $productUnit->updated_by = $request->user()->id;
            $productUnit->fill($params);
            $productUnit->save();
        });

        $response = new BaseJsonResponse($productUnit);
        return $response->response();
    }
}
