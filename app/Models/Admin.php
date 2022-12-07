<?php

namespace App\Models;

use App\Traits\ConvertToStringTrait;
use App\Traits\MessagesTrait;
use App\Traits\SspfTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Model
{
    use HasFactory,
        MessagesTrait,
        SspfTrait,
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
        'remark',
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
        'remark' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
        'deleted_at' => 'string',
    ];

    public $sortable = [
        'id',
        'user_id',
        'remark',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function scopeApply($query, $request, $excel)
    {
        return $this->applySspf(self::class, $request, $excel);
    }

    // One admin belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // One admin has many sub admin
    public function sub_admins()
    {
        return $this->hasMany(SubAdmin::class);
    }

    // One admin has many lead managers
    public function lead_managers_of_sub_admin()
    {
        return $this->hasManyThrough(LeadManager::class, SubAdmin::class, 'admin_id', 'sub_admin_id', 'id', 'id');
    }

    // One admin has many lead managers
    public function lead_managers()
    {
        return $this->hasMany(LeadManager::class);
    }

    // One admin has many sales associates
    public function sales_associates()
    {
        return $this->hasMany(SalesAssociate::class);
    }

    // Sales Associates of Sub Admin
    public function sales_associates_of_sub_admin()
    {
        return $this->hasManyThrough(SalesAssociate::class, SubAdmin::class, 'admin_id', 'sub_admin_id', 'id', 'id');
    }

    // Sales Associates of Lead Manager
    public function sales_associates_of_lead_manager()
    {
        return $this->hasManyThrough(SalesAssociate::class, LeadManager::class, 'admin_id', 'lead_manager_id', 'id', 'id');
    }
}
