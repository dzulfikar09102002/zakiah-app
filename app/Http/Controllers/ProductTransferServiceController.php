<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnum;
use App\Helpers\Services\ProductTransfer\ProductTransferApprove;
use App\Helpers\Services\ProductTransfer\ProductTransferStockReserved;
use App\Helpers\Services\ProductTransfer\ProductTransferStockUnreserved;
use App\Helpers\UniqueCodeGenerator;
use App\Http\Requests\ApproveProductTransferServiceRequest;
use App\Http\Requests\CancelProductTransferServiceRequest;
use App\Http\Requests\IndexProductTransferServiceRequest;
use App\Http\Requests\RejectProductTransferServiceRequest;
use App\Http\Requests\StoreProductTransferServiceRequest;
use App\Http\Requests\UpdateProductTransferServiceRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductTransferService;
use App\Models\ProductTransferServiceDetail;
use App\Models\ProductUnit;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductTransferServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexProductTransferServiceRequest $request)
    {
        //
        $params = $request->validated();
        $startDate = $params['start_date'];
        $endDate = (new DateTime($params['end_date']))->modify('+1 day -1 microsecond');

        $datas = ProductTransferService::where('entity_id', $request->entity->id)
            ->where(function (Builder $query) use($startDate) {
                $query->orWhere('local_requested_at', '>=', $startDate)
                    ->orWhere('local_approved_at', '>=', $startDate)
                    ->orWhere('local_cancelled_at', '>=', $startDate)
                    ->orWhere('local_rejected_at', '>=', $startDate);
            })
            ->where(function (Builder $query) use($endDate) {
                $query->orWhere('local_requested_at', '<=', $endDate)
                    ->orWhere('local_approved_at', '<=', $endDate)
                    ->orWhere('local_cancelled_at', '<=', $endDate)
                    ->orWhere('local_rejected_at', '<=', $endDate);
            });

        if ($request->exists('statuses')) {
            $datas->whereIn('status', $request->statuses);
        }

        $datas->where(function (Builder $query) use($request) {
            $query->where(function (Builder $query) use($request) {
                if ($request->exists('from_locs')) {
                    $query->whereIn('from_location_id', $request->from_locs);
                }

                if ($request->exists('to_locs')) {
                    $query->orWhereIn('to_location_id', $request->to_locs);
                }
            });
            $query->where(function (Builder $query) use($request) {
                if ($request->exists('from_exclude_locs')) {
                    $query->whereNotIn('from_location_id', $request->from_exclude_locs);
                }
        
                if ($request->exists('to_exclude_locs')) {
                    $query->orWhereNotIn('to_location_id', $request->to_exclude_locs);
                }
            });
        });

        return $datas->with(['fromLocation:id,name', 'toLocation:id,name'])->paginate($request->limit)->appends($params);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductTransferServiceRequest $request)
    {
        //
        $params = $request->validated();
        $data = new ProductTransferService();
        
        # start transcation
        DB::beginTransaction();
        try {
            $timeNow = new DateTime();
            $timezone = new DateTimeZone(Location::find($params['from_location_id'])->timezone ?? 'UTC');
            $localTimeNow = (new DateTime())->setTimezone($timezone);

            $data->code = UniqueCodeGenerator::generateCode();
            $data->entity_id = $request->entity->id;
            $data->employee_requested_by = $request->employee->id;
            $data->requested_at = $timeNow;
            $data->local_requested_at = $localTimeNow;
            $data->created_by = $request->user()->id;
            $data->updated_by = $request->user()->id;
            $data->fill($params);
            $data->save();
            
            foreach ($params['products'] as $productParams)
            {
                $product = Product::find($productParams['product_id']);
                $productUnit = ProductUnit::find($product->product_unit_id);

                $detail = new ProductTransferServiceDetail();
                $detail->product_transfer_service_id = $data->id;
                $detail->original_product_unit_id = $product->product_unit_id;
                $detail->smallest_product_unit_name = ProductUnit::find($product->product_unit_id)->name;
                $detail->conversion_quantity = 1;

                $detail->product_id = $product->id;
                $detail->product_name = $product->name;
                $detail->product_sku = $product->sku;
                $detail->product_code = $product->barcode;

                $detail->product_unit_id = $productUnit->id;
                $detail->product_unit_name = $productUnit->name;

                $detail->quantity = $productParams['quantity'];
                $detail->transfered_quantity = $detail->quantity * $detail->conversion_quantity;

                $detail->buying_price = $product->last_buying_price;
                $detail->transfered_buying_price = $detail->buying_price * $detail->conversion_quantity;

                $detail->save();
            }

            (new ProductTransferStockReserved($data->id, $request->employee->id))->reserved();
            if ($params['auto_approve'] == true) {
                (new ProductTransferApprove($data->id, $request->employee->id))->approve();
            }

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        return (new BaseJsonResponse(['id' => $data->id]))->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function approve(ApproveProductTransferServiceRequest $request, ProductTransferService $productTransferService)
    {
        //
        # TODO: validate ProductTransferService
        $params = $request->validated();
        
        # start transcation
        DB::beginTransaction();
        try {
            (new ProductTransferApprove($productTransferService->id, $request->employee->id))->approve($params['notes']);

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        return (new BaseJsonResponse(['id' => $productTransferService->id]))->response();
    }

    public function reject(RejectProductTransferServiceRequest $request, ProductTransferService $productTransferService)
    {
        //
        # TODO: validate ProductTransferService
        $params = $request->validated();
        
        # start transcation
        DB::beginTransaction();
        try {
            $timeNow = new DateTime();
            $timezone = new DateTimeZone(Location::find($productTransferService->to_location_id)->timezone ?? 'UTC');
            $localTimeNow = (new DateTime())->setTimezone($timezone);
            
            $productTransferService->employee_rejected_by = $request->employee->id;
            $productTransferService->rejected_at = $timeNow;
            $productTransferService->local_rejected_at = $localTimeNow;
            $productTransferService->approval_note = $params['notes'] ?? 'Auto Rejected by system';
            $productTransferService->status = StatusEnum::Rejected;
            $productTransferService->save();

            (new ProductTransferStockUnreserved($productTransferService->id, $request->employee->id))->unreserved();

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        return (new BaseJsonResponse([
            'id' => $productTransferService->id,
            'status' => $productTransferService->status,
        ]))->response();
    }

    public function cancel(CancelProductTransferServiceRequest $request, ProductTransferService $productTransferService)
    {
        //
        # TODO: validate ProductTransferService
        $params = $request->validated();
        
        # start transcation
        DB::beginTransaction();
        try {
            $timeNow = new DateTime();
            $timezone = new DateTimeZone(Location::find($productTransferService->to_location_id)->timezone ?? 'UTC');
            $localTimeNow = (new DateTime())->setTimezone($timezone);
            
            $productTransferService->employee_cancelled_by = $request->employee->id;
            $productTransferService->cancelled_at = $timeNow;
            $productTransferService->local_cancelled_at = $localTimeNow;
            $productTransferService->cancelled_note = $params['notes'] ?? 'Auto Cancelled by system';
            $productTransferService->status = StatusEnum::Cancelled;
            $productTransferService->save();

            (new ProductTransferStockUnreserved($productTransferService->id, $request->employee->id))->unreserved();

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        return (new BaseJsonResponse([
            'id' => $productTransferService->id,
            'status' => $productTransferService->status,
        ]))->response();
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductTransferService $productTransferService)
    {
        //
        # TODO: validate

        return $this->baseDetailResponse($productTransferService)->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductTransferServiceRequest $request, ProductTransferService $productTransferService)
    {
        //
        $params = $request->validated();
        
        # start transcation
        DB::beginTransaction();
        try {
            $timeNow = new DateTime();
            $timezone = new DateTimeZone(Location::find($params['from_location_id'])->timezone ?? 'UTC');
            $localTimeNow = (new DateTime())->setTimezone($timezone);

            (new ProductTransferStockUnreserved($productTransferService->id, $request->employee->id))->unreserved();

            $productTransferService->code = UniqueCodeGenerator::generateCode();
            $productTransferService->entity_id = $request->entity->id;
            $productTransferService->employee_requested_by = $request->employee->id;
            $productTransferService->requested_at = $timeNow;
            $productTransferService->local_requested_at = $localTimeNow;
            $productTransferService->fill($params);
            $productTransferService->save();
            
            $notDeletedIds = [];
            foreach ($params['products'] as $productParams)
            {
                $id = 0;
                if (array_key_exists('id', $productParams)) {
                    $id = $productParams['id'];
                    array_push($notDeletedIds, $id);
                }

                $destroy = false;
                if (array_key_exists('_destroy', $productParams)) {
                    $destroy = $productParams['_destroy'];
                }

                $detail = ProductTransferServiceDetail::firstOrNew(['id' => $id]);
                if ($detail != null && $destroy) {
                    $detail->delete();
                    continue;
                }

                $product = Product::find($productParams['product_id']);
                $productUnit = ProductUnit::find($product->product_unit_id);

                $detail->product_transfer_service_id = $productTransferService->id;
                $detail->original_product_unit_id = $product->product_unit_id;
                $detail->smallest_product_unit_name = ProductUnit::find($product->product_unit_id)->name;
                $detail->conversion_quantity = 1;

                $detail->product_id = $product->id;
                $detail->product_name = $product->name;
                $detail->product_sku = $product->sku;
                $detail->product_code = $product->code;

                $detail->product_unit_id = $productUnit->id;
                $detail->product_unit_name = $productUnit->name;

                $detail->quantity = $productParams['quantity'];
                $detail->transfered_quantity = $detail->quantity * $detail->conversion_quantity;

                $detail->buying_price = 0;
                $detail->transfered_buying_price = $detail->buying_price * $detail->conversion_quantity;

                $detail->save();
            }

            # clean
            if (count($notDeletedIds) > 0) {
                $productTransferService->productTransferServiceDetails()->whereNotIn('id', $notDeletedIds)->delete();
            }

            (new ProductTransferStockReserved($productTransferService->id, $request->employee->id))->reserved();
            if ($params['auto_approve'] == true) {
                (new ProductTransferApprove($productTransferService->id, $request->employee->id))->approve();
            }

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        return (new BaseJsonResponse([
            'id' => $productTransferService->id,
        ]))->response();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductTransferService $productTransferService)
    {
        //
    }

    public function showPdf(int $id)
    {
        $saleTransaction = ProductTransferService::where('id', $id)->first();
        if ($saleTransaction == null) {
            $response = new BaseJsonResponse(null, __('sale_transaction.error.not_found'));
            return $response->response(404);
        }

        $result = $this->detailResponse($saleTransaction);
 
        return view('pdf.product_transfer_service', $result->toArray());
    }

    private function baseDetailResponse(ProductTransferService $productTransferService): BaseJsonResponse
    {
        return new BaseJsonResponse($this->detailResponse($productTransferService));
    }

    private function detailResponse(ProductTransferService $productTransferService)
    {
        return $productTransferService->load([
            'entity:id,name,image_url',
            'fromLocation:id,name',
            'toLocation:id,name',
            'employeeRequestedBy:id,first_name,last_name',
            'employeeApprovedBy:id,first_name,last_name',
            'employeeRejectedBy:id,first_name,last_name',
            'productTransferServiceDetails.product',
        ]);
    }
}
