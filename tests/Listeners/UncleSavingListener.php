<?php

namespace WatheqAlshowaiter\ModelFields\Tests\Listeners;

use WatheqAlshowaiter\ModelFields\Tests\Models\UncleSaving;

class UncleSavingListener
{
    public function handle(UncleSaving $event): void
    {
        $event->model->event_saving = 'saving';
    }
}
