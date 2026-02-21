<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnum;
use App\Helpers\Constants\PageConstants;
use App\Helpers\Exceptions\NotFoundException;
use App\Http\Requests\ActiveCustomerRequest;
use App\Http\Requests\ArchiveCustomerRequest;
use App\Http\Requests\IndexCustomerRequest;
use App\Http\Requests\ShowCustomerRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexCustomerRequest $request)
    {
        //
        $params = $request->validated();

        $datas = Customer::with([
                'customerCategory:id,name',
                'location:id,name',
            ])
            ->where('entity_id', $request->entity->id)
            ->orderByRaw('case status when "active" then 0 else 1 end');

        if (array_key_exists('keyword', $params)) {
            $keyword =  "%" . $params['keyword'] . "%";

            $datas->where(function (Builder $builder) use($keyword) {
                $builder
                    ->where('first_name', 'like', $keyword)
                    ->orWhere('last_name', 'like', $keyword);
            });
        }

        if (array_key_exists('phone_number', $params)) {
            $datas->where('phone_number', $params['phone_number']);
        }

        return $datas->paginate($request->limit ?? PageConstants::DefaultLimit)->appends($params);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowCustomerRequest $request, int $id)
    {
        //
        $customer = Customer::where('entity_id', $request->entity->id)
                    ->where('id', $id)
                    ->first();

        if ($customer == null) {
            throw NotFoundException::withMessages([
                'customer' => __('general.not_found'),
            ]);
        }

        $response = new BaseJsonResponse($this->detailResponse($customer));
        return $response->response();
    }

    public function activate(ActiveCustomerRequest $request, int $id)
    {
        //
        $customer = Customer::where('entity_id', $request->entity->id)
                    ->where('id', $id)
                    ->first();

        if ($customer == null) {
            throw NotFoundException::withMessages([
                'customer' => __('general.not_found'),
            ]);
        }
        
        DB::transaction(function () use ($request, $customer) {
            $customer->status = StatusEnum::Active->value;
            $customer->save();
        });

        $response = new BaseJsonResponse($this->detailResponse($customer));
        return $response->response();
    }

    public function archive(ArchiveCustomerRequest $request, int $id)
    {
        //
        $customer = Customer::where('entity_id', $request->entity->id)
                    ->where('id', $id)
                    ->first();

        if ($customer == null) {
            throw NotFoundException::withMessages([
                'customer' => __('general.not_found'),
            ]);
        }
        
        DB::transaction(function () use ($request, $customer) {
            $customer->status = StatusEnum::Archived->value;
            $customer->save();
        });

        $response = new BaseJsonResponse($this->detailResponse($customer));
        return $response->response();
    }

    private function detailResponse(Customer $customer)
    {
        return $customer->load([
            'location:id,name',
            'customerCategory:id,name',
            'customerPoint:customer_id,total_point,reserved_point',
        ]);
    }
}
