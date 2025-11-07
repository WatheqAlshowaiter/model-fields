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
            $table->string('column_1'); // add by creating boot method in model
            $table->string('column_2'); //added by observer creating boot method in model
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uncles');
    }
}
