<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_admins', function (Blueprint $table) {
            $table->bigIncrements('id')->index()->unique()->comment('AUTO INCREMENT');
            $table->unsignedBigInteger('user_id')->index()->comment('Users table ID');
            $table->unsignedBigInteger('admin_id')->index()->comment('Admins table ID');
            $table->unsignedBigInteger('city_assigned')->index()->comment('Cities table ID')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('sub_admins', function ($table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sub_admins');
    }
}
