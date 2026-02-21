<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnum;
use App\Helpers\Constants\PageConstants;
use App\Http\Requests\IndexSaleTransactionRequest;
use App\Http\Requests\ShowSaleTransactionRequest;
use App\Http\Requests\VoidSaleTransactionRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\ProductStockMovement;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionDetail;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexSaleTransactionRequest $request)
    {
        //
        $params = $request->validated();

        $datas = SaleTransaction::with([
                'customer:id,first_name,last_name',
            ])
            ->where('entity_id', $request->entity->id)
            ->where('sales_at', '>=', $params['start_at'])
            ->where('sales_at', '<=', $params['end_at'])
            ->orderByRaw('case status when "ok" then 0 else 1 end')
            ->orderByDesc('local_sales_at');

        if ($params['select_all_location'] == 'true' && array_key_exists('exclude_locs', $params)) {
            $datas->whereNotIn('location_id', $params['exclude_locs']);
        } else if ($params['select_all_location'] == 'false' && array_key_exists('locs', $params)) {
            $datas->whereIn('location_id', $params['locs']);
        }

        if (array_key_exists('order_types', $params)) {
            $datas->whereIn('order_type_id', $params['order_types']);
        }

        return $datas->paginate($request->limit ?? PageConstants::DefaultLimit)->appends($params);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowSaleTransactionRequest $request, int $id)
    {
        //
        $saleTransaction = SaleTransaction::where('entity_id', $request->entity->id)
            ->where('id', $id)
            ->first();

        if ($saleTransaction == null) {
            $response = new BaseJsonResponse(null, __('sale_transaction.error.not_found'));
            return $response->response(404);
        }

        $response = new BaseJsonResponse($this->detailResponse($saleTransaction));
        return $response->response();
    }

    public function showPdf(int $id)
    {
        $saleTransaction = SaleTransaction::where('id', $id)->first();
        if ($saleTransaction == null) {
            $response = new BaseJsonResponse(null, __('sale_transaction.error.not_found'));
            return $response->response(404);
        }

        $result = $this->detailResponse($saleTransaction);
 
        return view('pdf.sale_transaction', $result->toArray());
    }

    public function void(VoidSaleTransactionRequest $request, int $id)
    {
        //
        $saleTransaction = SaleTransaction::where('entity_id', $request->entity->id)
                            ->where('id', $id)
                            ->first();

        if ($saleTransaction == null) {
            $response = new BaseJsonResponse(null, __('sale_transaction.error.not_found'));
            return $response->response(404);
        }

        DB::transaction(function () use ($saleTransaction, $request) {
            $params = $request->validated();

            $timezone = new DateTimeZone($saleTransaction->location_timezone);
            $timeNow = new DateTime();
            $localTimeNow = (new DateTime())->setTimezone($timezone);

            $saleTransaction->update([
                'void_by' => $request->employee->id,
                'void_at' => $timeNow,
                'local_void_at' => $localTimeNow,
                'void_reason' => $params['reason'],
                'void_notes' => $params['notes'],
                'status' => StatusEnum::Void->value,
            ]);

            foreach (SaleTransactionDetail::with(['product'])->where('sale_transaction_id', $saleTransaction->id)->get() as $saleDetailTransaction)
            {
                $saleDetailTransaction->status = 'void';
                $saleDetailTransaction->save();

                $product = $saleDetailTransaction->product()->first();

                # create movement
                $data = new ProductStockMovement();

                $data->product_id = $saleDetailTransaction->product_id;
                $data->location_id = $saleTransaction->location_id;
                $data->product_unit_id = $saleDetailTransaction->product_unit_id;

                $data->original_product_unit_id = $saleDetailTransaction->product_unit_id;

                $data->resource_id = $saleDetailTransaction->id;
                $data->resource_type = $saleDetailTransaction::class;

                $data->original_stock_out = 0;
                $data->original_stock_in = $saleDetailTransaction->quantity;
                $data->original_buying_price = $product->cost_of_goods_sold;
                $data->conversion_stock = 1; # should find conversion, not for now

                $data->stock_in = $data->original_stock_in * $data->conversion_stock;
                $data->stock_out = $data->original_stock_out * $data->conversion_stock;
                $data->buying_price = $data->original_buying_price * $data->conversion_stock;

                $data->save();
            }
        });

        $response = new BaseJsonResponse(["id" => $saleTransaction->id]);
        return $response->response();
    }

    private function detailResponse(SaleTransaction $saleTransaction)
    {
        return $saleTransaction->load([
            'entity:id,name,image_url',
            'location',
            'customer:id,first_name,last_name',
            'employeeSales:id,first_name,last_name',
            'cashier:id,first_name,last_name',
            'orderType:id,name',
            'voidBy:id,first_name,last_name',
            'paidBy:id,first_name,last_name',
            'saleTransactionDetailsTotalItem',
            'saleTransactionDetails',
            'saleTransactionPayments',
        ]);
    }
}
