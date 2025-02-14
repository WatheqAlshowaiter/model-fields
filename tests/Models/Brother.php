<?php

namespace WatheqAlshowaiter\ModelRequiredFields\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use WatheqAlshowaiter\ModelRequiredFields\RequiredFields;

class Brother extends Model
{
    use RequiredFields;

    protected $attributes = [
        'name' => 'default-user', // default
        'another' => '', // non-valid default
        'non-existed-field' => 'some-random-value', // non-existed field in the database
    ];
}
