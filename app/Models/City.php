<?php

namespace App\Models;

use App\Http\Resources\CityResource;
use App\Traits\ConvertToStringTrait;
use App\Traits\MessagesTrait;
use App\Traits\SspfTrait;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use SspfTrait,
        ConvertToStringTrait,
        MessagesTrait;

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            // ... code here
        });

        self::created(function ($model) {
            // ... code here
        });

        self::updating(function ($model) {
            // ... code here
        });

        self::updated(function ($model) {
            // ... code here
        });

        self::deleting(function ($model) {
            // ... code here
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        //
    ];

    protected $appends = [
        //
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'state_id',
        'state_code',
        'country_id',
        'country_code',
        'latitude',
        'longitude',
        'wikiDataId'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    public $dates = [
        //
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'string',
        'name' => 'string',
        'state_id' => 'string',
        'state_code' => 'string',
        'country_id' => 'string',
        'country_code' => 'string',
        'latitude' => 'string',
        'longitude' => 'string',
        'wikiDataId' => 'string'
    ];

    public $sortable = [
        'id',
        'name',
        'state_id',
        'state_code',
        'country_id',
        'country_code',
        'latitude',
        'longitude',
        'wikiDataId',
        'state.name',
        'country.name'
    ];

    public $light = [
        'id',
        'name',
    ];

    public function scopeApply($query, $request, $excel)
    {
        return $this->applySspf(self::class, $request, $excel);
    }

    /**
     * Get the Users for the City.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the State for the City.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the State for the City.
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function scopeCitiesOfState($query, $request)
    {
        $model = new City();
        $query = ($request->get('is_light', false))
            ? City::select($model->light)->where('state_id', $request['state_id'])->get()
            : City::where('state_id', $request['state_id'])->get();
        return ($query->count() > 0)
            ? CityResource::collection($query)->additional(['status' => true, 'status_code' => config('constants.validation_codes.ok'), 'message' => config('constants.messages.success.listed')])
            : $this->getMessage([], config('constants.messages.errors.not_found'), config('constants.validation_codes.not_found'), false);
    }
}
