<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CallDetail\CallDetailRequest;
use App\Http\Requests\Visit\ImportVisitRequest;
use App\Http\Resources\CallDetail\CallDetailResource;
use App\Http\Resources\CommonCollection;
use App\Imports\CallsImport;
use App\Models\Admin;
use App\Models\CallDetail;
use App\Models\LeadManager;
use App\Models\SubAdmin;
use App\Traits\AuthRelationshipTrait;
use Illuminate\Support\Facades\Date;
use App\Traits\MessagesTrait;
use App\Traits\SspfTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class CallDetailAPIController extends Controller
{
    use MessagesTrait,
        AuthRelationshipTrait,
        SspfTrait;

    public function index(Request $request)
    {
        $query = CallDetail::apply($request, false);
        return ($query->count() > 0)
            ? new CommonCollection(CallDetailResource::collection($query), CallDetailResource::class)
            : $this->getMessage([], config('constants.messages.errors.content_not_found'), config('constants.validation_codes.content_not_found'), false);
    }

    /**
     * show
     *
     * @param  mixed $call_detail
     * @return void
     */
    public function show(CallDetail $call_detail)
    {
        return (new CallDetailResource($call_detail))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.showed')
        ], config('constants.validation_codes.ok'));
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(CallDetailRequest $request)
    {

        //  dd(auth::user()->id);   


        if (CallDetail::where([
            'email' => $request->email, 'follow_up_number' => $request->follow_up_number, 'user_id' => Auth::guard('api')->id()
        ])->exists()) {
            return $this->getMessage([], config('constants.messages.errors.email_already_taken'), config('constants.validation_codes.unauthorized'), false);
        }
        if (CallDetail::where([
            'mobile_no' => $request->mobile_no, 'follow_up_number' => $request->follow_up_number, 'user_id' => Auth::guard('api')->id()
        ])->exists()) {
            return $this->getMessage([], config('constants.messages.errors.mobile_already_taken'), config('constants.validation_codes.unauthorized'), false);
        }
        $call_detail = Auth::guard('api')->user()->call_details()->create($request->all());
        $call_detail->update(['date' => Carbon::now()]);
        return (new CallDetailResource($call_detail))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.created'),
            'message' => config('constants.messages.success.stored')
        ], config('constants.validation_codes.created'));
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $call_detail
     * @return void
     */
    public function update(CallDetailRequest $request, CallDetail $call_detail)
    {
        if (!$this->canModifyModule('call-detail', $call_detail->user_id, 'update')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        $call_detail->update($request->all());
        return (new CallDetailResource($call_detail))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.stored')
        ], config('constants.validation_codes.ok'));
    }

    /**
     * myCalls
     *
     * @param  mixed $request
     * @return void
     */
    public function myCalls(Request $request)
    {
        $query = Auth::guard('api')->user()->call_details();
        $query = $this->sspfWithColumn($request, $query, Schema::getColumnListing((new CallDetail())->getTable()));
        return new CommonCollection(CallDetailResource::collection($query), CallDetailResource::class);
    }

    /**
     * adminCalls
     *
     * @param  mixed $admin
     * @param  mixed $request
     * @return void
     */
    public function adminCalls(Admin $admin, Request $request)
    {
        $query = Admin::find($admin->id)->user->call_details();
        $query = $this->sspfWithColumn($request, $query, Schema::getColumnListing((new CallDetail())->getTable()));
        return new CommonCollection(CallDetailResource::collection($query), CallDetailResource::class);
    }

    /**
     * subAdminCalls
     *
     * @param  mixed $sub_admin
     * @param  mixed $request
     * @return void
     */
    public function subAdminCalls(SubAdmin $sub_admin, Request $request)
    {
        $query = SubAdmin::find($sub_admin->id)->user->call_details();
        $query = $this->sspfWithColumn($request, $query, Schema::getColumnListing((new CallDetail())->getTable()));
        return new CommonCollection(CallDetailResource::collection($query), CallDetailResource::class);
    }


    public function leadmnagercalls(LeadManager $leadManager, request $request)
    {
        
        $query = LeadManager::find($leadManager->id)->user->call_details();
        $query = $this->sspfWithColumn($request, $query, Schema::getColumnListing((new CallDetail())->getTable()));
        return new CommonCollection(CallDetailResource::collection($query), CallDetailResource::class);
    }

    public function calls($id, Request $request)
    {
       
        $query = CallDetail::where('user_id', $id)->where('date', $request->date)->paginate('15');
        return $query;
    }

    public function importExcelCall(ImportVisitRequest $request)
    {
        $model = new CallsImport();
        Excel::import($model, $request->excel);
        if (count($model->getErrors()) > 0) {
            return $this->getMessage($model->getErrors(), config('constants.messages.errors.wrong_data'), config('constants.validation_codes.unprocessable_entity'), false);
        }
        return $this->getMessage([], config('constants.messages.success.imported'), config('constants.validation_codes.ok'), true);
    }
}
