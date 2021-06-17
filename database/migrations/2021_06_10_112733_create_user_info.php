<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_info', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->string('full_name');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('position_id');
            $table->foreign('position_id')->references('id')->on('position');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('department');
            $table->integer('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->dateTime('date_of_join')->nullable();
            $table->float('basic_salary')->nullable();
            $table->string('code_QR')->nullable();
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
        Schema::dropIfExists('user_info');
    }
}
