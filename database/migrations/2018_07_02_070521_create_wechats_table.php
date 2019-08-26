<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWechatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechats', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id')->unique()->nullable();
            $table->string('open_id', 64)->unique();
            $table->string('name', 32)->nullable();
            $table->string('avatar')->nullable();
            $table->tinyInteger('gender');
            $table->string('country', 32)->nullable();
            $table->string('province', 32)->nullable();
            $table->string('city', 32)->nullable();
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
        Schema::dropIfExists('wechats');
    }
}
