<?php

namespace App\Http\Resources\SalesAssociate;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesAssociateResource extends JsonResource
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
            'type_text' => config('constants.sales_associate.type_text.' . $this->type),
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
            'lead_manager' => (is_null($this->lead_manager)) ? (object) NULL :
                $this->lead_manager->user->only(
                    'first_name',
                    'last_name',
                    'full_name',
                    'email',
                    'mobile_no',
                    'profile_photo',
                    'profile_photo_thumb'
                ),
        ];
    }
}
