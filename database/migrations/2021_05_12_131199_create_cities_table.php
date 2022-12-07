<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->bigIncrements('id')->index()->unique()->comment('AUTO INCREMENT');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('state_id')->index()->comment('States table ID');
            $table->string('state_code')->nullable();
            $table->unsignedBigInteger('country_id')->index()->comment('Countries table ID');
            $table->char('country_code', 2)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('wikiDataId')->nullable();
        });

        Schema::table('cities', function ($table) {
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('state_id')->references('id')->on('states');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cities');
    }
}
