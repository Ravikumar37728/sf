<?php

namespace App\Http\Resources\Visit;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitResource extends JsonResource
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
            'name_of_visited_outlet' => $this->name_of_visited_outlet,
            'name' => $this->name,
            'mobile_no' => $this->mobile_no,
            'email' => $this->email,
            'address' => $this->address,
            'area' => $this->area,
            'remark' => $this->remark,
            'follow_up_number' => $this->follow_up_number,
            'time_log' => $this->user->time_logs()->whereDate('created_at', Carbon::parse($this->created_at)->toDateString())->first()->only('in_time', 'out_time', 'remark'),
            'date' => $this->created_at
        ];
    }
}
