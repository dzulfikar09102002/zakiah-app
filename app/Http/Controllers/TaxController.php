<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexTaxRequest;
use App\Http\Requests\ShowTaxRequest;
use App\Http\Requests\StoreTaxRequest;
use App\Http\Requests\UpdateTaxRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Tax;
use Illuminate\Support\Facades\DB;

class TaxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexTaxRequest $request)
    {
        $params = $request->validated();

        $payments = Tax::where('name', 'like', "%" . $request->search . "%")->where('entity_id', $request->entity->id);

        return $payments->paginate($request->limit)->appends($params);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaxRequest $request)
    {
        $data = new Tax();

        # start transcation
        DB::transaction(function () use ($request, $data) {
            $params = $request->validated();
            # not in fillable
            $data->entity_id = $request->entity->id;
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
    public function show(ShowTaxRequest $request, Tax $tax)
    {
        if ($request->entity->id != $tax->entity_id) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('entity.invalid_entity'));
            return $response->response(422);
        }

        $response = new BaseJsonResponse($tax);
        return $response->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaxRequest $request, Tax $tax)
    {
        if ($request->entity->id != $tax->entity_id) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('entity.invalid_entity'));
            return $response->response(422);
        }

        # start transcation
        DB::transaction(function () use ($request, $tax) {
            $params = $request->validated();

            $tax->updated_by = $request->user()->id;
            $tax->update($params);
        });

        $response = new BaseJsonResponse($tax);
        return $response->response();
    }
}
