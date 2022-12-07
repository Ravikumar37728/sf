<?php

namespace App\Models;

use App\Traits\ConvertToStringTrait;
use App\Traits\SspfTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubAdmin extends Model
{
    use HasFactory,
        SspfTrait,
        SoftDeletes,
        ConvertToStringTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'id',
        'user_id',
        'admin_id',
        'city_assigned',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public $cast = [
        'id' => 'string',
        'user_id' => 'string',
        'admin_id' => 'string',
        'city_assigned' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
        'deleted_at' => 'string'
    ];

    public $sortable = [
        'id',
        'user_id',
        'admin_id',
        'city_assigned',
        'created_at',
        'updated_at',
        'deleted_at',
        'user.first_name',
        'user.last_name'
    ];

    public function scopeApply($query, $request, $excel)
    {
        return $this->applySspf(self::class, $request, $excel);
    }

    // One sub-admin belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Many sub-admin belongs to one admin
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // One sub-admin has many lead managers
    public function lead_managers()
    {
        return $this->hasMany(LeadManager::class);
    }

    // One sub-admin has many sales associates
    public function sales_associates()
    {
        return $this->hasMany(SalesAssociate::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_assigned')->withDefault(function () {
            new City();
        });
    }

    // Sales Associates of Lead Manager
    public function sales_associates_of_lead_manager()
    {
        return $this->hasManyThrough(SalesAssociate::class, LeadManager::class, 'sub_admin_id', 'lead_manager_id', 'id', 'id');
    }
}
