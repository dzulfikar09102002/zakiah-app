<?php

namespace App\Http\Controllers;

use App\Helpers\Services\ProductOpname\ProductOpnameApprovalService;
use App\Helpers\UniqueCodeGenerator;
use App\Http\Requests\ApproveProductOpnameRequest;
use App\Http\Requests\DestroyProductOpnameServiceRequest;
use App\Http\Requests\IndexProductOpnameServiceRequest;
use App\Http\Requests\PreviewProductOpnameServiceRequest;
use App\Http\Requests\RejectProductOpnameRequest;
use App\Http\Requests\ShowProductOpnameServiceRequest;
use App\Http\Requests\StoreProductOpnameServiceRequest;
use App\Http\Requests\UpdateProductOpnameServiceRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Http\Responses\BaseJsonWithPagingResponse;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductOpnameService;
use App\Models\ProductOpnameServiceDetail;
use App\Models\ProductUnit;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductOpnameServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexProductOpnameServiceRequest $request)
    {
        //
        $params = $request->validated();

        $startDate = Carbon::parse($params['start_date'])->startOfDay();
        $endDate = Carbon::parse($params['end_date'])->endOfDay();

        $datas = ProductOpnameService::where('entity_id', $request->entity->id)
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
    public function store(StoreProductOpnameServiceRequest $request)
    {
        //
        $params = $request->validated();
        $data = new ProductOpnameService();

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
            }

            $data->save();

            foreach ($params['products'] as $product)
            {
                $line = new ProductOpnameServiceDetail();
                $line->product_opname_service_id = $data->id;
                $line->employee_id = $data->employee_requested_by;
                $line->location_id = $data->location_id;

                $foundProduct = Product::find($product['product_id']);
                $line->product_name = $foundProduct->name;
                $line->product_sku = $foundProduct->sku;
                $line->product_code = $foundProduct->barcode;
                $line->product_description = $foundProduct->description ?? '';

                $line->product_category_name = $foundProduct->productCategory()?->name ?? "";

                $foundProductUnit = ProductUnit::find($foundProduct->product_unit_id);
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

        return (new BaseJsonResponse(['id' => $data->id]))->response();
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowProductOpnameServiceRequest $request, int $id)
    {
        //
        $data = ProductOpnameService::where('entity_id', $request->entity->id)->where('id', $id)->first();
        $this->validate($data);

        return $this->baseDetailResponse($data)->response();
    }

    public function preview(PreviewProductOpnameServiceRequest $request, int $id)
    {
        //
        $data = ProductOpnameService::where('entity_id', $request->entity->id)->where('id', $id)->first();
        $this->validate($data);

        $location_id = $data->location_id;

        $result = $data->load([
            'location:id,name',
            'employeeRequestedBy:id,first_name,last_name',
            'employeeApprovedBy:id,first_name,last_name',
            'employeeRejectedBy:id,first_name,last_name',
        ]);

        $details = Product::leftJoin('product_opname_service_details', function (JoinClause $join) use($id) {
                $join->on('products.id', '=', 'product_opname_service_details.product_id')
                    ->where('product_opname_service_details.product_opname_service_id', $id);
            })
            ->leftJoin('product_units', 'product_units.id', '=', 'products.product_unit_id')
            ->leftJoin('product_categories', 'product_categories.id', '=', 'products.product_category_id')
            ->leftJoin('product_location_stocks', function (JoinClause $join) use($location_id) {
                $join->on('products.id', '=', 'product_location_stocks.product_id')
                    ->where('product_location_stocks.location_id', $location_id);
            })
            ->where('products.entity_id', $request->entity->id)
            ->select(
                'products.*',
                'product_units.id as product_unit_id', 'product_units.name as product_unit_name',
                'product_categories.id as product_category_id', 'product_categories.name as product_category_name',
                'product_opname_service_details.id as product_opname_service_detail_id',
                DB::raw('IFNULL(product_opname_service_details.recorded_stock, IFNULL(product_location_stocks.stock, 0)) as recorded_stock'),
                DB::raw('IFNULL(product_opname_service_details.counted_stock, 0) as counted_stock'),
                DB::raw('IFNULL(product_opname_service_details.difference_stock, 0 - IFNULL(product_location_stocks.stock, 0)) as difference_stock'),
                DB::raw('IFNULL(product_opname_service_details.id, products.id) as sorted_id'),
                'product_opname_service_details.note as note',
            )
            ->orderByRaw('case when product_opname_service_details.difference_stock is not null then 0 else 1 end')
            ->orderBy('sorted_id');

        # checking
        if (($request->show_all ?? 'false') == 'false') {
            $details->whereNotNull('product_opname_service_details.difference_stock');
        }

        if (($request->show_difference ?? 'true') == 'true') {
            $details->where(function (Builder $query) {
                $query->orWhereNull('difference_stock')->orWhere('difference_stock', '!=', 0);
            });
        }
        
        $dataCount = $details->count();
        $limit = $request->limit ?? 10;
        $page = $request->page ?? 1;
        $offset = ($page - 1) * $limit;
        $last_page = ceil($dataCount / $limit);

        $productOpnameServiceDetails = [];
        foreach ($details->offset($offset)->limit($limit)->get() as $detail) {
            array_push($productOpnameServiceDetails, [
                'id' => $detail['product_opname_service_detail_id'],
                'product_id' => $detail['id'],
                'product_category_id' => $detail['product_category_id'],
                'product_unit_id' => $detail['product_unit_id'],
                'product_name' => $detail['name'],
                'product_sku' => $detail['sku'],
                'product_code' => $detail['barcode'],
                'recorded_stock' => $detail['recorded_stock'],
                'counted_stock' => $detail['counted_stock'],
                'difference_stock' => $detail['difference_stock'],
                'note' => $detail['note'],
                'product' =>[
                    'id' => $detail['id'],
                    'sku' => $detail['sku'],
                    'barcode' => $detail['barcode'],
                    'code' => $detail['barcode'],
                    'name' => $detail['name'],
                    'sell_price' => $detail['sell_price'],
                    'product_category' => [
                        'id' => $detail['product_category_id'],
                        'name' => $detail['product_category_name'],
                    ],
                    'product_unit' => [
                        'id' => $detail['product_unit_id'],
                        'name' => $detail['product_unit_name'],
                    ],
                ],
            ]);
        }

        $result = array_merge($result->toArray(), ['product_opname_service_details' => $productOpnameServiceDetails]);

        $response = new BaseJsonWithPagingResponse($result);
        return $response->setPaging([
            "current_page" => $page,
            "limit" => $limit,
            "last_page" => $last_page,
            "total" => $dataCount,
            "prev_page_url" => $page > 1 ? 'prev' : null,
            "next_page_url" => $last_page > $page ? 'next': null,
        ])->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductOpnameServiceRequest $request, int $id)
    {
        //
        $params = $request->validated();
        $data = ProductOpnameService::where('entity_id', $request->entity->id)->where('id', $id)->first();
        $this->validate($data);

        # start transcation
        DB::beginTransaction();
        try {
            $data->updated_by = $request->user()->id;
            $data->fill($params);
            $data->save();

            $notDeletedIds = [];
            foreach ($params['products'] as $product)
            {
                $lineId = 0;
                if (array_key_exists('id', $product)) {
                    $lineId = $product['id'];
                    array_push($notDeletedIds, $lineId);
                }

                $line = ProductOpnameServiceDetail::find($lineId);
                if ($line == null) {
                    $line = new ProductOpnameServiceDetail();
                }

                $destroy = false;
                if (array_key_exists('_destroy', $product)) {
                    $destroy = $product['_destroy'];
                }

                if ($lineId != 0 && $destroy) {
                    $line->delete();
                    continue;
                }

                $line->product_opname_service_id = $data->id;
                $line->employee_id = $data->employee_requested_by;
                $line->location_id = $data->location_id;

                $foundProduct = Product::find($product['product_id']);
                $line->product_name = $foundProduct->name;
                $line->product_sku = $foundProduct->sku;
                $line->product_code = $foundProduct->barcode;
                $line->product_description = $foundProduct->description ?? '';

                $line->product_category_name = $foundProduct->productCategory()?->name ?? "";

                $foundProductUnit = ProductUnit::find($foundProduct->product_unit_id);
                $line->product_unit_name = $foundProductUnit->name;
                $line->updated_by = $request->user()->id;

                $line->fill($product);

                $line->save();
            }

            # clean
            if (count($notDeletedIds) > 0) {
                $data->productOpnameServiceDetails()->whereNotIn('id', $notDeletedIds)->delete();
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
    public function destroy(DestroyProductOpnameServiceRequest $request, int $id)
    {
        //
        $data = ProductOpnameService::where('entity_id', $request->entity->id)->where('id', $id)->first();
        $this->validate($data);

        # start transcation
        DB::beginTransaction();
        try {
            ProductOpnameServiceDetail::where('product_opname_service_id', $data->id)->delete();
            $data->delete();

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        return (new BaseJsonResponse(null))->response();
    }

    public function approve(ApproveProductOpnameRequest $request, int $id)
    {
        //
        # TODO: validate ProductTransferService
        $params = $request->validated();

        $productOpnameService = ProductOpnameService::where('entity_id', $request->entity->id)->where('id', $id)->first();
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

            (new ProductOpnameApprovalService($productOpnameService))->moveStock();

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        return (new BaseJsonResponse(['id' => $productOpnameService->id]))->response();
    }

    public function reject(RejectProductOpnameRequest $request, int $id)
    {
        //
        # TODO: validate ProductTransferService
        $params = $request->validated();

        $productOpnameService = ProductOpnameService::where('entity_id', $request->entity->id)->where('id', $id)->first();
        $this->validate($productOpnameService);
        $this->validateStatus($productOpnameService, 'requested');
        
        # start transcation
        DB::beginTransaction();
        try {
            $timeNow = new DateTime();
            $timezone = new DateTimeZone($productOpnameService->location()->timezone ?? 'UTC');
            $localTimeNow = (new DateTime())->setTimezone($timezone);
            
            $productOpnameService->employee_rejected_by = $request->employee->id;
            $productOpnameService->rejected_at = $timeNow;
            $productOpnameService->local_rejected_at = $localTimeNow;
            $productOpnameService->rejected_note = $params['note'];
            $productOpnameService->status = 'rejected';
            $productOpnameService->updated_by = $request->user()->id;
            $productOpnameService->save();

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        return (new BaseJsonResponse(['id' => $productOpnameService->id]))->response();
    }

    private function baseDetailResponse(ProductOpnameService $productOpnameService): BaseJsonResponse
    {
        return new BaseJsonResponse($productOpnameService->load([
            'location:id,name',
            'employeeRequestedBy:id,first_name,last_name',
            'employeeApprovedBy:id,first_name,last_name',
            'employeeRejectedBy:id,first_name,last_name',
            'productOpnameServiceDetails.product:id,sku,code,barcode,sell_price,name,product_category_id,product_unit_id',
            'productOpnameServiceDetails.product.productUnit:id,name',
            'productOpnameServiceDetails.product.productCategory:id,name',
            'productOpnameServiceDetails.productUnit:id,name',
            'productOpnameServiceDetails.productCategory:id,name',
        ]));
    }

    private function validate(?ProductOpnameService $productOpnameService) {
        if ($productOpnameService != null) {
            return;
        }

        throw ValidationException::withMessages([
            'product_opname_service' => __('general.not_found'),
        ]);
    }

    private function validateStatus(?ProductOpnameService $productOpnameService, string $requiredStatus) {
        if ($productOpnameService->status == $requiredStatus) {
            return;
        }

        throw ValidationException::withMessages([
            'product_opname_service' => __('general.status_not_valid'),
        ]);
    }
}
