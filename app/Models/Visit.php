<?php

namespace App\Models;

use App\Traits\AuthRelationshipTrait;
use App\Traits\ConvertToStringTrait;
use App\Traits\SspfTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory,
        SspfTrait,
        ConvertToStringTrait,
        AuthRelationshipTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'id',
        'user_id',
        'name_of_visited_outlet',
        'name',
        'mobile_no',
        'email',
        'address',
        'area',
        'remark',
        'follow_up_number',
        'created_at',
        'updated_at','date',
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
        'name_of_visited_outlet' => 'string',
        'name' => 'string',
        'mobile_no' => 'string',
        'email' => 'string',
        'address' => 'string',
        'area' => 'string',
        'remark' => 'string',
        'follow_up_number' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
        'deleted_at' => 'string',
        'date'=>'string',
    ];

    public $sortable = [
        'id',
        'user_id',
        'name_of_visited_outlet',
        'name',
        'mobile_no',
        'email',
        'address',
        'area',
        'remark',
        'follow_up_number',
        'created_at',
        'updated_at',
        'deleted_at',
        'user.first_name',
        'user.last_name',
        'user.email'
    ];

    public function scopeApply($query, $request, $excel)
    {
        return $this->applySspf(self::class, $request, $excel);
    }

    // Many visits belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
