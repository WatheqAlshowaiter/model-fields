<?php

namespace WatheqAlshowaiter\ModelFields\Tests\Listeners;

use WatheqAlshowaiter\ModelFields\Tests\Models\UncleCreating;

class UncleCreatingListener
{
    public function handle(UncleCreating $event): void
    {
        $event->model->event_creating = 'creating';
    }
}
