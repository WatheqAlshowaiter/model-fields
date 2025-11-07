<?php

namespace WatheqAlshowaiter\ModelFields\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Uncle extends Model {

    // todo check if it works
    //protected $dispatchesEvents = [
    //    'saved' => UserSaved::class,
    //    'deleted' => UserDeleted::class,
    //];
    protected static function boot(): void
    {
        parent::boot();

        self::creating(function ($model) {
            $model->column_1 = 'creating';
        });

        self::saving(function ($model) {
            // todo if model is new then it will be created
            $model->column_1 = 'created';
        });
    }
}

// todo UncleObserver
