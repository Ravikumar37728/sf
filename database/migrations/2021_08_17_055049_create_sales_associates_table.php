<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesAssociatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_associates', function (Blueprint $table) {
            $table->bigIncrements('id')->index()->unique()->comment('AUTO INCREMENT');
            $table->unsignedBigInteger('user_id')->index()->comment('Users table ID')->nullable();
            $table->enum('type', ['0', '1', '2'])->comment('0 = Direct, 1 = Sub Admin Assigned, 2 = Lead Manager Assigned');
            $table->unsignedBigInteger('admin_id')->index()->nullable()->comment('Admins table ID');
            $table->unsignedBigInteger('sub_admin_id')->index()->nullable()->comment('Sub Admins table ID');
            $table->unsignedBigInteger('lead_manager_id')->index()->nullable()->comment('Lead Managers table ID');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('sales_associates', function ($table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreign('sub_admin_id')->references('id')->on('sub_admins')->onDelete('cascade');
            $table->foreign('lead_manager_id')->references('id')->on('lead_managers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_associates');
    }
}
