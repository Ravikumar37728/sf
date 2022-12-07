<?php

namespace App\Models;

use App\Traits\ConvertToStringTrait;
use App\Traits\MessagesTrait;
use App\Traits\SspfTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeLog extends Model
{
    use HasFactory,
        SspfTrait,
        MessagesTrait,
        ConvertToStringTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'id',
        'user_id',
        'in_time',
        'out_time',
        'remark',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'in_time' => 'string',
        'out_time' => 'string',
        'remark' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string'
    ];

    public $sortable = [
        'id',
        'user_id',
        'in_time',
        'out_time',
        'remark',
        'created_at',
        'updated_at'
    ];

    // Many time logs belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
