<?php

namespace App\Services;

use App\Helpers\UniqueCodeGenerator;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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

    public function store(array $data)
{
    dd($data);
    return DB::transaction(function () use ($data) {

        $authUser = auth()->user();

        $location = new Location();

        $location->entity_id = $authUser->entity->id;
        $location->code = UniqueCodeGenerator::generateCode();
        $location->initial = UniqueCodeGenerator::generateInitial();
        $location->search_name = UniqueCodeGenerator::generateSearchName($data['name']);
        $location->created_by = $authUser->id;
        $location->updated_by = $authUser->id;
        $location->name = $data['name'] ?? null;
        $location->backoffice_email = $data['backoffice_email'] ?? null;
        $location->contact_email = $data['contact_email'] ?? null;
        $location->backoffice_phone_number_country_code = $data['backoffice_phone_number_country_code'] ?? null;
        $location->backoffice_phone_number = $data['backoffice_phone_number'] ?? null;
        $location->kind = $data['kind'] ?? null;
        $location->status = $data['status'] ?? 'active';
        $location->full_address = $data['full_address'] ?? null;
        $location->postal_code = $data['postal_code'] ?? null;
        $location->district = $data['district'] ?? null;
        $location->city = $data['city'] ?? null;
        $location->province = $data['province'] ?? null;
        $location->country = $data['country'] ?? null;
        $location->footer = $data['footer'] ?? null;

        if (!array_key_exists('timezone', $data)) {
            $location->timezone = $authUser->entity->timezone;
        } else {
            $location->timezone = $data['timezone'];
        }

        $location->save();

        return $location;
    });
}
}
