<?php

namespace App\Http\Requests\CallDetail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class CallDetailRequest extends FormRequest
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
        $id = (isset($this->call_detail->id)) ? $this->call_detail->id : 0;
        return ($id == 0)
            ? [
                'name' => 'required',
                'email' => 'required|email',
                'mobile_no' => ['required', 'regex:/^(\\+\\d{1,3}( )?)?((\\(\\d{3}\\))|\\d{3})[- .]?\\d{3}[- .]?\\d{4}$/'],
                'source' => 'required',
                'reason' => 'required',
                'follow_up_number' => 'required|integer',
                'is_appointed' => 'required|in:0,1',
                'remark' => 'required'
            ]
            : [
                'name' => 'required',
                'email' => 'prohibited',
                'mobile_no' => 'prohibited',
                'source' => 'required',
                'reason' => 'required',
                'follow_up_number' => 'prohibited',
                'is_appointed' => 'required|in:0,1',
                'remark' => 'required'
            ];
    }
}
