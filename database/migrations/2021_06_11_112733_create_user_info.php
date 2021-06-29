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
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->dateTime('date_of_join')->nullable();
            $table->float('basic_salary', 11, 2)->nullable();
            $table->string('code_QR')->nullable();
            $table->string('address')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('id_card')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
