<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // dd($this->id);
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'mobile_no' => $this->mobile_no,
            'profile_photo' => $this->profile_photo,
            'profile_photo_thumb' => $this->profile_photo_thumb,
            'user_type' => $this->user_type,
            'user_type_text' =>  (string) config('constants.users.user_type_text.' . $this->user_type),
            'status' => $this->status,
            'status_text' =>  (string) config('constants.status_text.' . $this->status),
            'admin_id' => ($this->user_type == 1) ? $this->admin->id : "",
            'sub_admin_id' => ($this->user_type == 2) ? $this->sub_admin->id : "",
            'lead_manager_id' => ($this->user_type == 3) ? $this->lead_manager->id : "",
            'sales_associate_id' => ($this->user_type == 4) ? $this->sales_associate->id : "",
            'authorization' => $this->authorization
        ];
    }
}
