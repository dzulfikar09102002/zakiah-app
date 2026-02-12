<?php

namespace App\Http\Services;

use App\Models\Location;

class LocationService{
    public function paginate(int $entityId, int $perPage = 10)
    {
        return Location::where('entity_id', $entityId)
        ->paginate($perPage)
        ->withQueryString();
    }
}