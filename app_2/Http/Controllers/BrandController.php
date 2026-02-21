<?php

namespace App\Http\Controllers;

use App\Helpers\UniqueCodeGenerator;
use App\Http\Requests\IndexBrandRequest;
use App\Http\Requests\ShowBrandRequest;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Brand;
use App\Models\BrandLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexBrandRequest $request)
    {
        $params = $request->validated();

        $datas = Brand::where('entity_id', $request->entity->id);

        if (array_key_exists('search', $params)) {
            $datas->where('name', 'like',  "%". $params['search'] ."%");
        }
        
        if (array_key_exists('code', $params)) {
            $datas->where('code', $params['code']);
        }

        if (array_key_exists('statuses', $params)) {
            $datas->whereIn('status', $params['statuses']);
        }

        return $datas->paginate($request->limit)->appends($params);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request)
    {
        $brand = new Brand();

        # start transcation
        DB::transaction(function () use ($request, $brand) {
            $params = $request->validated();
            # not in fillable
            $brand->entity_id = $request->entity->id;
            $brand->code = UniqueCodeGenerator::generateCode();
            $brand->initial = UniqueCodeGenerator::generateInitial();
            $brand->created_by = $request->user()->id;
            $brand->updated_by = $request->user()->id;
            $brand->fill($params);
            $brand->save();
    
            if ($request->has('location_ids')) {
                $brand->brandLocations()->createMany($request->get('location_ids'));
            }
        });

        $response = new BaseJsonResponse($brand->load('locations:id,name'));
        return $response->response();
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowBrandRequest $request, Brand $brand)
    {
        if ($request->entity->id != $brand->entity_id) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('entity.invalid_entity'));
            return $response->response(422);
        }

        $response = new BaseJsonResponse($brand->load('locations:id,name'));
        return $response->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        if ($request->entity->id != $brand->entity_id) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('entity.invalid_entity'));
            return $response->response(422);
        }

        # start transcation
        DB::transaction(function () use ($request, $brand) {
            $params = $request->validated();

            $brand->updated_by = $request->user()->id;
            $brand->update($params);
    
            if ($request->has('locations')) {
                $deleted_brand_location_ids = [];
                $create_brand_locations = [];

                foreach ($request->get('locations') as $location) {
                    if (!array_key_exists('id', $location)) {
                        array_push($create_brand_locations, $location);
                    } else if ($location['deleted']) {
                        array_push($deleted_brand_location_ids, $location['id']);
                    }
                }

                if (count($create_brand_locations) > 0) {
                    $brand->brandLocations()->createMany($create_brand_locations);
                }

                if (count($deleted_brand_location_ids) > 0) {
                    BrandLocation::where('brand_id', $brand->id)->whereIn('id', $deleted_brand_location_ids)->delete();
                }
            }
        });

        $response = new BaseJsonResponse($brand->load('locations:id,name'));
        return $response->response();
    }
}
