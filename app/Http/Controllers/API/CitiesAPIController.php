<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Traits\MessagesTrait;
use App\Http\Requests\Location\CitiesOfStateRequest;
use App\Http\Resources\City\CitiesResource;
use App\Http\Resources\CommonCollection;
use App\Traits\ConvertToStringTrait;
use App\Traits\SspfTrait;
use Illuminate\Support\Facades\Schema;

class CitiesAPIController extends Controller
{
    use MessagesTrait, ConvertToStringTrait, SspfTrait;

    public function index(Request $request)
    {
        $model = new City();
        $query = ($request->get('is_light', false))
            ? City::select($model->light)->where('country_id', 101)->get()
            : $this->sspfWithColumn($request, City::where('country_id', 101), Schema::getColumnListing((new City())->getTable()));
        return ($query->count())
            ? new CommonCollection(CitiesResource::collection($query), CitiesResource::class)
            : $this->getMessage([], config('constants.messages.errors.content_not_found'), config('constants.validation_codes.content_not_found'), false);
    }

    public function show(City $city)
    {
        return (new CitiesResource($city->load([])))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.showed')
        ], config('constants.validation_codes.ok'));
    }

    public function cities_of_state(CitiesOfStateRequest $request)
    {
        $model = new City();
        $query = ($request->get('is_light', false))
            ? City::select($model->light)->where('state_id', $request['state_id'])->get()
            : City::where('state_id', $request['state_id'])->get();
        return CitiesResource::collection($query);
    }
}
