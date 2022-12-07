<?php

namespace App\Http\Resources\TimeLog;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user' => $this->user->only('first_name', 'last_name', 'full_name', 'profile_photo', 'profile_photo_thumb'),
            'in_time' => $this->in_time,
            'out_time' => $this->out_time,
            'remark' => $this->remark,
            'calls' => (is_null($this->user->today_call)) ? (object) $this->calls :
                $this->user->today_call->only('count', 'is_early_out', 'is_early_out_text', 'flag', 'flag_text', 'date'),
            'date' => (Carbon::parse($this->created_at))->toDateString()
        ];
    }
}
