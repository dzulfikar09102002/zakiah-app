<?php

namespace App\Http\Controllers;

use App\Helpers\Constants\PageConstants;
use App\Helpers\Exceptions\NotFoundException;
use App\Http\Requests\IndexCustomerPointMovementRequest;
use App\Models\Customer;

class CustomerPointMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexCustomerPointMovementRequest $request)
    {
        //
        $params = $request->validated();
        
        $customer = $this->getCustomer($request->entity->id, $params['customer_id']);
        if ($customer == null) {
            throw NotFoundException::withMessages([
                'customer' => __('general.not_found'),
            ]);
        }

        $customerPoint = $customer->customerPoint()->first();
        if ($customerPoint == null) {
            throw NotFoundException::withMessages([
                'customer_point' => __('general.not_found'),
            ]);
        }

        $datas = $customerPoint->customerPointMovements()
            ->where('created_at', '>=', $params['start_at'])
            ->where('created_at', '<=', $params['end_at'])
            ->whereIn('location_id', $params['locs'])
            ->orderByDesc('id');

        if (array_key_exists('types', $params)) {
            $datas->whereIn('type', $params['types']);
        }

        return $datas->paginate($request->limit ?? PageConstants::DefaultLimit)->appends($params);
    }

    private function getCustomer(int $entityId, int $customerId): Customer
    {
        return Customer::where('entity_id', $entityId)
            ->where('id', $customerId)
            ->first();
    }
}
