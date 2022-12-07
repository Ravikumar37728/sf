<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id')->index()->unique()->comment('AUTO INCREMENT');
            $table->enum('user_type', ['0', '1', '2', '3', '4'])->comment('0 = Super Admin, 1 = Admin, 2 = Sub Admin, 3 = Lead Manager, 4 = Sales Associates');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->index()->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('mobile_no', 15)->nullable();
            $table->enum('status', ['0', '1', '2'])->default('1')->comment('0 = Inactive, 1 = Active, 2 = Block');
            $table->string('profile_photo')->nullable()->comment('jpg, jpeg, png');
            $table->string('profile_photo_thumb')->nullable()->comment('jpg, jpeg, png');
            $table->enum('gender', ['0', '1'])->default('1')->comment('0 = Female, 1 = Male');
            $table->string('remember_token')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('users')->insert([
            'user_type' => '0',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'super.admin@gmail.com',
            'password' => '$2y$10$ReUgp5G7J5egF4Pbqoqj1uapABmFnjVOoeNYrbOTHFjKDPmYYat0C',
            'mobile_no' => '9638527410',
            'status' => '1',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
