<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductPostersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_posters', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->index();
            $table->string('src')->comment('海报图片');
            $table->float('x-axis')->comment('固定二维码的x轴起始位置');
            $table->float('y-axis')->comment('固定二维码的y轴起始位置');
            $table->float('width')->comment('二维码大小px');
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
        Schema::dropIfExists('product_posters');
    }
}
