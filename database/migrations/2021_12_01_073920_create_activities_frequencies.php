<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesFrequencies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_frequency', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('activity_id')->constrained();
            $table->foreignId('frequency_id')->constrained();
            $table->unsignedInteger('amount')->default(1);
            $table->timestamp('date_completed')->nullable();
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
        Schema::dropIfExists('activities_frequencies');
    }
}
