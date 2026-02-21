<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;

class LocationService
{
    public function getLocations()
    {
        $search = request('search', '');

    return Location::query()
        ->where('entity_id', auth()->user()?->entity?->id)
        ->withTrashed()

        ->when($search, function ($query) use ($search) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        })

        ->when(request('statuses'), fn ($query, $statuses) =>
            $query->whereIn('status', (array) $statuses)
        )

        ->orderBy('id')

        ->paginate(request('per_page', 10))
        ->withQueryString();
    }
}
