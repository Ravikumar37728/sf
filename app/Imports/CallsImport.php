<?php

namespace App\Imports;

use App\Models\CallDetail;
use App\Traits\MessagesTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CallsImport implements ToCollection, WithValidation, WithHeadingRow, WithStartRow, WithCustomCsvSettings
{
    use Importable, MessagesTrait;

    private $errors = [];
    private $rows = 0;

    public function getCsvSettings(): array
    {
        return [
            'input_encoding' => 'ISO-8859-1'
        ];
    }

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
            'name' => 'required',
            'email' => 'required|email',
            'mobile_no' => ['required', 'regex:/^(\\+\\d{1,3}( )?)?((\\(\\d{3}\\))|\\d{3})[- .]?\\d{3}[- .]?\\d{4}$/'],
            'source' => 'required',
            'reason' => 'required|in:MF,FR,LM,CO,SU',
            'follow_up_number' => 'required|integer',
            'is_appointed' => 'required|in:0,1',
            'remark' => 'required'
        ];
    }

    public function validationMessages()
    {
        return [
            'name.required' => trans('The name is required'),
            'email.required' => trans('The email is required'),
            'mobile_no.required' => trans('The mobile no is required'),
            'source.required' => trans('The source is required'),
            'reason.required' => trans('The reason is required'),
            'follow_up_number.required' => trans('The follow up number is required'),
            'is_appointed.required' => trans('The is appointed is required'),
            'remark.required' => trans('The remark is required'),
        ];
    }

    public function validateBulk($collection)
    {
        $keys = ['name', 'email', 'mobile_no', 'source', 'reason', 'follow_up_number', 'is_appointed', 'remark'];
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

            $validator = Validator::make($col->toArray(), $this->rules(), $this->validationMessages());
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $messages) {
                    foreach ($messages as $error) {
                        $this->errors[] = $error . ' on row ' . $i;
                    }
                }
            }

            if (CallDetail::where([
                'email' => $col['email'], 'follow_up_number' => $col['follow_up_number'], 'user_id' => Auth::guard('api')->id()
            ])->exists()) {
                $this->errors[] = config('constants.messages.errors.email_already_taken') . ' on row ' . $i;
            }
            if (CallDetail::where([
                'mobile_no' => $col['mobile_no'], 'follow_up_number' => $col['follow_up_number'], 'user_id' => Auth::guard('api')->id()
            ])->exists()) {
                $this->errors[] = config('constants.messages.errors.mobile_already_taken') . ' on row ' . $i;
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
                CallDetail::create([
                    'user_id' => Auth::guard('api')->id(),
                    'name' => (string)$col['name'],
                    'email' => (string)$col['email'],
                    'mobile_no' => (string)$col['mobile_no'],
                    'source' => (string)$col['source'],
                    'follow_up_number' => (string)$col['follow_up_number'],
                    'is_appointed' => (string)$col['is_appointed'],
                    'remark' => (string)$col['remark'],
                    'reason' => ($col['reason'] == 'MF') ? config('constants.call_detail.reason.0')
                        : (($col['reason'] == 'FR') ? config('constants.call_detail.reason.1')
                            : (($col['reason'] == 'LM') ? config('constants.call_detail.reason.2')
                                : (($col['reason'] == 'CO') ? config('constants.call_detail.reason.3')
                                    : config('constants.call_detail.reason.4'))))
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
