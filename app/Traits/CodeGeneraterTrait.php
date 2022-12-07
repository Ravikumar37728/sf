<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

trait CodeGeneraterTrait
{
    public function generate_otp($model, $column)
    {
        $otp = mt_rand(100000, 999999);
        return $this->checkExists($model, $column, $otp, false, NULL) ? $this->generate_otp($model, $column) : $otp;
    }

    public function generate_slug($model, $column, $value, $id = NULL, $is_soft_delete = false)
    {
        if (!is_null($value)) {
            $slug = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(" ", "-", $value)));
            $i = 0;
            while ($this->checkExists($model, $column, $slug, $is_soft_delete, $id)) {
                $i++;
                return $slug . '-' . $i;
            }
            return $slug;
        } else {
            return $value; // NULL
        }
    }

    public function generate_code($model, $column, $amount)
    {
        $random_str = substr(str_shuffle(str_repeat($x = 'ABCDEFGHIJKLMNPQRSTUVWXYZ', ceil(1 / strlen($x)))), 1, 1);
        $string = substr(Auth::guard('api')->user()->first_name, 0, 1) . substr(Auth::guard('api')->user()->last_name, 0, 1); // Bony Ariwala => BA
        $code = $string . mt_rand(1, 9) . $random_str . $amount;
        return $this->checkExists($model, $column, $code, false, NULL) ? $this->generate_code($model, $column, $amount) : $code;
    }

    public function generate_referral_code($user_name)
    {
        $characters = 'ABCDEFGHIJKLMNPQRSTUVWXYZ';
        $randstring = $characters[rand(0, strlen($characters) - 1)];
        $referral_code = strtoupper(substr($user_name, 0, 2)) . rand(1, 9) . $randstring . '200';
        return $this->checkExists(User::class, 'referral_code', $referral_code, false, NULL) ? $this->generate_referral_code($user_name) : $referral_code;
    }

    public function checkExists($model, $column, $value, $is_soft_delete, $id = NULL)
    {
        if ($is_soft_delete) {
            return (is_null($id))
                ? $model::where($column, $value)->exists()
                : $model::where($column, $value)
                ->where('id', '!=', $id)
                ->where('deleted_at', NULL)
                ->exists();
        } else {
            return (is_null($id)) ? $model::where($column, $value)->exists() : $model::where($column, $value)->where('id', '!=', $id)->exists();
        }
    }

    // For developer use only
    public function generate_query($query)
    {
        return Str::replaceArray('?', $query->getBindings(), $query->toSql());
    }

    public function checkSubscription($model, $column, $value)
    {
        return $model::where($column, $value)
            ->where([
                'status' => config('constants.subscriptions.status.1'),
                'is_expired' => config('constants.subscriptions.is_expired.0')
            ])->exists();
    }
}
