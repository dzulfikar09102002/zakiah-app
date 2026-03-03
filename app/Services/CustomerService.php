<?php
namespace App\Services;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
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

        ->paginate(request('per_page', 10))
        ->withQueryString();
}
}