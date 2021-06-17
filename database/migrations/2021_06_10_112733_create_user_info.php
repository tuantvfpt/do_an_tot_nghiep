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
            $table->foreign('position_id')->references('id')->on('Position');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('Department');
            $table->integer('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->dateTime('date_of_join')->nullable();
            $table->decimal('Basic_salary', 10, 2)->nullable();
            $table->string('Code_QR')->nullable();
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
