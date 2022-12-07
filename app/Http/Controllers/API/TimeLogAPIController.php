<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeLog\TimeLogRequest;
use App\Http\Resources\CommonCollection;
use App\Http\Resources\TimeLog\CallResource;
use App\Http\Resources\TimeLog\TimeLogResource;
use App\Http\Resources\Visit\VisitResource;
use App\Models\Admin;
use App\Models\Call;
use App\Models\LeadManager;
use App\Models\SalesAssociate;
use App\Models\SubAdmin;
use App\Models\User;
use App\Models\Visit;
use App\Traits\CodeGeneraterTrait;
use App\Traits\MessagesTrait;
use App\Traits\SspfTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TimeLogAPIController extends Controller
{
    use MessagesTrait,
        CodeGeneraterTrait,
        SspfTrait;

    /**
     * inTimeLog
     *
     * @param  mixed $request
     * @return void
     */
    public function inTimeLog(TimeLogRequest $request)
    {
        if (Auth::guard('api')->user()->time_logs()->whereDate('created_at', Carbon::now()->toDateString())->exists()) {
            return $this->getMessage([], config('constants.messages.errors.in_time_already_added'), config('constants.validation_codes.forbidden'), false);
        }
        $in_time = Auth::guard('api')->user()->time_logs()->create([
            'in_time' => Carbon::now()->toTimeString()
        ]);
        return (new TimeLogResource($in_time))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.created'),
            'message' => config('constants.messages.success.stored')
        ], config('constants.validation_codes.created'));
    }

    /**
     * checkOutTime
     *
     * @return void
     */
    public function checkOutTime()
    {
        $user = Auth::guard('api')->user();
        $time_log = $user->today_time_log;
        $time_log->calls = $this->createCall($user);
        return (new TimeLogResource($time_log))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.showed')
        ], config('constants.validation_codes.ok'));
    }

    /**
     * outTimeLog
     *
     * @return void
     */
    public function outTimeLog()
    {
        $user = Auth::guard('api')->user();
        $check_in_time = $user->time_logs()->whereNotNull('in_time')->whereDate('created_at', Carbon::now()->toDateString())->first();
        if (!$check_in_time) {
            return $this->getMessage([], config('constants.messages.errors.something_wrong'), config('constants.validation_codes.unprocessable_entity'), false);
        }
        // dd(!empty($check_in_time->out_time));
        // if (!$check_in_time->whereNull('out_time')->exists()) {
        //     return $this->getMessage([], config('constants.messages.errors.out_time_already_added'), config('constants.validation_codes.forbidden'), false);
        // }
        if (!empty($check_in_time->out_time)) {
            return $this->getMessage([], config('constants.messages.errors.out_time_already_added'), config('constants.validation_codes.forbidden'), false);
        }
        $check_in_time->update(['out_time' => Carbon::now()->toTimeString()]);
        $user->calls()->create($this->createCall($user));
        return (new TimeLogResource($check_in_time))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.updated')
        ], config('constants.validation_codes.ok'));
    }

    /**
     * createCall
     *
     * @param  mixed $user
     * @return void
     */
    public function createCall(User $user)
    {
        $calls['count'] = ($user->user_type == config('constants.users.user_type.1') || $user->user_type == config('constants.users.user_type.2') ||  $user->user_type == config('constants.users.user_type.3'))
            ? $user->call_details()->whereDate('created_at', Carbon::now()->toDateString())->count()
            : $user->visits()->whereDate('created_at', Carbon::now()->toDateString())->count();

        $calls['flag'] = ($calls['count'] <= 20) ? config('constants.time_log.flag.0')
            : (($calls['count'] >= 21 && $calls['count'] <= 50)
                ? config('constants.time_log.flag.1') : config('constants.time_log.flag.2'));
        $calls['flag_text'] = config('constants.time_log.flag_text.' .  $calls['flag']);

        $calls['is_early_out'] = (Carbon::now()->toTimeString() < '20:00:00')
            ? config('constants.time_log.is_early_out.1') : config('constants.time_log.is_early_out.0');
        $calls['is_early_out_text'] = config('constants.time_log.is_early_out_text.' . $calls['is_early_out']);

        $calls['date'] = Carbon::now()->toDateString();
        return $calls;
    }

    /**
     * myTimeLog
     *
     * @param  mixed $request
     * @return void
     */
    public function myTimeLog(Request $request)
    {
        $query = Auth::guard('api')->user()->calls();
        $query = $this->customSSPF(Call::class, $query, $request);
        return new CommonCollection(CallResource::collection($query), CallResource::class);
    }

    /**
     * singleAdminTimeLog
     *
     * @param  mixed $admin
     * @param  mixed $request
     * @return void
     */
    public function singleAdminTimeLog(Admin $admin, Request $request)
    {
        $query = $admin->user->calls();
        $query = $this->customSSPF(Call::class, $query, $request);
        return new CommonCollection(CallResource::collection($query), CallResource::class);
    }

    /**
     * singleSubAdminTimeLog
     *
     * @param  mixed $sub_admin
     * @param  mixed $request
     * @return void
     */
    public function singleSubAdminTimeLog(SubAdmin $sub_admin, Request $request)
    {
        $query = $sub_admin->user->calls();
        $query = $this->customSSPF(Call::class, $query, $request);
        return new CommonCollection(CallResource::collection($query), CallResource::class);
    }

    /**
     * singleLeadManagerTimeLog
     *
     * @param  mixed $lead_manager
     * @param  mixed $request
     * @return void
     **/
    public function singleLeadManagerTimeLog(LeadManager $lead_manager, Request $request)
    {
        $query = $lead_manager->user->calls();
        $query = $this->customSSPF(Call::class, $query, $request);
        return new CommonCollection(CallResource::collection($query), CallResource::class);
    }

    /**
     * 
     
     * singleSalesAssociatesTimeLog
     *
     * @param  mixed $sales_associate
     * @param  mixed $request
     * @return void
     */
    public function singleSalesAssociatesTimeLog(SalesAssociate $sales_associate, Request $request)
    {
        $query = $sales_associate->user->calls();
        $query = $this->customSSPF(Call::class, $query, $request);
        return new CommonCollection(CallResource::collection($query), CallResource::class);
    }
}
