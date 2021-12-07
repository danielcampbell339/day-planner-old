<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTracker extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trackers', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('type'); // divide, max, increment
            $table->unsignedInteger('max')->nullable();
            $table->unsignedInteger('measurement')->nullable();
            $table->unsignedInteger('increment')->nullable();
            $table->string('unit')->nullable();
            $table->string('sound')->nullable();
            $table->string('disabled')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tracker');
    }
}
