<?php

namespace App\Imports;

use App\Models\Visit;
use App\Traits\MessagesTrait;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class VisitsImport implements WithHeadingRow, ToCollection, WithStartRow, WithValidation
{
    use MessagesTrait, Importable;

    private $errors = [];
    private $rows = 0;

    public function startRow(): int
    {
        return 2;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function rules(): array
    {
        return [
            'name_of_visited_outlet' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'mobile_no' => ['required', 'regex:/^(\\+\\d{1,3}( )?)?((\\(\\d{3}\\))|\\d{3})[- .]?\\d{3}[- .]?\\d{4}$/'],
            'address' => 'required',
            'area' => 'required',
            'remark' => 'required',
            'follow_up_number' => 'required'
        ];
    }

    public function validationMessages()
    {
        return [
            'name_of_visited_outlet.required' => trans('The name of visited outlet is required'),
            'name.required' => trans('The name is required'),
            'email.required' => trans('The email is required'),
            'mobile_no.required' => trans('The mobile no is required'),
            'address.required' => trans('The address is required'),
            'area.required' => trans('The area is required'),
            'remark.required' => trans('The remark is required'),
            'follow_up_number.required' => trans('The follow up number is required'),
            'follow_up_number.unique_with' => trans('The follow up number is unique'),
        ];
    }

    public function validateBulk($collection)
    {
        $keys = ['name_of_visited_outlet', 'name', 'email', 'mobile_no', 'address', 'area', 'remark', 'follow_up_number'];
        $i = 1;
        $uniques = [];
        foreach ($collection as $col) {
            if (count(array_diff_key(array_flip($keys), $col->toArray())) > 0) {
                $this->errors[] = config('constants.messages.errors.imports.invalid_file_format');
                break;
            }
            $i++;

            $uniques[$i]['email'] = $col['email'];
            $uniques[$i]['follow_up_number'] = $col['follow_up_number'];
            $check_unique_email = array_unique($uniques, SORT_REGULAR);
            if (sizeof($check_unique_email) != sizeof($uniques)) {
                $this->errors[] = 'Duplicate email on row ' . $i . '| Email is : ' . $uniques[$i]['email'] . ' | Follow up number is : ' . $uniques[$i]['follow_up_number'];
                break;
            }

            if (Visit::where([
                'email' => $col['email'], 'follow_up_number' => $col['follow_up_number'], 'user_id' => Auth::guard('api')->id()
            ])->exists()) {
                $this->errors[] = config('constants.messages.errors.email_already_taken') . ' on row ' . $i;
            }

            if (Visit::where([
                'mobile_no' => $col['mobile_no'], 'follow_up_number' => $col['follow_up_number'], 'user_id' => Auth::guard('api')->id()
            ])->exists()) {
                $this->errors[] = config('constants.messages.errors.mobile_already_taken') . ' on row ' . $i;
            }

            $validator = Validator::make($col->toArray(), $this->rules(), $this->validationMessages());
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $messages) {
                    foreach ($messages as $error) {
                        $this->errors[] = $error . ' on row ' . $i;
                    }
                }
            }
        }
        return $this->getErrors();
    }

    public function collection(Collection $collection)
    {
        $errors = $this->validateBulk($collection);
        if (!empty($errors)) {
            return;
        } else {
            foreach ($collection as $col) {
                $visit = Visit::create([
                    'name_of_visited_outlet' => (string)$col['name_of_visited_outlet'],
                    'name' => (string)$col['name'],
                    'email' => (string)$col['email'],
                    'mobile_no' => (string)$col['mobile_no'],
                    'address' => (string)$col['address'],
                    'area' => (string)$col['area'],
                    'remark' => (string)$col['remark'],
                    'follow_up_number' => (string)$col['follow_up_number'],
                    'user_id' => Auth::guard('api')->id()
                ]);
                $this->rows++;
            }
        }
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }
}
