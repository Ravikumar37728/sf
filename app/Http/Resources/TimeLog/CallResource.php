<?php

namespace App\Http\Resources\TimeLog;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CallResource extends JsonResource
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
            'user' => $this->user->only('first_name', 'last_name', 'full_name', 'profile_photo', 'profile_photo_thumb'),
            'date' => $this->date,
            'call_count' => $this->count,
            'time_log' => $this->user->time_logs()->whereDate('created_at', $this->date)->first()->only('in_time', 'out_time', 'remark'),
            'is_early_out' => $this->is_early_out,
            'is_early_out_text' => $this->is_early_out_text,
            'flag' => $this->flag,
            'flag_text' => $this->flag_text,
        ];
    }
}
