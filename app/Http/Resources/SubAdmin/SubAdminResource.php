<?php

namespace App\Http\Resources\SubAdmin;

use Illuminate\Http\Resources\Json\JsonResource;

class SubAdminResource extends JsonResource
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
            'user' => $this->user->only(
                'first_name',
                'last_name',
                'full_name',
                'email',
                'mobile_no',
                'profile_photo',
                'profile_photo_thumb',
                'status',
                'status_text',
                'user_type',
                'user_type_text'
            ),
            'admin' => $this->admin->user->only('first_name', 'last_name', 'full_name', 'profile_photo', 'profile_photo_thumb'),
            'city_assigned' => $this->city->only('id', 'name')
        ];
    }
}
