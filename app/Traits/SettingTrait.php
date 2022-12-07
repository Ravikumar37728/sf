<?php

namespace App\Traits;

use App\Models\Setting;

trait SettingTrait
{
    public function getSetting($key)
    {
        return Setting::where('setting_key', '=', $key)->first()->setting_value;
    }
}
