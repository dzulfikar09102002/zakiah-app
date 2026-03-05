<?php
namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class CustomerService
{
    
    public function getCustomers()
    {
        $entityId = auth()->user()?->entity?->id;

        return Customer::query()
            ->with([
                'customerCategory:id,name',
                'location:id,name',
            ])
            ->where('entity_id', $entityId)
            ->when(request('search'), function (Builder $query, $search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })

            ->when(request('phone_number'), function (Builder $query, $phone) {
                $query->where('phone_number', $phone);
            })
             ->orderByDesc('id') 
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function getCustomerCategories()
    {
        $entityId = auth()->user()?->entity?->id;
        return CustomerCategory::where('entity_id', $entityId)->get();
    }

    public function getLocations()
    {
        $entityId = auth()->user()?->entity?->id;
        return Location::where('entity_id', $entityId)->get();
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            $authUser = auth()->user();

            $phone = $data['phone_number'];
            $defaultPassword = $phone . '12345678';

            $user = User::create([
                'name'     => $phone,
                'password' => Hash::make($defaultPassword),
                'phone_number_country_code' => $data['phone_number_country_code'],
                'phone_number'              => $data['phone_number'],
            ]);

            return Customer::create([
                'entity_id'                 => $authUser->entity->id,
                'user_id'                   => $user->id,
                'first_name'                => $data['first_name'],
                'last_name'                 => $data['last_name'],
                'phone_number_country_code' => $data['phone_number_country_code'],
                'customer_category_id'      => $data['customer_category_id'],
                'phone_number'              => $data['phone_number'],
                'location_id'               => $data['location_id'],
                'created_by'                => $authUser->id,
                'updated_by'                => $authUser->id,
            ]);
        });
    }
    public function update(Customer $customer, array $data)
    {
        return DB::transaction(function () use ($customer, $data) {

            $authUser = auth()->user();
            $phone = $data['phone_number'];
            $customer->user->update([
                'name'     => $phone,
                'phone_number_country_code' => $data['phone_number_country_code'],
                'phone_number'              => $data['phone_number'],
            ]);

            $customer->update([
                'first_name'                => $data['first_name'],
                'last_name'                 => $data['last_name'],
                'phone_number_country_code' => $data['phone_number_country_code'],
                'customer_category_id'      => $data['customer_category_id'],
                'phone_number'              => $data['phone_number'],
                'location_id'               => $data['location_id'],
                'updated_by'                => $authUser->id,
            ]);

            return $customer->fresh(['customerCategory', 'location']);
        });
    }

    public function delete(Customer $customer)
    {
        return $customer->delete();
    }

    public function restore(int $id)
    {
        $customer = Customer::withTrashed()->findOrFail($id);
        return $customer->restore();
    }

    public function getDeletedCustomers()
    {
        $entityId = auth()->user()?->entity?->id;

        return Customer::onlyTrashed()
            ->with([
                'customerCategory:id,name',
                'location:id,name',
            ])
            ->where('entity_id', $entityId)
            ->when(request('search'), function (Builder $query, $search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })

            ->when(request('phone_number'), function (Builder $query, $phone) {
                $query->where('phone_number', $phone);
            })
             ->orderByDesc('id') 
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }
}