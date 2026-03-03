<?php
namespace App\Services;

use App\Enums\StatusEnum;
use App\Models\CustomerCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
class CustomerCategoryService
{
    public function getCustomerCategories()
    {
        $entityId = auth()->user()?->entity?->id;

        return CustomerCategory::query()
            ->where('entity_id', $entityId)->with('customerCategoryRule')
            
            ->when(request('search'), function (Builder $query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })

            ->when(request('statuses'), function (Builder $query, $statuses) {
                $query->whereIn('status', $statuses);
            })

            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function store(array $data)
    {
        $user = auth()->user();
        return DB::transaction(function () use ($data, $user) 
        {
            $category = CustomerCategory::create([
                'name'       => $data['name'],
                'entity_id'  => $user->entity->id,
                'status' => StatusEnum::Active,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $minimalSpend = $data['customer_category_rule']['minimal_spend'] ?? 0;
        
            $category->customerCategoryRule()->create([
                'minimal_spend' => $minimalSpend,
            ]);

            return $category;
        });
    }
    public function update(CustomerCategory $category, array $data)
    {
        $category->update([
            'name' => $data['name'],
            'updated_by' => auth()->id(),
        ]);

        $category->customerCategoryRule->update([
            'minimal_spend' => data_get(
                $data,
                'customer_category_rule.minimal_spend',
                $category->customerCategoryRule->minimal_spend
            ),
            'updated_by' => auth()->id(),
        ]);

        return $category;
    }

    public function delete(CustomerCategory $customerCategory)
    {
        $customerCategory->delete();
    }

    public function getDeleted()
    {
        $entityId = auth()->user()?->entity?->id;

        return CustomerCategory::query()->onlyTrashed()
            ->where('entity_id', $entityId)->with('customerCategoryRule')
            
            ->when(request('search'), function (Builder $query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })

            ->when(request('statuses'), function (Builder $query, $statuses) {
                $query->whereIn('status', $statuses);
            })

            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function restore(int $id)
    {
        $customerCategory = CustomerCategory::withTrashed()->findOrFail($id);
        return $customerCategory->restore();
    }
}