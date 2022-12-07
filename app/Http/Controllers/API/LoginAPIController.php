<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\LoginResource;
use App\Models\User;
use App\Traits\AuthRelationshipTrait;
use App\Traits\ConvertToStringTrait;
use App\Traits\MessagesTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginAPIController extends Controller
{
    use MessagesTrait,
        ConvertToStringTrait,
        AuthRelationshipTrait;

    public function login(LoginRequest $request)
    {
        $user = ($request->route()->getName() == "super.admin.login")
            ? User::where('mobile_no', '=', $request->mobile_no)->where('user_type', '=', config('constants.users.user_type.0'))->first()
            : User::where('mobile_no', '=', $request->mobile_no)->where('user_type', '!=', config('constants.users.user_type.0'))->first();

        if (is_null($user)) {
            return $this->getMessage([], config('constants.messages.errors.not_found'), config('constants.validation_codes.not_found'), false);
        }

        if ($user->status != config('constants.status.1')) {
            return $this->getMessage([], config('constants.messages.errors.account_not_verified'), config('constants.validation_codes.not_verified'), false);
        }

        if (!Hash::check($request->password, $user->password)) {
            return $this->getMessage([], config('constants.messages.errors.invalid_password'), config('constants.validation_codes.unprocessable_entity'), false);
        }

        if (Auth::attempt($request->only('mobile_no', 'password'))) {
            $authUser = Auth::user();
            $tokenResult = $authUser->createToken('Personal Access Token');
            $token = $tokenResult->token;
            $authUser->authorization = $tokenResult->accessToken;
            if ($request->remember_me) {
                $token->expires_at = Carbon::now()->addWeeks(1);
            }
            $token->save();
            return (new LoginResource($authUser))->additional([
                'status' => true,
                'status_code' => config('constants.validation_codes.ok'),
                'message' => config('constants.messages.success.login')
            ]);
        }
        return $this->getMessage([], config('constants.messages.errors.something_wrong'), config('constants.validation_codes.unprocessable_entity'), false);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = User::where(['id' => Auth::guard('api')->id(), 'status' => config('constants.status.1')])->first();
        if (is_null($user)) {
            return $this->getMessage([], config('constants.messages.errors.not_found'), config('constants.validation_codes.not_found'), false);
        }
        if (!Hash::check($request['old_password'], $user->password)) {
            return $this->getMessage([], config("constants.messages.errors.change_password.invalid_old_password"), config('constants.validation_codes.unprocessable_entity'), false);
        }
        return ($user->update(['password' => $request['new_password']]))
            ? $this->getMessage([], config('constants.messages.success.password_changed'), config('constants.validation_codes.ok'), true)
            : $this->getMessage([], config("constants.messages.errors.something_wrong"), config('constants.validation_codes.unprocessable_entity'), false);
    }

    public function logout()
    {
        $accessToken = Auth::guard('api')->user()->token();
        $accessToken->revoke();
        return $this->getMessage([], config("constants.messages.success.logout"), config('constants.validation_codes.ok'), true);
    }
}
