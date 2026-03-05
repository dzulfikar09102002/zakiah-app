<?php

namespace App\Http\Controllers;

use App\Helpers\Exceptions\BadRequestException;
use App\Helpers\Exceptions\NotFoundException;
use App\Helpers\UniqueCodeGenerator;
use App\Http\Requests\ActivateLoyaltyRequest;
use App\Http\Requests\ArchiveLoyaltyRequest;
use App\Http\Requests\DeactivateLoyaltyRequest;
use App\Http\Requests\DestroyLoyaltyRequest;
use App\Http\Requests\IndexLoyaltyRequest;
use App\Http\Requests\ShowLoyaltyRequest;
use App\Http\Requests\StoreLoyaltyRequest;
use App\Http\Requests\UpdateLoyaltyRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Loyalty;
use App\Models\LoyaltyRewardProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LoyaltyController extends Controller
{
    public function index()
    {
        return Inertia::render("comingsoon/index");
    }
    // /**
    //  * Display a listing of the resource.
    //  */
    // public function index(IndexLoyaltyRequest $request)
    // {
    //     //
    //     $params = $request->validated();
    //     $data = Loyalty::where('entity_id', $request->entity->id)
    //         ->orderByRaw('case status when "active" then 0 when "in_active" then 1 else 2 end');

    //     if (array_key_exists('keyword', $params)) {
    //         $keyword =  "%" . $params['keyword'] . "%";
            
    //         $data->where(function (Builder $builder) use($keyword) {
    //             $builder->where('name', 'like', $keyword);
    //         });
    //     }

    //     return $data->paginate($request->limit)->appends($params);
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(StoreLoyaltyRequest $request)
    // {
    //     //
    //     $loyalty = new Loyalty();

    //     # start transcation
    //     DB::transaction(function () use ($request, $loyalty) {
    //         $params = $request->validated();

    //         # not in fillable
    //         $loyalty->entity_id = $request->entity->id;
    //         $loyalty->code = UniqueCodeGenerator::generateCode();
    //         $loyalty->status = 'active';
    //         $loyalty->created_by = $request->user()->id;
    //         $loyalty->updated_by = $request->user()->id;
    //         $loyalty->fill($params);
    //         $loyalty->save();

    //         $rewardProducts = [];
    //         foreach($params['reward_products'] as $rewardProduct)
    //         {
    //             array_push($rewardProducts, array_merge($rewardProduct, ["entity_id" => $loyalty->entity_id]));
    //         }

    //         $loyalty->rewardProducts()->createMany($rewardProducts);

    //         Loyalty::where('entity_id', $loyalty->entity_id)->where('id', '!=', $loyalty->id)->where('status', 'active')->update(['status' => 'in_active']);
    //     });

    //     $response = new BaseJsonResponse(['id' => $loyalty->id]);
    //     return $response->response(201);
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(ShowLoyaltyRequest $request, int $id)
    // {
    //     //
    //     $loyalty = Loyalty::where('entity_id', $request->entity->id)->where('id', $id)->first();
    //     if ($loyalty == null) {
    //         throw NotFoundException::withMessages([
    //             'loyalty' => __('general.not_found'),
    //         ]);
    //     }

    //     $loyaltyResponse = $loyalty->load(
    //         'rewardProducts.product:id,sku,code,name',
    //         'rewardProducts.productUnit:id,name',
    //     );

    //     $response = new BaseJsonResponse($loyaltyResponse);
    //     return $response->response();
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(UpdateLoyaltyRequest $request, int $id)
    // {
    //     //
    //     $loyalty = Loyalty::where('entity_id', $request->entity->id)->where('id', $id)->first();
    //     if ($loyalty == null) {
    //         throw NotFoundException::withMessages([
    //             'loyalty' => __('general.not_found'),
    //         ]);
    //     }

    //     DB::transaction(function () use ($request, $loyalty) {
    //         $params = $request->validated();

    //         # not in fillable
    //         $loyalty->updated_by = $request->user()->id;
    //         $loyalty->update($params);

    //         $rewardProducts = [];
    //         $deletedIds = [];
    //         foreach($params['reward_products'] as $rewardProduct)
    //         {
    //             # new
    //             if (!array_key_exists('id', $rewardProduct)) {
    //                 array_push($rewardProducts, array_merge($rewardProduct, ["entity_id" => $loyalty->entity_id]));
    //             } else if (array_key_exists('_destroy', $rewardProduct) && $rewardProduct['_destroy'] == true) {
    //                 array_push($deletedIds, $rewardProduct['id']);
    //             } else {
    //                 $foundRewardProduct = LoyaltyRewardProduct::findOrFail($rewardProduct['id']);
    //                 $foundRewardProduct->update($rewardProduct);
    //             }
    //         }

    //         $loyalty->rewardProducts()->createMany($rewardProducts);
    //         $loyalty->rewardProducts()->whereIn('id', $deletedIds)->delete();

    //         if ($loyalty->status == 'active') {
    //             Loyalty::where('entity_id', $loyalty->entity_id)
    //                 ->where('id', '!=', $loyalty->id)
    //                 ->where('status', 'active')
    //                 ->update(['status' => 'in_active']);
    //         }
    //     });

    //     $response = new BaseJsonResponse(['id' => $loyalty->id]);
    //     return $response->response();
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(DestroyLoyaltyRequest $request, int $id)
    // {
    //     //
    //     $loyalty = Loyalty::where('entity_id', $request->entity->id)->where('id', $id)->first();
    //     if ($loyalty == null) {
    //         throw NotFoundException::withMessages([
    //             'loyalty' => __('general.not_found'),
    //         ]);
    //     }

    //     if ($loyalty->status == 'active') {
    //         throw BadRequestException::withMessages([
    //             'loyalty' => __('loyalty.cant_delete_active_loyalty'),
    //         ]);
    //     }

    //     DB::transaction(function () use ($loyalty) {
    //         # not in fillable
    //         $loyalty->rewardProducts()->where('loyalty_id', $loyalty->id)->delete();
    //         $loyalty->delete();
    //     });

    //     $response = new BaseJsonResponse(['id' => $loyalty->id]);
    //     return $response->response();
    // }

    // public function archive(ArchiveLoyaltyRequest $request, int $id)
    // {
    //     //
    //     $loyalty = Loyalty::where('entity_id', $request->entity->id)->where('id', $id)->first();
    //     if ($loyalty == null) {
    //         throw NotFoundException::withMessages([
    //             'loyalty' => __('general.not_found'),
    //         ]);
    //     }

    //     $loyalty->updated_by = $request->user()->id;

    //     DB::transaction(function () use ($loyalty) {
    //         $loyalty->update(['status' => 'archived']);
    //     });

    //     $response = new BaseJsonResponse(['id' => $loyalty->id]);
    //     return $response->response();
    // }

    // public function deactivate(DeactivateLoyaltyRequest $request, int $id)
    // {
    //     //
    //     $loyalty = Loyalty::where('entity_id', $request->entity->id)->where('id', $id)->first();
    //     if ($loyalty == null) {
    //         throw NotFoundException::withMessages([
    //             'loyalty' => __('general.not_found'),
    //         ]);
    //     }
    //     $loyalty->updated_by = $request->user()->id;

    //     DB::transaction(function () use ($loyalty) {
    //         $loyalty->update(['status' => 'in_active']);
    //     });

    //     $response = new BaseJsonResponse(['id' => $loyalty->id]);
    //     return $response->response();
    // }

    // public function activate(ActivateLoyaltyRequest $request, int $id)
    // {
    //     //
    //     $loyalty = Loyalty::where('entity_id', $request->entity->id)->where('id', $id)->first();
    //     if ($loyalty == null) {
    //         throw NotFoundException::withMessages([
    //             'loyalty' => __('general.not_found'),
    //         ]);
    //     }

    //     $loyalty->updated_by = $request->user()->id;
    //     DB::transaction(function () use ($loyalty) {
    //         $loyalty->update(['status' => 'active']);

    //         if ($loyalty->status == 'active') {
    //             Loyalty::where('entity_id', $loyalty->entity_id)
    //                 ->where('id', '!=', $loyalty->id)
    //                 ->where('status', 'active')
    //                 ->update(['status' => 'in_active']);
    //         }
    //     });

    //     $response = new BaseJsonResponse(['id' => $loyalty->id]);
    //     return $response->response();
    // }
}
