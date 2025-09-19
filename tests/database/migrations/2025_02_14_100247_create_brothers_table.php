<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** @noinspection PhpIllegalPsrClassPathInspection */
class CreateBrothersTable extends Migration
{
    public function up(): void
    {
        Schema::create('brothers', function (Blueprint $table) {
            $table->string('email'); // required
            $table->string('name'); // default => ignored because it has the default value in the model $attributes
            $table->string('number')->nullable(); // nullable, with default in model
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brothers');
    }
}
