<?php

namespace App\Observers;

use App\Helpers\Services\Employee\EmployeeLocationSetter;
use App\Helpers\UniqueCodeGenerator;
use App\Models\Location;

class LocationObserver
{
    /**
     * Handle the Location "creating" event.
     */
    public function creating(Location $location): void
    {
        //
        $location->search_name = UniqueCodeGenerator::generateSearchName($location->name);
        if ($location->initial == null || $location->initial == '') {
            $location->initial = UniqueCodeGenerator::generateInitial($location->search_name);
        }
        if ($location->timezone == null || $location->timezone == '') {
            $location->timezone = 'Asia/Jakarta';
        }
        $location->checksum = $this->generateChecking($location);
    }
    /**
     * Handle the Location "creating" event.
     */
    public function created(Location $location): void
    {
        # TODO: move to job
        (new EmployeeLocationSetter($location))->create();
    }

    /**
     * Handle the Location "updating" event.
     */
    public function updating(Location $location): void
    {
        //
        $location->search_name = UniqueCodeGenerator::generateSearchName($location->name);
        if ($location->initial == null || $location->initial == '') {
            $location->initial = UniqueCodeGenerator::generateInitial($location->search_name);
        }
        if ($location->timezone == null || $location->timezone == '') {
            $location->timezone = 'Asia/Jakarta';
        }
        $location->checksum = $this->generateChecking($location);
    }

    /**
     * Handle the Location "deleting" event.
     */
    public function deleting(Location $location): void
    {
        //
        $location->checksum = $this->generateChecking($location);
    }

    private function generateChecking(Location $location) : string {
        return md5(
            $location->entity_id . '-' . 
            $location->search_name . '-' . 
            $location->deleted_at ?? 0
        );
    }
}
