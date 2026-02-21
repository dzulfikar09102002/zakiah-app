<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexPaymentMethodRequest;
use App\Http\Requests\ShowPaymentMethodRequest;
use App\Http\Requests\StorePaymentMethodRequest;
use App\Http\Requests\UpdatePaymentMethodRequest;
use App\Models\PaymentMethod;
use App\Http\Responses\BaseJsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexPaymentMethodRequest $request)
    {
        $params = $request->validated();

        $datas = PaymentMethod::where('name', 'like', "%" . $request->search . "%")->where('entity_id', $request->entity->id);
        
        if ($request->exists('status')) {
            $datas = $datas->where('status', $request->status);
        }

        if ($request->exists('kinds')) {
            $datas = $datas->whereIn('kind', $request->kinds);
        }

        if ($request->exists('statuses')) {
            $datas = $datas->whereIn('status', $request->statuses);
        }

        return $datas->paginate($request->limit)->appends($params);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentMethodRequest $request)
    {
        $data = new PaymentMethod();

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
    public function show(ShowPaymentMethodRequest $request, PaymentMethod $paymentMethod)
    {
        if ($request->entity->id != $paymentMethod->entity_id) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('entity.invalid_entity'));
            return $response->response(422);
        }

        $response = new BaseJsonResponse($paymentMethod);
        return $response->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod)
    {
        if ($request->entity->id != $paymentMethod->entity_id) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('entity.invalid_entity'));
            return $response->response(422);
        }

        # start transcation
        DB::transaction(function () use ($request, $paymentMethod) {
            $params = $request->validated();

            $paymentMethod->updated_by = $request->user()->id;
            $paymentMethod->update($params);
        });

        $response = new BaseJsonResponse($paymentMethod);
        return $response->response();
    }
}
