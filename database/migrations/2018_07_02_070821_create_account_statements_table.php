<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_statements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->index();
            $table->tinyInteger('type');
            $table->integer('amount');
            $table->integer('order_id')->nullable();
            $table->tinyInteger('status');
            $table->integer('withdraw_id')->nullable();
            $table->integer('account_balance')->comment('记录某一时刻此比流水发生后的账户余额');
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
        Schema::dropIfExists('account_statements');
    }
}
