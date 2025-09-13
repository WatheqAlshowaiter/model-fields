<?php

namespace WatheqAlshowaiter\ModelRequiredFields\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Brother extends Model
{
    protected $attributes = [
        'name' => 'default-user', // default
        'another' => '', // non-valid default
        'number' => '0000', // default for nullable field
        'non-existed-field' => 'some-random-value', // non-existed field in the database
    ];
}
