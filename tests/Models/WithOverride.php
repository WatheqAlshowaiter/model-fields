<?php

namespace WatheqAlshowaiter\ModelFields\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class WithOverride extends Model
{
    public static function requiredFields()
    {
        return [
            'some override text',
        ];
    }
}
