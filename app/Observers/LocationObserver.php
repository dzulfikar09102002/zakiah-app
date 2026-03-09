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
    $location->search_name = UniqueCodeGenerator::generateSearchName($location->name);

        if (!$location->initial) {
            $location->initial = $this->generateInitial($location);
        }

        if (!$location->timezone) {
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
        $location->search_name = UniqueCodeGenerator::generateSearchName($location->name);

        // regenerate initial hanya jika name berubah
        if ($location->isDirty('name')) {
            $location->initial = $this->generateInitial($location);
        }

        if (!$location->timezone) {
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

    private function generateInitial(Location $location)
    {
        $entityName = strtoupper($location->entity?->name ?? '');

        $entityPrefix = match ($entityName) {
            'ZAKIAH' => 'ZH',
            'SECACA' => 'SCC',
            default => strtoupper(substr($entityName, 0, 3)),
        };

        $locationName = strtoupper($location->name);
        $cleanLocation = preg_replace('/[^A-Z]/', '', $locationName);

        $candidates = [];

        $candidates[] = substr($cleanLocation, 0, 3);

        $consonants = preg_replace('/[AEIOU]/', '', $cleanLocation);
        if (strlen($consonants) >= 3) {
            $candidates[] = substr($consonants, 0, 3);
        }

        $alt = '';
        for ($i = 0; $i < strlen($cleanLocation); $i += 2) {
            $alt .= $cleanLocation[$i];
        }

        if (strlen($alt) >= 3) {
            $candidates[] = substr($alt, 0, 3);
        }

        if (strlen($cleanLocation) >= 4) {
            $candidates[] = substr($cleanLocation, 1, 3);
        }

        $finalInitial = null;

        foreach ($candidates as $abbr) {

            $candidate = $entityPrefix . $abbr;

            $exists = Location::where('initial', $candidate)
                ->when($location->id, fn ($q) => $q->where('id', '!=', $location->id))
                ->exists();

            if (!$exists) {
                $finalInitial = $candidate;
                break;
            }
        }

        if (!$finalInitial) {

            $base = $entityPrefix . substr($cleanLocation, 0, 3);
            $i = 1;

            do {

                $candidate = $base . $i;

                $exists = Location::where('initial', $candidate)
                    ->when($location->id, fn ($q) => $q->where('id', '!=', $location->id))
                    ->exists();

                $i++;

            } while ($exists);

            $finalInitial = $candidate;
        }

        return $finalInitial;
    }
}
