<?php

namespace App\Http\Controllers\Kasir;

use App\Helpers\Services\Location\LocationFinder;
use App\Helpers\Services\Taking\TakingCreator;
use App\Helpers\Services\Taking\TakingIndexBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\IndexKasirTakingRequest;
use App\Http\Requests\Kasir\StoreKasirTakingRequest;
use App\Http\Requests\KasirStoreTakingRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Taking;
use Exception;
use Illuminate\Support\Facades\DB;

class KasirTakingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexKasirTakingRequest $request)
    {
        //
        $params = $request->validated();

        $location = (new LocationFinder($request->employee, $params['location_id']))->get();
        if ($location == null) {
            $response = new BaseJsonResponse(null, __('location.error.not_found'));
            return $response->response(404);
        }

        $builder = new TakingIndexBuilder($request->device, $request->entity, $location, $request->employee);

        $response = new BaseJsonResponse($builder->build()->response());
        return $response->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKasirTakingRequest $request)
    {
        //
        $taking = new Taking();
        
        # start transcation
        DB::beginTransaction();
        try {
            $params = $request->validated();
            $taking = (new TakingCreator($request->entity, $request->device, $params, $request->user(), $request->employee))->create();

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        $response = new BaseJsonResponse(["id" => $taking->id]);
        return $response->response();
    }
}
