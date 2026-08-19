<?php

namespace App\Services;

use App\Helpers\UniqueCodeGenerator;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LocationService
{
    public function getLocations()
    {
        $search = request('search', '');

        return Location::query()
            ->where('entity_id', auth()->user()?->entity?->id)

            ->when($search, function ($query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })

            ->when(request('statuses'), fn ($query, $statuses) => $query->whereIn('status', (array) $statuses)
            )
            ->orderBy('id');
    }

    public function getLocationOptions()
    {
        return Location::query()
            ->where('entity_id', auth()->user()?->entity?->id)
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn (Location $location) => [
                'label' => Str::title(Str::lower($location->name)),
                'value' => $location->id,
            ]);
    }
    public function getDeletedLocations()
    {
        $search = request('search', '');

        return Location::query()
            ->where('entity_id', auth()->user()?->entity?->id)
            ->onlyTrashed()
            ->when($search, function ($query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })

            ->when(request('statuses'), fn ($query, $statuses) => $query->whereIn('status', (array) $statuses)
            )

            ->orderBy('id')

            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            $authUser = auth()->user();

            $location = new Location;

            $location->entity_id = $authUser->entity->id;
            $location->code = UniqueCodeGenerator::generateCode();
            $location->created_by = $authUser->id;
            $location->updated_by = $authUser->id;
            $location->name = $data['name'] ?? null;
            $location->backoffice_email = $data['backoffice_email'] ?? null;
            $location->contact_email = $data['contact_email'] ?? null;
            $location->backoffice_phone_number_country_code = $data['backoffice_phone_number_country_code'] ?? null;
            $location->backoffice_phone_number = $data['backoffice_phone_number'] ?? null;
            $location->contact_phone_number_country_code = $data['backoffice_phone_number_country_code'] ?? null;
            $location->contact_phone_number = $data['backoffice_phone_number'] ?? null;
            $location->kind = $data['kind'] ?? null;
            $location->status = $data['status'] ?? 'active';
            $location->full_address = $data['full_address'] ?? null;
            $location->postal_code = $data['postal_code'] ?? null;
            $location->district = $data['district'] ?? null;
            $location->city = $data['city'] ?? null;
            $location->province = $data['province'] ?? null;
            $location->country = $data['country'] ?? null;
            $location->footer = $data['footer'] ?? null;

            $location->save();

            return $location;
        });
    }

    public function update(array $data, Location $location)
    {
        return DB::transaction(function () use ($data, $location) {

            $authUser = auth()->user();

            $location->updated_by = $authUser->id;
            $location->name = $data['name'] ?? $location->name;
            $location->backoffice_email = $data['backoffice_email'] ?? $location->backoffice_email;
            $location->contact_email = $data['contact_email'] ?? $location->contact_email;
            $location->backoffice_phone_number_country_code = $data['backoffice_phone_number_country_code'] ?? $location->backoffice_phone_number_country_code;
            $location->backoffice_phone_number = $data['backoffice_phone_number'] ?? $location->backoffice_phone_number;
            $location->contact_phone_number_country_code = $data['contact_phone_number_country_code'] ?? $location->contact_phone_number_country_code;
            $location->contact_phone_number = $data['contact_phone_number'] ?? $location->contact_phone_number;
            $location->kind = $data['kind'] ?? $location->kind;
            $location->status = $data['status'] ?? $location->status;
            $location->full_address = $data['full_address'] ?? $location->full_address;
            $location->postal_code = $data['postal_code'] ?? $location->postal_code;
            $location->district = $data['district'] ?? $location->district;
            $location->city = $data['city'] ?? $location->city;
            $location->province = $data['province'] ?? $location->province;
            $location->country = $data['country'] ?? $location->country;
            $location->footer = $data['footer'] ?? $location->footer;

            $location->save();

            return $location;
        });
    }

    public function delete(Location $location)
    {
        return $location->delete();
    }

    public function restore(int $id)
    {
        $location = Location::withTrashed()->findOrFail($id);

        return $location->restore();
    }
}
