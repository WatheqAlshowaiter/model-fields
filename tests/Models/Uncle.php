<?php

namespace WatheqAlshowaiter\ModelFields\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Uncle extends Model
{
    protected $dispatchesEvents = [
        'creating' => UncleCreating::class, // fill `event_creating` field
        'saving' => UncleSaving::class, // fill `event_saving` field
    ];

    protected static function boot(): void
    {
        parent::boot();

        self::observe(UncleObserver::class);

        self::creating(function ($model) {
            $model->boot_creating = 'creating';
        });

        self::saving(function ($model) {
            $model->boot_saving = 'saving';
        });
    }
}

class UncleObserver
{
    public function creating(Uncle $model): void
    {
        $model->observer_creating = 'creating';
    }

    public function saving(Uncle $model): void
    {
        $model->observer_saving = 'saving';
    }
}

class UncleCreating
{
    public Uncle $model;

    public function __construct(Uncle $model) {
        $this->model = $model;
    }
}

class UncleSaving
{
    public Uncle $model;

    public function __construct(Uncle $model) {
        $this->model = $model;
    }
}
