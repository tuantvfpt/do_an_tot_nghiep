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
            $table->string('name');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('chuc_vu_id');
            $table->foreign('chuc_vu_id')->references('id')->on('chuc_vu');
            $table->unsignedBigInteger('phong_ban_id');
            $table->foreign('phong_ban_id')->references('id')->on('phong_ban');
            $table->integer('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->dateTime('ngay_gia_nhap')->nullable();
            $table->integer('luong_co_ban')->nullable();
            $table->string('ma_QR')->nullable();
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
