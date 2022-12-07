<?php

namespace App\Traits;

use Illuminate\Support\Collection as BaseCollection;

trait ConvertToStringTrait
{
    /**
     * Cast an attribute to a native PHP type.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        if (!is_null($value)) {
            return parent::castAttribute($key, $value);
        }
        switch ($this->getCastType($key)) {
            case 'int':
            case 'integer':
            case 'real':
            case 'float':
            case 'double':
            case 'string':
            case 'bool':
            case 'boolean':
            case 'date':
            case 'datetime':
            case 'timestamp':
            case 'null':
                return "";
            case 'object':
                return (object) [];
            case 'array':
            case 'json':
                return [];
            case 'collection':
                return new BaseCollection();
            default:
                return (string) $value;
        }
    }
}
