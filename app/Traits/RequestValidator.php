<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait RequestValidator
{
    public static function validate(array $data)
    {
        $validator = Validator::make($data, (new self)->rules());
        return $validator;
    }
}
