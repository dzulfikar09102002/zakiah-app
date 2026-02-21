<?php

namespace App\Http\Controllers;

use App\Helpers\Services\Promo\PromoCreator;
use App\Helpers\Services\Promo\PromoUpdater;
use App\Http\Requests\IndexPromoRequest;
use App\Http\Requests\ShowPromoRequest;
use App\Http\Requests\StorePromoRequest;
use App\Http\Requests\UpdatePromoRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Promo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class PromoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexPromoRequest $request)
    {
        //
        $params = $request->validated();

        $data = Promo::where('entity_id', $request->entity->id)
            ->with([
                'ownerLocation:id,name,code,initial',
            ])
            ->orderBy('start_at');

        if ($params['select_all_location'] == 'true' && array_key_exists('exclude_locs', $params)) {
            $data->whereNotIn('owner_location_id', $params['exclude_locs']);
        } else if ($params['select_all_location'] == 'false' && array_key_exists('locs', $params)) {
            $data->whereIn('owner_location_id', $params['locs']);
        }

        if (array_key_exists('keyword', $params)) {
            $keyword =  "%" . $params['keyword'] . "%";
            $data->where(function (Builder $builder) use($keyword) {
                $builder->where('name', 'like', $keyword)->orWhere('code', 'like', $keyword);
            });
        }

        if ($request->exists('statuses')) {
            $data->whereIn('status', $request->statuses);
        }

        if ($request->exists('channels')) {
            $data->whereIn('channel', $request->channels);
        }

        if ($request->exists('start_at')) {
            $start_at = $request->start_at;

            $data->where(function (Builder $builder) use($start_at) {
                $builder->where('end_at', '>=', $start_at)->orWhere('end_at', null);
            });
        }

        if ($request->exists('end_at')) {

            $data->where('start_at', '<=', $request->end_at);
        }

        return $data->paginate($request->limit)->appends($params);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePromoRequest $request)
    {
        # start transcation
        DB::beginTransaction();
        try {
            $promo = (new PromoCreator($request->entity, $request->validated()))->create();

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        $response = new BaseJsonResponse($promo);
        return $response->response();
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowPromoRequest $request, Promo $promo)
    {
        //
        if ($request->entity->id != $promo->entity_id) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('entity.invalid_entity'));
            return $response->response(422);
        }

        $response = new BaseJsonResponse($promo->load(
            'ownerLocation:id,name,code,initial',
            'promoRule.promoRuleCustomerCategories',
            'promoRule.promoRuleOrderTypes:order_type_id',
            'promoRule.promoRuleProducts',
            'promoReward.promoRewardProducts',
        ));
        return $response->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePromoRequest $request, Promo $promo)
    {
        # start transcation
        DB::beginTransaction();
        try {
            $promo = (new PromoUpdater($request->entity, $promo, $request->validated()))->create();

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        $response = new BaseJsonResponse($promo);
        return $response->response();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Promo $promo)
    {
        //
    }
}
