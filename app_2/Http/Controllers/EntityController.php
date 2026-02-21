<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreentityRequest;
use App\Http\Requests\UpdateEntityRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Entity;

class EntityController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Entity $entity)
    {
        $response = new BaseJsonResponse($entity);
        return $response->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEntityRequest $request, Entity $entity)
    {
        if ($request->entity->id != $entity->id) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('auth.password'));
            return $response->response(422);
        }

        $entity->update($request->validated());

        $response = new BaseJsonResponse($entity);
        return $response->response();
    }
}
