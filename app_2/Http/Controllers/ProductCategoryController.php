<?php

namespace App\Http\Controllers;

use App\Helpers\UniqueCodeGenerator;
use App\Http\Requests\IndexProductCategoryRequest;
use App\Http\Requests\ShowProductCategoryRequest;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexProductCategoryRequest $request)
    {
        $params = $request->validated();

        $datas = ProductCategory::with(['parent:id,name'])->where('name', 'like', "%" . $request->search . "%")->where('entity_id', $request->entity->id);

        if ($request->exists('statuses')) {
            $datas = $datas->whereIn('status', $request->statuses);
        }

        return $datas->paginate($request->limit)->appends($params);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductCategoryRequest $request)
    {
        $data = new ProductCategory();

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

        return $this->buildResponse($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowProductCategoryRequest $request, ProductCategory $productCategory)
    {
        if ($request->entity->id != $productCategory->entity_id) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('entity.invalid_entity'));
            return $response->response(422);
        }

        return $this->buildResponse($productCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory)
    {
        if ($request->entity->id != $productCategory->entity_id) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('entity.invalid_entity'));
            return $response->response(422);
        }

        # start transcation
        DB::transaction(function () use ($request, $productCategory) {
            $params = $request->validated();
            if ($request->exists('name')) {
                $productCategory->search_name = UniqueCodeGenerator::generateSearchName($params['name']);
            }

            $productCategory->updated_by = $request->user()->id;
            $productCategory->fill($params);
            $productCategory->save();
        });

        return $this->buildResponse($productCategory);
    }

    public function dropdown(IndexProductCategoryRequest $request)
    {
        $params = $request->validated();

        $datas = ProductCategory::with([
            'parent:id,name',
        ])
            ->where('entity_id', $request->entity->id)
            ->where('name', 'like', "%" . $request->keyword . "%")
            ->select('id', 'name');

        if ($request->exists('selected_ids')) {
            $datas = $datas->whereNotIn('id', $request->selected_ids);
        }

        return $datas->cursorPaginate($request->limit)->appends($params);
    }

    private function buildResponse(ProductCategory $productCategory)
    {
        $response = new BaseJsonResponse($productCategory->load([
            'parent:id,name',
        ]));

        return $response->response();
    }
}
