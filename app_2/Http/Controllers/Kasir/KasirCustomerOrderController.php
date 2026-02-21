<?php

namespace App\Http\Controllers\Kasir;

use App\Helpers\Services\CustomerOrder\CustomerOrderCreator;
use App\Helpers\Services\CustomerOrder\CustomerOrderRequestTransformer;
use App\Helpers\Services\CustomerOrder\CustomerOrderRequestTransformerUpdater;
use App\Helpers\Services\SaleTransaction\SaleTransactionCreator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\CalculatePromoKasirCustomerOrderRequest;
use App\Http\Requests\Kasir\IndexKasirCustomerOrderRequest;
use App\Http\Requests\Kasir\PayKasirCustomerOrderRequest;
use App\Http\Requests\Kasir\ShowKasirCustomerOrderRequest;
use App\Http\Requests\Kasir\StoreKasirCustomerOrderRequest;
use App\Http\Requests\Kasir\UpdateKasirCustomerOrderRequest;
use App\Http\Requests\Kasir\WantToPayWithCashKasirCustomerOrderRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\CustomerOrder;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class KasirCustomerOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexKasirCustomerOrderRequest $request)
    {
        //
        $params = $request->validated();
        // $deviceId = $request->device->id;

        $datas = CustomerOrder::with([
                'location:id,name',
                'customer',
                'customerOrderPromos',
                'customerOrderDetails.product.productSellPrices',
                'customerOrderDetails.product.productLocationStocks',
                'customerOrderDetails.productCategory',
                'customerOrderDetails.productUnit',
                'customerOrderDetails.promo',
            ])->where('entity_id', $request->entity->id)
            // ->where(function (Builder $builder) use($deviceId) {
            //     $builder->where('device_id', $deviceId)->orWhere('checkpoint_device_id', $deviceId);
            // })
            ->whereIn('location_id', $params['locs']);

        if (array_key_exists('order_types', $params)) {
            $datas->whereIn('order_type_id', $params['order_types']);
        }

        if (array_key_exists('statuses', $params)) {
            $datas->whereIn('status', $params['statuses']);
        }

        return $datas->cursorPaginate($request->limit)->appends($params);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKasirCustomerOrderRequest $request)
    {
        //
        $customerOrder = new CustomerOrder();
        
        # start transcation
        DB::beginTransaction();
        try {
            $transforming = new CustomerOrderRequestTransformer($request->entity, $request->device, $request);
            $creator = new CustomerOrderCreator($transforming->transform());
            $customerOrder = $creator->create();

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        $response = new BaseJsonResponse(["id" => $customerOrder->id, "code" => $customerOrder->code]);
        return $response->response();
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowKasirCustomerOrderRequest $request, int $id)
    {
        //
        # TODO validation
        $deviceId = $request->device->id;

        $customerOrder = CustomerOrder::where('entity_id', $request->entity->id)
            ->where('id', $id)
            ->where(function (Builder $builder) use($deviceId) {
                $builder->where('device_id', $deviceId)->orWhere('checkpoint_device_id', $deviceId);
            })
            ->first();

        if ($customerOrder == null) {
            $response = new BaseJsonResponse(null, __('customer_order.not_found'));
            return $response->response(404);
        }

        $response = new BaseJsonResponse($this->detailResponse($customerOrder));
        return $response->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKasirCustomerOrderRequest $request, int $id)
    {
        //
        # start transcation
        DB::beginTransaction();
        try {
            $deviceId = $request->device->id;
    
            $customerOrder = CustomerOrder::where('entity_id', $request->entity->id)
                ->where('id', $id)
                ->where(function (Builder $builder) use($deviceId) {
                    $builder->where('device_id', $deviceId)->orWhere('checkpoint_device_id', $deviceId);
                })
                ->first();
    
            if ($customerOrder == null) {
                $response = new BaseJsonResponse(null, __('customer_order.not_found'));
                return $response->response(404);
            }

            $transforming = new CustomerOrderRequestTransformerUpdater($request->entity, $request->device, $customerOrder, $request);
            $creator = new CustomerOrderCreator($transforming->transform());
            $customerOrder = $creator->create();

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        $response = new BaseJsonResponse($this->detailResponse($customerOrder));
        return $response->response();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerOrder $customerOrder)
    {
        //
    }

    public function wantToPayWithCash(WantToPayWithCashKasirCustomerOrderRequest $request, int $id)
    {
        DB::beginTransaction();
        try {
            $deviceId = $request->device->id;

            $customerOrder = CustomerOrder::where('entity_id', $request->entity->id)
                ->where('id', $id)
                ->where(function (Builder $builder) use($deviceId) {
                    $builder->where('device_id', $deviceId)->orWhere('checkpoint_device_id', $deviceId);
                })
                ->lockForUpdate()
                ->first();
    
            if ($customerOrder == null) {
                $response = new BaseJsonResponse(null, __('customer_order.not_found'));
                return $response->response(404);
            }

            # calculate payment method
            $params = $request->validated();
            

            $customerOrder->update(['status' => 'want_to_pay_cash']);



            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }
    }

    public function pay(PayKasirCustomerOrderRequest $request, int $id)
    {
        //
        # start transcation
        DB::beginTransaction();
        try {
            $deviceId = $request->device->id;
    
            $customerOrder = CustomerOrder::where('entity_id', $request->entity->id)
                ->where('id', $id)
                ->where(function (Builder $builder) use($deviceId) {
                    $builder->where('device_id', $deviceId)->orWhere('checkpoint_device_id', $deviceId);
                })
                ->first();
    
            if ($customerOrder == null) {
                $response = new BaseJsonResponse(null, __('customer_order.not_found'));
                return $response->response(404);
            }

            $creator = new SaleTransactionCreator(
                $customerOrder, $request->device, $request->employee, null, $request->validated(), $request->employee
            );
            $creator->create();

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        $response = new BaseJsonResponse($this->detailResponse($customerOrder));
        return $response->response();
    }

    public function calculatePromo(CalculatePromoKasirCustomerOrderRequest $request)
    {
        $transforming = new CustomerOrderRequestTransformer($request->entity, $request->device, $request);
        $transformed = $transforming->transform();

        $response = new BaseJsonResponse(array_merge($transformed->toArray(), ["customerOrderId" => $request->customer_order_id ?? $request->customerOrderId]));
        return $response->response();
    }

    private function detailResponse(CustomerOrder $customerOrder)
    {
        return $customerOrder->load([
            'location:id,name',
            'orderType:id,name',
            'customerOrderDetails.product:id,name,sku',
            'customerOrderDetails.productUnit:id,name',
        ]);
    }
}
