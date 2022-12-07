<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->bigIncrements('id')->index()->unique()->comment('AUTO INCREMENT');
            $table->unsignedBigInteger('user_id')->index()->comment('Users table ID');
            $table->string('name_of_visited_outlet')->nullable();
            $table->string('name')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('area')->nullable();
            $table->string('remark')->nullable();
            $table->string('follow_up_number')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('visits', function ($table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visits');
    }
}
