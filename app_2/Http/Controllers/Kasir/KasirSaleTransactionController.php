<?php

namespace App\Http\Controllers\Kasir;

use App\Helpers\Services\CustomerOrder\CustomerOrderCreator;
use App\Helpers\Services\CustomerOrder\CustomerOrderRequestTransformer;
use App\Helpers\Services\SaleTransaction\SaleTransactionCreator;
use App\Helpers\Services\SaleTransaction\SaleTransactionRefund;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\IndexKasirSaleTransactionRequest;
use App\Http\Requests\Kasir\RefundKasirSaleTransactionRequest;
use App\Http\Requests\Kasir\ShowKasirSaleTransactionRequest;
use App\Http\Requests\Kasir\StoreKasirSaleTransactionRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\CustomerOrder;
use App\Models\SaleTransaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class KasirSaleTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexKasirSaleTransactionRequest $request)
    {
        //
        $params = $request->validated();
        $deviceId = $request->device->id;

        $datas = SaleTransaction::with([
            'customer',
            'cashier:id,code,first_name,last_name',
            'employeeSales:id,code,first_name,last_name',
        ])
            ->where('entity_id', $request->entity->id)
            // ->where('taking_id', null)
            // ->where(function (Builder $builder) use($deviceId) {
            //     $builder->where('device_id', $deviceId)->orWhere('checkpoint_device_id', $deviceId);
            // })
            ->where('void_at', null)
            ->where(function (Builder $builder) {
                $builder
                    ->where(function (Builder $builder) {
                        $builder->whereNull('customer_id')
                            ->where('local_sales_at', '>=', Carbon::now()->subDays(15)->startOfDay()); # TODO: change to 1
                    })
                    ->orWhere(function (Builder $builder) {
                        $builder->whereNotNull('customer_id')
                            ->where('local_sales_at', '>=', Carbon::now()->subDays(15)->startOfDay()); # TODO: change to 2
                    });
            })
            ->whereIn('location_id', $params['locs']);

        if (array_key_exists('order_types', $params)) {
            $datas = $datas->whereIn('order_type_id', $params['order_types']);
        }

        if (array_key_exists('refund_amount', $params)) {
            $datas = $datas->where('net_sales_after_tax', '>=', $params['refund_amount']);
        }

        if (array_key_exists('exclude_ids', $params)) {
            $datas = $datas->whereNotIn('id', $params['exclude_ids']);
        }

        if (array_key_exists('keyword', $params)) {
            $keyword =  $params['keyword'];
            $keywordLike =  "%" . $keyword . "%";

            $datas->where(function (Builder $builder) use($keywordLike, $keyword) {
                $builder->where('sales_no', 'like', $keywordLike);
            });
        }

        if (array_key_exists('cashier_ids', $params)) {
            $datas->whereIn('cashier_id', $params['cashier_ids']);
        }

        if (array_key_exists('only_logged_cashier', $params)) {
            $logged = $params['only_logged_cashier'] == 'true';

            if ($logged) {
                $datas->where('cashier_id', $request->employee->id);
                $datas->where('local_sales_at', '>=', Carbon::now()->subDays(1)->startOfDay());
            }
            else {
                $datas->where('local_sales_at', '>=', Carbon::now()->subDays(2)->startOfDay());
            }
        }
        
        return $datas->orderBy('id', 'asc')->cursorPaginate($request->limit)->appends($params);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowKasirSaleTransactionRequest $request, int $id)
    {
        //
        $deviceId = $request->device->id;

        $saleTransaction = SaleTransaction::where('entity_id', $request->entity->id)
            ->where('id', $id)
            // ->where(function (Builder $builder) use($deviceId) {
            //     $builder->where('device_id', $deviceId)->orWhere('checkpoint_device_id', $deviceId);
            // })
            ->first();

        if ($saleTransaction == null) {
            $response = new BaseJsonResponse(null, __('sale_transaction.error.not_found'));
            return $response->response(404);
        }

        $response = new BaseJsonResponse($this->detailResponse($saleTransaction));
        return $response->response();
    }

    public function showPdf(ShowKasirSaleTransactionRequest $request, int $id)
    {
        $deviceId = $request->device->id;

        $saleTransaction = SaleTransaction::where('entity_id', $request->entity->id)
            ->where('id', $id)
            // ->where(function (Builder $builder) use($deviceId) {
            //     $builder->where('device_id', $deviceId)->orWhere('checkpoint_device_id', $deviceId);
            // })
            ->first();

        if ($saleTransaction == null) {
            $response = new BaseJsonResponse(null, __('sale_transaction.error.not_found'));
            return $response->response(404);
        }

        $result = $this->detailResponse($saleTransaction);
        $timeNow = strtotime("now");

        $pdf = Pdf::loadView('pdf.sale_transaction');
 
        return $pdf->stream();

        // return pdf()
        //     ->view('pdf.sale_transaction', compact('result'))
        //     ->name("invoice-$id-$timeNow.pdf");
    }

    public function store(StoreKasirSaleTransactionRequest $request)
    {
        //
        $saleTransaction = new SaleTransaction();

        # start transcation
        DB::beginTransaction();
        try {
            $transformer = new CustomerOrderRequestTransformer($request->entity, $request->device, $request);
            $customerOrder = CustomerOrder::where('entity_id', $request->entity->id)
                ->where('id', $request->customer_order_id ?? $request->customerOrderId)
                ->first();

            if ($customerOrder == null) {
                $customerOrderCreator = new CustomerOrderCreator($transformer->transform());
                $customerOrder = $customerOrderCreator->create();
            }
    
            if ($customerOrder == null) {
                $response = new BaseJsonResponse(null, __('customer_order.not_found'));
                return $response->response(404);
            }

            $creator = new SaleTransactionCreator(
                $customerOrder, $request->device, $request->employee, null, $request->validated(), $request->employee
            );
            $saleTransaction = $creator->create();

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        $response = new BaseJsonResponse(["id" => $saleTransaction->id]);
        return $response->response();
    }

    public function refund(RefundKasirSaleTransactionRequest $request, int $id)
    {
        //
        // $deviceId = $request->device->id;

        $saleTransaction = SaleTransaction::where('entity_id', $request->entity->id)
            ->where('id', $id)
            // ->where(function (Builder $builder) use($deviceId) {
            //     $builder->where('device_id', $deviceId)->orWhere('checkpoint_device_id', $deviceId);
            // })
            ->first();

        if ($saleTransaction == null) {
            $response = new BaseJsonResponse(null, __('sale_transaction.error.not_found'));
            return $response->response(404);
        }

        # start transcation
        DB::beginTransaction();
        try {
            $creator = new SaleTransactionRefund(
                $saleTransaction, $request->device, $request->employee, $request->validated()
            );
            $creator->refund();

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        $response = new BaseJsonResponse($this->detailResponse($saleTransaction));
        return $response->response();
    }

    private function detailResponse(SaleTransaction $saleTransaction)
    {
        return $saleTransaction->load([
            'location:id,name',
            'orderType:id,name',
            'customer',
            'cashier:id,code,first_name,last_name',
            'employeeSales:id,code,first_name,last_name',
            'saleTransactionDetails',
            'saleTransactionPayments',
            'saleRefunds',
            'saleRefunds.saleRefundDetails',
        ]);
    }
}
