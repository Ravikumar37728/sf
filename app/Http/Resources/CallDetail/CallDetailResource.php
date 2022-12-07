<?php

namespace App\Http\Resources\CallDetail;

use Illuminate\Http\Resources\Json\JsonResource;

class CallDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // dd($this->user);
        return [
            'id' => $this->id,
            'user' => $this->user->only('first_name', 'last_name', 'full_name', 'profile_photo', 'profile_photo_thumb'),
            'name' => $this->name,
            'email' => $this->email,
            'mobile_no' => $this->mobile_no,
            'source' => $this->source,
            'reason' => $this->reason,
            'reason_text' => config('constants.call_detail.reason_text.' . $this->reason),
            'follow_up_number' => $this->follow_up_number,
            'is_appointed' => $this->is_appointed,
            'is_appointed_text' => config('constants.call_detail.is_appointed_text.' . $this->is_appointed),
            'remark' => $this->remark,
            'date' => $this->created_at,
        ];
    }
}
