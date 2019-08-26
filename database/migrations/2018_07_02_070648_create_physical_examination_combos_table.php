<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhysicalExaminationCombosTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('physical_examination_combos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->string('physical_examination_id', 64);
            $table->string('code', 64)->unique();
            $table->timestamps();
            $table->unique(['physical_examination_id', 'product_id'], 'prod_pe');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('physical_examination_combos');
    }
}
