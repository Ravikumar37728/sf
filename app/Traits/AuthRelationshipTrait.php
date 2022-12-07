<?php

namespace App\Traits;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait AuthRelationshipTrait
{
    public function canModifyModule($relationship, $id, $method)
    {
        $authUser = User::find(Auth::guard('api')->id());
        $admin_type = config('constants.users.user_type.0');
        $system_id = config('constants.system_user_id');

        switch ($relationship) {

            case 'admin':
                switch ($method) {
                    case 'store':
                    case 'delete':
                    case 'update-status':
                    case 'update-remark':
                        return ($system_id == $authUser->id) ? true : false;
                    case 'update':
                        return ($authUser->id == $id) ? true : false;
                        break;
                }
                break;

            case 'call-detail':
                switch ($method) {
                    case 'update':
                        return ($authUser->id == $id) ? true : false;
                        break;
                }
                break;

            case 'sub-admin':
                switch ($method) {
                    case 'store':
                    case 'delete':
                    case 'update-status':
                        return ($system_id == $authUser->id) ? true : false;
                    case 'update':
                        return ($authUser->id == $id) ? true : false;
                        break;
                }
                break;

            case 'lead-manager':
                switch ($method) {
                    case 'delete':
                    case 'update-status':
                    case 'update':
                        return ($authUser->id == $id) ? true : false;
                        break;
                }
                break;

            case 'sales-associate':
                switch ($method) {
                    case 'delete':
                    case 'update-status':
                    case 'update':
                        return ($authUser->id == $id) ? true : false;
                        break;
                }
                break;

            default:
                return false;
        }
    }
}
