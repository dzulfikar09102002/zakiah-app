<?php

namespace App\Http\Controllers\Kasir;

use App\Helpers\Services\Checkpoint\CustomerOrderCheckpoint;
use App\Helpers\Services\Checkpoint\SaleTransactionCheckpoint;
use App\Helpers\UniqueCodeGenerator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\StoreKasirAuthRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Device;
use Illuminate\Support\Facades\DB;

class KasirAuthController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKasirAuthRequest $request)
    {
        //
        $device = new Device();

        # start transcation
        DB::transaction(function () use ($request, $device) {
            $params = $request->validated();

            $device->employee_id = $request->employee->id;
            $device->location_id = $params['location_id'];
            $device->code = UniqueCodeGenerator::generateCode();
            $device->device_id = $params['device_id'];
            $device->device_name = $params['device_name'];
            $device->device_type = $params['device_type'];
            $device->save();

            # checkpoint
            (new CustomerOrderCheckpoint($device, $device->location))->checkpoint();
            (new SaleTransactionCheckpoint($device, $device->location))->checkpoint();
        });

        $response = new BaseJsonResponse([
            "device" => $device,
        ]);
        return $response->response();
    }
}
