<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** @noinspection PhpIllegalPsrClassPathInspection */

class CreateUnclesTable extends Migration
{
    public function up(): void
    {
        Schema::create('uncles', function (Blueprint $table) {
            $table->string('boot_creating'); // filed by creating boot method in model
            $table->string('boot_saving'); //filed by saving boot method in model
            $table->string('observer_creating'); // filed by observer creating
            $table->string('observer_saving'); // filled by observer saving
            $table->string('event_creating'); // filled by event creating
            $table->string('event_saving'); // filled by event saving
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uncles');
    }
}
