<?php

namespace App\Models;

use App\Traits\ConvertToStringTrait;
use App\Traits\SspfTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    use HasFactory,
        ConvertToStringTrait,
        SspfTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'id',
        'user_id',
        'date',
        'count',
        'flag',
        'created_at',
        'updated_at'
    ];

    public $dates = [
        'created_at',
        'updated_at',
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
        'date' => 'string',
        'count' => 'string',
        'flag' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];

    public $sortable = [
        'id',
        'user_id',
        'date',
        'count',
        'flag',
        'created_at',
        'updated_at'
    ];

    // Many calls belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getIsEarlyOutAttribute()
    {
        return (Carbon::now()->toTimeString() < '20:00:00')
            ? config('constants.time_log.is_early_out.1') : config('constants.time_log.is_early_out.0');
    }

    public function getIsEarlyOutTextAttribute()
    {
        return config('constants.time_log.is_early_out_text.' . $this->is_early_out);
    }

    public function getFlagTextAttribute()
    {
        return config('constants.time_log.flag_text.' . $this->flag);
    }
}
