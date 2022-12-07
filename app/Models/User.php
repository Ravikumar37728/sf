<?php

namespace App\Models;

use App\Traits\ConvertToStringTrait;
use App\Traits\SspfTrait;
use App\Traits\UploadTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use
        HasFactory,
        Notifiable,
        HasApiTokens,
        SspfTrait,
        UploadTrait,
        ConvertToStringTrait,
        SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'user_type',
        'first_name',
        'last_name',
        'email',
        'password',
        'mobile_no',
        'profile_photo',
        'profile_photo_thumb',
        'gender',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'string',
        'user_type' => 'string',
        'first_name' => 'string',
        'last_name' => 'string',
        'email' => 'string',
        'password' => 'string',
        'mobile_no' => 'string',
        'profile_photo' => 'string',
        'profile_photo_thumb' => 'string',
        'gender' => 'string',
        'remember_token' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
        'deleted_at' => 'string',
    ];

    protected $append = ['full_name'];

    public function scopeApply($query, $request, $excel)
    {
        return $this->applySspf(self::class, $request, $excel);
    }

    public function getProfilePhotoAttribute($value)
    {
        return (is_null($value)) ? "" : (($this->is_file_exists($value)) ? url(config('constants.image.dir_path') . $value) : "");
    }

    public function getProfilePhotoThumbAttribute($value)
    {
        return (is_null($value)) ? "" : (($this->is_file_exists($value)) ? url(config('constants.image.dir_path') . $value) : "");
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getStatusTextAttribute()
    {
        return config('constants.status_text.' . $this->status);
    }

    public function getUserTypeTextAttribute()
    {
        return config('constants.users.user_type_text.' . $this->user_type);
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    // One user has one admin
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    // One user has many call-details
    public function call_details()
    {
        return $this->hasMany(CallDetail::class);
    }

    // One user has many visits
    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    // One user has one sub-admin
    public function sub_admin()
    {
        return $this->hasOne(SubAdmin::class);
    }

    // One user has one lead manager
    public function lead_manager()
    {
        return $this->hasOne(LeadManager::class);
    }

    // One user has one sales associate
    public function sales_associate()
    {
        return $this->hasOne(SalesAssociate::class);
    }

    // One user has many time logs
    public function time_logs()
    {
        return $this->hasMany(TimeLog::class);
    }

    public function today_time_log()
    {
        return $this->hasOne(TimeLog::class)->whereDate('created_at', Carbon::now()->toDateString());
    }

    // One user has many calls
    public function calls()
    {
        return $this->hasMany(Call::class);
    }

    // get today calls
    public function today_call()
    {
        return $this->hasOne(Call::class, 'user_id')->whereDate('date', Carbon::now()->toDateString());
    }
}
