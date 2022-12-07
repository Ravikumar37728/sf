<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_details', function (Blueprint $table) {
            $table->bigIncrements('id')->index()->unique()->comment('AUTO INCREMENT');
            $table->unsignedBigInteger('user_id')->index()->comment('Users table ID');
            $table->string('name')->nullable();
            $table->string('email')->index()->nullable();
            $table->string('mobile_no', 15)->nullable();
            $table->string('source')->nullable();
            $table->enum('reason', ['0', '1', '2', '3', '4'])->comment('0 = Master Franchise, 1 = Franchise, 2 = Lead Manager, 3 = Consultant, 4 = Subscriber');
            $table->string('follow_up_number')->nullable();
            $table->enum('is_appointed', ['0', '1'])->comment('0 = Yes, 1 = No');
            $table->string('remark')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('call_details', function ($table) {
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
        Schema::dropIfExists('call_details');
    }
}
