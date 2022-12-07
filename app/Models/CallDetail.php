<?php

namespace App\Models;

use App\Traits\AuthRelationshipTrait;
use App\Traits\ConvertToStringTrait;
use App\Traits\MessagesTrait;
use App\Traits\SspfTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallDetail extends Model
{
    use HasFactory,
        AuthRelationshipTrait,
        SspfTrait,
        MessagesTrait,
        ConvertToStringTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'user_id',
        'name',
        'email',
        'mobile_no',
        'source',
        'reason',
        'follow_up_number',
        'is_appointed',
        'remark',
        'created_at',
        'updated_at',
        'deleted_at',
        'date'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'name' => 'string',
        'email' => 'string',
        'mobile_no' => 'string',
        'source' => 'string',
        'reason' => 'string',
        'follow_up_number' => 'string',
        'is_appointed' => 'string',
        'remark' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
        'deleted_at' => 'string',
        'date'=>'string',
    ];

    public $sortable = [
        'id',
        'user_id',
        'name',
        'email',
        'mobile_no',
        'source',
        'reason',
        'follow_up_number',
        'is_appointed',
        'remark',
        'created_at',
        'updated_at',
        'deleted_at',
        'user.first_name',
        'user.last_name',
        'date',
    ];

    public function scopeApply($query, $request, $excel)
    {
        return $this->applySspf(self::class, $request, $excel);
    }

    // Many Call details belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
