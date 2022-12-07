<?php

namespace App\Models;

use App\Traits\ConvertToStringTrait;
use App\Traits\MessagesTrait;
use App\Traits\SspfTrait;
use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadManager extends Model
{
    // public $table = "lead_managers";

    use HasFactory,
        UploadTrait,
        SspfTrait,
        MessagesTrait,
        ConvertToStringTrait,
        SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'id',
        'user_id',
        'sub_admin_id',
        'admin_id',
        'type',
        'base_location',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public $cast = [
        'id' => 'string',
        'user_id' => 'string',
        'sub_admin_id' => 'string',
        'admin_id' => 'string',
        'type' => 'string',
        'base_location' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
        'deleted_at' => 'string',
    ];

    public $sortable = [
        'id',
        'user_id',
        'sub_admin_id',
        'admin_id',
        'type',
        'base_location',
        'created_at',
        'updated_at',
        'deleted_at',
        'user.first_name',
        'user.last_name',
        'city.name'
    ];

    public function scopeApply($query, $request, $excel)
    {
        return $this->applySspf(self::class, $request, $excel);
    }

    // One lead manager belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Many lead managers belongs to one sub admin
    public function sub_admin()
    {
        return $this->belongsTo(SubAdmin::class);
    }

    // Many lead managers belongs to one admin
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // One lead manager has many sales associates
    public function sales_associates()
    {
        return $this->hasMany(SalesAssociate::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'base_location')->withDefault(function () {
            new City();
        });
    }
}
