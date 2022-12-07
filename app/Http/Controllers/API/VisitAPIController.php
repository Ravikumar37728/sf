<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Requests\Visit\VisitRequest;
use App\Http\Requests\Visit\ImportVisitRequest;
use App\Http\Resources\CommonCollection;
use App\Http\Resources\Visit\VisitResource;
use App\Models\Visit;
use App\Models\SalesAssociate;
use Carbon\Carbon;

use App\Traits\AuthRelationshipTrait;
use App\Traits\MessagesTrait;
use App\Traits\SspfTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Imports\VisitsImport;

class VisitAPIController extends Controller
{
    use MessagesTrait,
        AuthRelationshipTrait,
        SspfTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Visit::apply($request, false);
        return ($query->count() > 0)
            ? new CommonCollection(VisitResource::collection($query), VisitResource::class)
            : $this->getMessage([], config('constants.messages.errors.content_not_found'), config('constants.validation_codes.content_not_found'), false);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(VisitRequest $request)
    {
        if (Visit::where([
            'email' => $request->email, 'follow_up_number' => $request->follow_up_number, 'user_id' => Auth::guard('api')->id()
        ])->exists()) {
            return $this->getMessage([], config('constants.messages.errors.email_already_taken'), config('constants.validation_codes.unauthorized'), false);
        }
        if (Visit::where([
            'mobile_no' => $request->mobile_no, 'follow_up_number' => $request->follow_up_number, 'user_id' => Auth::guard('api')->id()
        ])->exists()) {
            return $this->getMessage([], config('constants.messages.errors.mobile_already_taken'), config('constants.validation_codes.unauthorized'), false);
        }
        $visit = Auth::guard('api')->user()->visits()->create($request->all());
        $visit->update(['date'=>Carbon::now()]);
        return (new VisitResource($visit))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.created'),
            'message' => config('constants.messages.success.stored')
        ], config('constants.validation_codes.created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Visit $visit)
    {
        return (new VisitResource($visit))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.showed')
        ], config('constants.validation_codes.ok'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(VisitRequest $request, Visit $visit)
    {
        if (!$this->canModifyModule('call-detail', $visit->user_id, 'update')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        $visit->update($request->all());
        return (new VisitResource($visit))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.stored')
        ], config('constants.validation_codes.ok'));
    }

    /**
     * myVisits
     *
     * @param  mixed $request
     * @return void
     */
    public function myVisits(Request $request)
    {
        $query = Auth::guard('api')->user()->visits();
        $query = $this->sspfWithColumn($request, $query, Schema::getColumnListing((new Visit())->getTable()));
        return new CommonCollection(VisitResource::collection($query), VisitResource::class);
    }

    /**
     * saWiseVisits
     *
     * @param  mixed $request
     * @param  mixed $sales_associate
     * @return void
     */
    public function saWiseVisits(Request $request, SalesAssociate $sales_associate)
    {
        $query = $sales_associate->user->visits();
        $query = $this->sspfWithColumn($request, $query, Schema::getColumnListing((new Visit())->getTable()));
        return new CommonCollection(VisitResource::collection($query), VisitResource::class);
    }

    public function importExcelVisit(ImportVisitRequest $request)
    {
        $model = new VisitsImport();
        Excel::import($model, $request->excel);
        if (count($model->getErrors()) > 0) {
            return $this->getMessage($model->getErrors(), config('constants.messages.errors.wrong_data'), config('constants.validation_codes.unprocessable_entity'), false);
        }
        return $this->getMessage([], config('constants.messages.success.imported'), config('constants.validation_codes.ok'), true);
    }
    
    public function visits ($id, request $request)
    {
        $query = Visit::where('user_id',$id)->where('date',$request->date)->paginate('15');
        // return $query;
        return new CommonCollection(VisitResource::collection($query), VisitResource::class);

    }
}
