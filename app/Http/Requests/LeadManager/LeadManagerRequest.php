<?php

namespace App\Http\Requests\LeadManager;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LeadManagerRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => false,
                'status_code' => config('constants.validation_codes.unprocessable_entity'),
                'message' => $validator->errors()->all()[0]
            ], config('constants.validation_codes.unprocessable_entity'))
        );
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = (isset($this->lead_manager->id) && $this->lead_manager->id != 0) ? $this->lead_manager->id : 0;

        return ($id == 0) ?
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'mobile_no' => ['required', 'unique:users,mobile_no,NULL,deleted_at', 'regex:/^(\\+\\d{1,3}( )?)?((\\(\\d{3}\\))|\\d{3})[- .]?\\d{3}[- .]?\\d{4}$/'],
                'password' => 'required',
                'user_type' => 'required|in:3',
                'type' => 'required|in:0,1',
                'sub_admin_id' => 'required_if:type,1|prohibited_if:type,0|exists:sub_admins,id,deleted_at,NULL',
                'base_location' => 'required',
            ] : [
                'first_name' => 'nullable',
                'last_name' => 'nullable',
                'email' => 'nullable|email',
                'photo' => 'nullable|mimes:jpeg,png,jpg'
            ];
    }
}
