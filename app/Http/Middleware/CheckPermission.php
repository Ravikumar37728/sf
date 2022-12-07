<?php

namespace App\Http\Middleware;

use App\Traits\MessagesTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckPermission
{
    use MessagesTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $controllerMethod = class_basename($request->route()->getActionname()); // Get controllerclassname@method
        $controllerMethodArray = explode("@", $controllerMethod); // Separate it from '@' character

        $controller = $controllerMethodArray[0];
        $method = $controllerMethodArray[1];

        // Get module name from Controller class e.g. UsersAPIController -> users
        if (Str::contains($controller, 'APIController')) {
            $module = Str::lower(str_replace("APIController", "s", $controller));
        } else if (Str::contains($controller, 'sAPIController')) {
            $module = Str::lower(str_replace("sAPIController", "", $controller));
        } else if (Str::contains($controller, 'Controller')) {
            $module = Str::lower(str_replace("Controller", "s", $controller));
        } else if (Str::contains($controller, 'sController')) {
            $module = Str::lower(str_replace("Controller", "", $controller));
        } else {
            return $this->getMessage([], config('constants.messages.errors.something_wrong'), config('constants.validation_codes.unprocessable_entity'), false);
        }

        $permission = $method . '-' . $module;

        // dd($permission);

        $pre_permissions = [
            // Default Condition
            'login-logins', 'logout-logins', 'changePassword-logins',

            // Admin
            'store-admins',
            'update-admins',
            'show-admins',
            'index-admins',
            'destroy-admins',
            'updateStatus-admins',
            'updateRemark-admins',
            'deleteMultiple-admins',

            // Call Details
            'store-calldetails',
            'update-calldetails',
            'show-calldetails',
            'index-calldetails',
            'myCalls-calldetails',
            'adminCalls-calldetails',
            'subAdminCalls-calldetails',
            'calls-calldetails',
            'leadmnagercalls-calldetails',
            // Sub Admin
            'store-subadmins',
            'update-subadmins',
            'show-subadmins',
            'index-subadmins',
            'updateStatus-subadmins',
            'destroy-subadmins',
            'deleteMultiple-subadmins',
            'mySubAdmin-subadmins',
            'adminWiseSubadmin-subadmins',

            // Lead Manager
            'store-leadmanagers',
            'update-leadmanagers',
            'updateStatus-leadmanagers',
            'show-leadmanagers',
            'index-leadmanagers',
            'destroy-leadmanagers',
            'deleteMultiple-leadmanagers',
            'myLeadManager-leadmanagers',
            'adminWiseLM-leadmanagers',
            'subadminWiseLM-leadmanagers',
            'storesalesassociate-leadmanagers',

            // Sales Associates
            'store-salesassociates',
            'update-salesassociates',
            'updateStatus-salesassociates',
            'show-salesassociates',
            'index-salesassociates',
            'destroy-salesassociates',
            'deleteMultiple-salesassociates',
            'mySalesAssociates-salesassociates',
            'adminWiseSA-salesassociates',
            'subAdminWiseSA-salesassociates',
            'leadManagerWiseSA-salesassociates',

            // Visit
            'store-visits',
            'update-visits',
            'show-visits',
            'index-visits',
            'myVisits-visits',
            'saWiseVisits-visits',
            'visits-visits',

            // Time Logs
            'inTimeLog-timelogs',
            'checkOutTime-timelogs',
            'outTimeLog-timelogs',
            'myTimeLog-timelogs',
            'adminTimeLog-timelogs',
            'adminTeam-timelogs',
            'singleAdminTimeLog-timelogs',
            'singleSubAdminTimeLog-timelogs',
            'singleLeadManagerTimeLog-timelogs',
            'singleSalesAssociatesTimeLog-timelogs',

            // Excel Import
            'importExcelVisit-visits',
            'importExcelCall-calldetails',
        ];

        // pre defined permissions || user defined permissions || auth and without auth batch requests
        if (
            in_array($permission, $pre_permissions)
            || Auth::guard('api')->user()->tokenCan($permission)
        ) {
            return $next($request);
        } else {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.forbidden'), false);
        }

        return $next($request);
    }
}
