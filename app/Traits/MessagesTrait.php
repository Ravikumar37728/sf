<?php

namespace App\Traits;

trait MessagesTrait
{

    public function getMessage($data, $message, $status_code, $is_success)
    {
        $status = ($is_success) ? true : false;
        return (!empty($data))
            ? response()->json(['data' => $data, 'status' => $status, 'status_code' => $status_code, 'message' => $message], $status_code)
            : response()->json(['status' => $status, 'status_code' => $status_code, 'message' => $message], $status_code);
    }
}
