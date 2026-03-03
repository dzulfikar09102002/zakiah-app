<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnum;
use App\Http\Requests\ActivateCustomerCategoryRequest;
use App\Http\Requests\ArchiveCustomerCategoryRequest;
use App\Http\Requests\DestroyCustomerCategoryRequest;
use App\Http\Requests\IndexCustomerCategoryRequest;
use App\Http\Requests\ShowCustomerCategoryRequest;
use App\Http\Requests\StoreCustomerCategoryRequest;
use App\Http\Requests\UpdateCustomerCategoryRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\CustomerCategory;
use App\Services\CustomerCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerCategoryController extends Controller
{
    public function __construct(
            private CustomerCategoryService $service
    ) {}
    public function index()
    {
        $pagination = $this->service->getCustomerCategories();
        return Inertia::render("customers/categories", compact("pagination"));
    }

    public function store(StoreCustomerCategoryRequest $request)
    {
         $this->service->store($request->validated());
        return to_route('customer-categories.index')->with('success', 'Kategori pelanggan berhasil ditambahkan');
    }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(ShowCustomerCategoryRequest $request, int $id)
    // {
    //     $customerCategory = CustomerCategory::where('entity_id', $request->entity->id)->where('id', $id)->first();
    //     if ($customerCategory == null) {
    //         $response = new BaseJsonResponse(null, __('general.not_found'));
    //         return $response->response(404);
    //     }

    //     $response = new BaseJsonResponse($customerCategory->load('customerCategoryRule'));
    //     return $response->response();
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(UpdateCustomerCategoryRequest $request, int $id)
    // {
    //     $customerCategory = CustomerCategory::where('entity_id', $request->entity->id)->where('id', $id)->first();
    //     if ($customerCategory == null) {
    //         $response = new BaseJsonResponse(null, __('general.not_found'));
    //         return $response->response(404);
    //     }

    //     # start transcation
    //     DB::transaction(function () use ($request, $customerCategory) {
    //         $params = $request->validated();

    //         $customerCategory->updated_by = $request->user()->id;
    //         $customerCategory->update($params);
    
    //         if ($request->has('customer_category_rule')) {
    //             $customerCategory->customerCategoryRule()->update($request->customer_category_rule);
    //         }
    //     });

    //     $response = new BaseJsonResponse($customerCategory->load('customerCategoryRule'));
    //     return $response->response();
    // }
    
    // public function activate(ActivateCustomerCategoryRequest $request, int $id)
    // {
    //     //
    //     $customerCategory = CustomerCategory::where('entity_id', $request->entity->id)->where('id', $id)->first();
    //     if ($customerCategory == null) {
    //         $response = new BaseJsonResponse(null, __('general.not_found'));
    //         return $response->response(404);
    //     }

    //     DB::transaction(function () use ($request, $customerCategory) {
    //         $customerCategory->updated_by = $request->user()->id;
    //         $customerCategory->update(['status' => StatusEnum::Active]);
    //     });

    //     $response = new BaseJsonResponse(['id' => $customerCategory->id]);
    //     return $response->response();
    // }

    // public function archive(ArchiveCustomerCategoryRequest $request, int $id)
    // {
    //     //
    //     $customerCategory = CustomerCategory::where('entity_id', $request->entity->id)->where('id', $id)->first();
    //     if ($customerCategory == null) {
    //         $response = new BaseJsonResponse(null, __('general.not_found'));
    //         return $response->response(404);
    //     }

    //     DB::transaction(function () use ($request, $customerCategory) {
    //         $customerCategory->updated_by = $request->user()->id;
    //         $customerCategory->update(['status' => StatusEnum::Archived]);
    //     });

    //     $response = new BaseJsonResponse(['id' => $customerCategory->id]);
    //     return $response->response();
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(DestroyCustomerCategoryRequest $request, int $id)
    // {
    //     $customerCategory = CustomerCategory::where('entity_id', $request->entity->id)->where('id', $id)->first();
    //     if ($customerCategory == null) {
    //         $response = new BaseJsonResponse(null, __('general.not_found'));
    //         return $response->response(404);
    //     }

    //     if ($customerCategory->status == StatusEnum::Active->value) {
    //         $response = new BaseJsonResponse(null, __('customer_category.cant_delete_active'));
    //         return $response->response(400);
    //     }

    //     # start transcation
    //     DB::transaction(function () use ($customerCategory) {
    //         $customerCategory->customerCategoryRule()->delete();
    //         $customerCategory->delete();

    //         # update customer
    //     });

    //     $response = new BaseJsonResponse(null);
    //     return $response->response();
    // }
}
