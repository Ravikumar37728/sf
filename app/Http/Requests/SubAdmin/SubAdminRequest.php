<?php

namespace App\Http\Requests\SubAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class SubAdminRequest extends FormRequest
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
    public function rules(Request $request)
    {
        $id = (isset($this->sub_admin->id) && $this->sub_admin->id != 0) ? $this->sub_admin->id : 0;

        return ($id == 0)
            ? [ // Store
                'first_name' => 'required',
                'last_name' => 'required',
                'mobile_no' => ['required', 'unique:users,mobile_no,NULL,deleted_at', 'regex:/^(\\+\\d{1,3}( )?)?((\\(\\d{3}\\))|\\d{3})[- .]?\\d{3}[- .]?\\d{4}$/'],
                'password' => 'required',
                'admin_id' => 'required|exists:admins,id,deleted_at,NULL',
                'user_type' => 'required|in:2',
                'city_assigned' => 'required',
            ]
            : [ // Update
                'first_name' => 'nullable',
                'last_name' => 'nullable',
                'email' => 'nullable|email',
                'photo' => 'nullable|mimes:jpeg,png,jpg'
            ];
    }
}
