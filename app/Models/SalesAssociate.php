<?php

namespace App\Models;

use App\Traits\AuthRelationshipTrait;
use App\Traits\ConvertToStringTrait;
use App\Traits\SspfTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesAssociate extends Model
{
    use HasFactory,
        ConvertToStringTrait,
        AuthRelationshipTrait,
        SspfTrait,
        SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'id',
        'user_id',
        'type',
        'admin_id',
        'sub_admin_id',
        'lead_manager_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    public $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'type' => 'string',
        'admin_id' => 'string',
        'sub_admin_id' => 'string',
        'lead_manager_id' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
        'deleted_at' => 'string',
    ];

    public $sortable = [
        'id',
        'user_id',
        'type',
        'admin_id',
        'sub_admin_id',
        'lead_manager_id',
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

    // One sales associate belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Many sales associate belongs to one admin
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // Many sales associates belongs to one sub-admin
    public function sub_admin()
    {
        return $this->belongsTo(SubAdmin::class);
    }

    // Many sales associates belongs to one lead-manager
    public function lead_manager()
    {
        return $this->belongsTo(LeadManager::class);
    }
}
