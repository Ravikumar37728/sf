<?php

namespace App\Http\Requests\Visit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VisitRequest extends FormRequest
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
        $id = (isset($this->visit->id)) ? $this->visit->id : 0;

        return ($id == 0)
            ? [
                'name_of_visited_outlet' => 'required',
                'name' => 'required',
                'email' => 'required|email',
                'mobile_no' => ['required', 'regex:/^(\\+\\d{1,3}( )?)?((\\(\\d{3}\\))|\\d{3})[- .]?\\d{3}[- .]?\\d{4}$/'],
                'address' => 'required',
                'area' => 'required',
                'remark' => 'required',
                'follow_up_number' => 'required'
            ] : [
                'name_of_visited_outlet' => 'required',
                'name' => 'required',
                'email' => 'prohibited',
                'mobile_no' => 'prohibited',
                'address' => 'required',
                'area' => 'required',
                'remark' => 'required',
                'follow_up_number' => 'prohibited'
            ];
    }
}
