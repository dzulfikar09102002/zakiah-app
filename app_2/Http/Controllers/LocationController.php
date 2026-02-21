<?php

namespace App\Http\Controllers;

use App\Helpers\Exceptions\BadRequestException;
use App\Helpers\Exceptions\NotFoundException;
use App\Helpers\UniqueCodeGenerator;
use App\Http\Requests\IndexLocationRequest;
use App\Http\Requests\ShowLocationRequest;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use App\Http\Responses\BaseJsonResponse;
use Illuminate\Database\Eloquent\Builder;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexLocationRequest $request)
    {
        $params = $request->validated();
        $locations = Location::where('entity_id', $request->entity->id)->orderBy('id');

        if (array_key_exists('keyword', $params)) {
            $keyword =  "%" . $params['keyword'] . "%";
            $locations->where(function (Builder $builder) use($keyword) {
                $builder->where('name', 'like', $keyword)->orWhere('code', 'like', $keyword);
            });
        }

        if ($request->exists('statuses')) {
            $locations->whereIn('status', $request->statuses);
        }

        return $locations->paginate($request->limit)->appends($params);
    }

    public function dropdown(IndexLocationRequest $request)
    {
        $params = $request->validated();
        $locations = Location::where('entity_id', $request->entity->id)
            ->select('id', 'name');

        if (array_key_exists('keyword', $params)) {
            $keyword =  "%" . $params['keyword'] . "%";
            $locations->where(function (Builder $builder) use($keyword) {
                $builder->where('name', 'like', $keyword)->orWhere('code', 'like', $keyword);
            });
        }

        if ($request->exists('statuses')) {
            $locations->whereIn('status', $request->statuses);
        }

        return $locations->cursorPaginate($request->limit)->appends($params);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLocationRequest $request)
    {
        $params = $request->validated();

        $location = new Location();
        # not in fillable
        $location->entity_id = $request->entity->id;
        $location->code = UniqueCodeGenerator::generateCode();
        $location->initial = UniqueCodeGenerator::generateInitial();
        $location->search_name = UniqueCodeGenerator::generateSearchName($params['name']);
        $location->created_by = $request->user()->id;
        $location->updated_by = $request->user()->id;
        $location->fill($params);

        if (!array_key_exists('timezone', $params)) {
            $location->timezone = $request->entity->timezone;
        }

        $location->save();

        $response = new BaseJsonResponse($location);
        return $response->response();
    }

    /**
     * Display the specified resource.
     */
    # TODO: fix error when not found
    public function show(ShowLocationRequest $request, int $id)
    {
        $location = Location::where('id', $id)->first();

        if ($location == null) {
            throw NotFoundException::withMessages([
                'location' => __('general.not_found'),
            ]);
        }

        if ($request->entity->id != $location->entity_id) {
            throw NotFoundException::withMessages([
                'location' => __('general.invalid_entity'),
            ]);
        }

        $response = new BaseJsonResponse($location);
        return $response->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLocationRequest $request, int $id)
    {
        $location = Location::where('id', $id)->first();

        if ($location == null) {
            throw NotFoundException::withMessages([
                'location' => __('general.not_found'),
            ]);
        }

        if ($request->entity->id != $location->entity_id) {
            throw BadRequestException::withMessages([
                'location' => __('general.invalid_entity'),
            ]);
        }

        $params = $request->validated();
        if ($request->exists('name')) {
            $location->search_name = UniqueCodeGenerator::generateSearchName($params['name']);
        }

        $location->fill($params);
        if (!array_key_exists('timezone', $params)) {
            $location->timezone = $request->entity->timezone;
        }

        $location->updated_by = $request->user()->id;
        $location->save();

        $response = new BaseJsonResponse($location);
        return $response->response();
    }
}
