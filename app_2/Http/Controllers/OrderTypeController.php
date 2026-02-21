<?php

namespace App\Http\Controllers;

use App\Helpers\Exceptions\NotFoundException;
use App\Helpers\UniqueCodeGenerator;
use App\Http\Requests\IndexOrderTypeRequest;
use App\Http\Requests\ShowOrderTypeRequest;
use App\Http\Requests\StoreOrderTypeRequest;
use App\Http\Requests\UpdateOrderTypeRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\OrderType;
use Illuminate\Support\Facades\DB;

class OrderTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexOrderTypeRequest $request)
    {
        $params = $request->validated();

        $datas = OrderType::where('name', 'like', "%" . $request->search . "%")->where('entity_id', $request->entity->id);

        if (array_key_exists('statuses', $params)) {
            $datas = $datas->whereIn('status', $params['statuses']);
        }

        return $datas->paginate($request->limit)->appends($params);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderTypeRequest $request)
    {
        $data = new OrderType();

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
    public function show(ShowOrderTypeRequest $request, OrderType $orderType)
    {
        if ($request->entity->id != $orderType->entity_id) {
            throw NotFoundException::withMessages([
                'order_type' => __('general.not_found'),
            ]);
        }

        $response = new BaseJsonResponse($orderType);
        return $response->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderTypeRequest $request, OrderType $orderType)
    {
        if ($request->entity->id != $orderType->entity_id) {
            throw NotFoundException::withMessages([
                'order_type' => __('general.not_found'),
            ]);
        }

        # start transcation
        DB::transaction(function () use ($request, $orderType) {
            $params = $request->validated();
            if ($request->exists('name')) {
                $orderType->search_name = UniqueCodeGenerator::generateSearchName($params['name']);
            }
            $orderType->fill($params);
            $orderType->updated_by = $request->user()->id;
            $orderType->save();
        });

        $response = new BaseJsonResponse($orderType);
        return $response->response();
    }
}
