<?php

namespace App\Http\Controllers;

use App\Helpers\Services\ProductAdjustmentStock\ProductAdjustmentStockApprovalService;
use App\Helpers\Services\ProductAdjustmentStock\ProductAdjustmentStockDestroyService;
use App\Helpers\UniqueCodeGenerator;
use App\Http\Requests\ApproveProductAdjustmentStockRequest;
use App\Http\Requests\DestroyProductAdjustmentStockRequest;
use App\Http\Requests\IndexProductAdjustmentStockRequest;
use App\Http\Requests\ShowProductAdjustmentStockRequest;
use App\Http\Requests\StoreProductAdjustmentStockRequest;
use App\Http\Requests\UpdateProductAdjustmentStockRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductAdjustmentStock;
use App\Models\ProductAdjustmentStockDetail;
use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Validation\ValidationException;

class ProductAdjustmentStockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexProductAdjustmentStockRequest $request)
    {
        //
        $params = $request->validated();

        $startDate = Carbon::parse($params['start_date'])->startOfDay();
        $endDate = Carbon::parse($params['end_date'])->endOfDay();

        $datas = ProductAdjustmentStock::where('entity_id', $request->entity->id)
            ->where(function (Builder $query) use($startDate) {
                $query->orWhere('local_requested_at', '>=', $startDate)
                    ->orWhere('local_approved_at', '>=', $startDate)
                    ->orWhere('local_rejected_at', '>=', $startDate);
            })
            ->where(function (Builder $query) use($endDate) {
                $query->orWhere('local_requested_at', '<=', $endDate)
                    ->orWhere('local_approved_at', '<=', $endDate)
                    ->orWhere('local_rejected_at', '<=', $endDate);
            });

        if ($request->exists('statuses')) {
            $datas->whereIn('status', $request->statuses);
        }

        if ($request->exists('locs')) {
            $datas->whereIn('location_id', $request->locs);
        }

        if ($request->exists('exclude_locs')) {
            $datas->whereNotIn('location_id', $request->exclude_locs);
        }

        return $datas
            ->with([
                'location:id,name',
                'employeeRequestedBy:id,first_name,last_name',
                'employeeApprovedBy:id,first_name,last_name',
                'employeeRejectedBy:id,first_name,last_name'
            ])
            ->paginate($request->limit)
            ->appends($params);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductAdjustmentStockRequest $request)
    {
        //
        $params = $request->validated();
        $data = new ProductAdjustmentStock();

        # start transcation
        DB::beginTransaction();
        try {
            $timeNow = new DateTime();
            $timezone = new DateTimeZone(Location::find($params['location_id'])->timezone ?? 'UTC');
            $localTimeNow = (new DateTime())->setTimezone($timezone);
            
            $data->code = UniqueCodeGenerator::generateCode();
            $data->entity_id = $request->entity->id;
            $data->employee_requested_by = $request->employee->id;
            $data->requested_at = $timeNow;
            $data->local_requested_at = $localTimeNow;
            $data->created_by = $request->user()->id;
            $data->updated_by = $request->user()->id;
            $data->fill($params);

            if  ($params['auto_approve'])
            {
                $data->employee_approved_by = $request->employee->id;
                $data->approved_at = $timeNow;
                $data->local_approved_at = $localTimeNow;
                $data->status = 'approved';
            }

            $data->save();

            foreach ($params['products'] as $product)
            {
                $line = new ProductAdjustmentStockDetail();
                $line->product_adjustment_stock_id = $data->id;
                $line->employee_id = $data->employee_requested_by;
                $line->location_id = $data->location_id;

                $foundProduct = Product::find($product['product_id']);
                $line->product_name = $foundProduct->name;
                $line->product_sku = $foundProduct->sku;
                $line->product_code = $foundProduct->code;
                $line->product_description = $foundProduct->description ?? '';

                $line->product_category_name = $foundProduct->productCategory()?->name ?? "";

                $foundProductUnit = ProductUnit::find($product['product_unit_id']);
                $line->product_unit_name = $foundProductUnit->name;

                $line->fill($product);

                $line->save();
            }

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        if ($data->approved_at != null) {
            (new ProductAdjustmentStockApprovalService($data))->adjustStock();
        }

        return (new BaseJsonResponse(['id' => $data->id]))->response();
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowProductAdjustmentStockRequest $request, int $id)
    {
        //
        $data = ProductAdjustmentStock::where('entity_id', $request->entity->id)->where('id', $id)->first();
        $this->validate($data);

        return $this->baseDetailResponse($data)->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductAdjustmentStockRequest $request, int $id)
    {
        //
        $params = $request->validated();
        $data = ProductAdjustmentStock::where('entity_id', $request->entity->id)->where('id', $id)->first();
        $this->validate($data);

        # start transcation
        DB::beginTransaction();
        try {
            $data->updated_by = $request->user()->id;
            $data->fill($params);
            $data->save();

            foreach ($params['products'] as $product)
            {
                $lineId = 0;
                if (array_key_exists('id', $product)) {
                    $lineId = $product['id'];
                }

                $line = ProductAdjustmentStockDetail::find($lineId);
                if ($line == null) {
                    $line = new ProductAdjustmentStockDetail();
                }

                if ($lineId != 0 && $product['_deleted']) {
                    $line->delete();
                    continue;
                }

                $line->product_opname_service_id = $data->id;
                $line->employee_id = $data->employee_requested_by;
                $line->location_id = $data->location_id;

                $foundProduct = Product::find($product['product_id']);
                $line->product_name = $foundProduct->name;
                $line->product_sku = $foundProduct->sku;
                $line->product_code = $foundProduct->code;
                $line->product_description = $foundProduct->description;

                $line->product_category_name = $foundProduct->productCategory()?->name ?? "";

                $foundProductUnit = ProductUnit::find($product['product_unit_id']);
                $line->product_unit_name = $foundProductUnit->name;
                $line->updated_by = $request->user()->id;

                $line->fill($product);

                $line->save();
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
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyProductAdjustmentStockRequest $request, int $id)
    {
        //
        $data = ProductAdjustmentStock::where('entity_id', $request->entity->id)->where('id', $id)->first();
        $this->validate($data);

        (new ProductAdjustmentStockDestroyService($data))->revertStock();

        return (new BaseJsonResponse(null))->response();
    }

    public function approve(ApproveProductAdjustmentStockRequest $request, int $id)
    {
        //
        # TODO: validate ProductTransferService
        $params = $request->validated();

        $productOpnameService = ProductAdjustmentStock::where('entity_id', $request->entity->id)->where('id', $id)->first();
        $this->validate($productOpnameService);
        $this->validateStatus($productOpnameService, 'requested');
        
        # start transcation
        DB::beginTransaction();
        try {
            $timeNow = new DateTime();
            $timezone = new DateTimeZone($productOpnameService->location()->timezone ?? 'UTC');
            $localTimeNow = (new DateTime())->setTimezone($timezone);
            
            $productOpnameService->employee_approved_by = $request->employee->id;
            $productOpnameService->approved_at = $timeNow;
            $productOpnameService->local_approved_at = $localTimeNow;
            $productOpnameService->approval_note = $params['note'];
            $productOpnameService->status = 'approved';
            $productOpnameService->updated_by = $request->user()->id;
            $productOpnameService->save();

            (new ProductAdjustmentStockApprovalService($productOpnameService))->adjustStock();

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        return (new BaseJsonResponse(['id' => $productOpnameService->id]))->response();
    }

    private function baseDetailResponse(ProductAdjustmentStock $productAdjustmentStock): BaseJsonResponse
    {
        return new BaseJsonResponse($productAdjustmentStock->load([
            'location:id,name',
            'employeeRequestedBy:id,first_name,last_name',
            'employeeApprovedBy:id,first_name,last_name',
            'employeeRejectedBy:id,first_name,last_name',
            'productAdjustmentStockDetails.product',
            'productAdjustmentStockDetails.product.productCategory:id,name',
            'productAdjustmentStockDetails.productUnit:id,name',
            'productAdjustmentStockDetails.productCategory:id,name',
        ]));
    }

    private function validate(?ProductAdjustmentStock $productAdjustmentStock) {
        if ($productAdjustmentStock != null) {
            return;
        }

        throw ValidationException::withMessages([
            'product_opname_service' => __('general.not_found'),
        ]);
    }

    private function validateStatus(?ProductAdjustmentStock $productOpnameService, string $requiredStatus) {
        if ($productOpnameService->status == $requiredStatus) {
            return;
        }

        throw ValidationException::withMessages([
            'product_opname_service' => __('general.status_not_valid'),
        ]);
    }
}
