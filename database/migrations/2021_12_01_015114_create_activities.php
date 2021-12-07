<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('parent_activity_id')->nullable()->constrained('activities');
            $table->foreignId('type_id')->constrained();
            $table->string('name');
            $table->string('priority')->nullable();
            $table->json('days')->nullable();
            $table->unsignedInteger('minutes')->nullable();
            $table->string('limit_min')->nullable();
            $table->string('limit_max')->nullable();
            $table->boolean('disabled')->default(0);
            $table->string('start')->nullable();
            $table->string('end')->nullable();
            $table->string('date')->nullable();
            $table->string('sound')->nullable();
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
        Schema::dropIfExists('activities');
    }
}
