<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_managers', function (Blueprint $table) {
            $table->bigIncrements('id')->index()->unique()->comment('AUTO INCREMENT');
            $table->unsignedBigInteger('user_id')->index()->comment('Users table ID');
            $table->enum('type', ['0', '1'])->comment('0 = Direct, 1 = Sub Admin Assigned');
            $table->unsignedBigInteger('admin_id')->index()->nullable()->comment('Admins table ID');
            $table->unsignedBigInteger('sub_admin_id')->index()->nullable()->comment('Sub Admins table ID');
            $table->unsignedBigInteger('base_location')->index()->comment('Cities table ID')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('lead_managers', function ($table) {
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sub_admin_id')->references('id')->on('sub_admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_managers');
    }
}
