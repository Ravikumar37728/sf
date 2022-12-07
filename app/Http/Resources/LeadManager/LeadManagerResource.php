<?php

namespace App\Http\Resources\LeadManager;

use Illuminate\Http\Resources\Json\JsonResource;

class LeadManagerResource extends JsonResource
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
            'type' => $this->type,
            'type_text' => config('constants.lead_manager.type_text.' . $this->type),
            'sub_admin' => (is_null($this->sub_admin)) ? (object) NULL :
                $this->sub_admin->user->only(
                    'first_name',
                    'last_name',
                    'full_name',
                    'email',
                    'mobile_no',
                    'profile_photo',
                    'profile_photo_thumb'
                ),
            'admin' => (is_null($this->admin)) ? (object) NULL :
                $this->admin->user->only(
                    'first_name',
                    'last_name',
                    'full_name',
                    'email',
                    'mobile_no',
                    'profile_photo',
                    'profile_photo_thumb'
                ),
            'base_location' => $this->city->only('id', 'name'),

        ];
    }
}
