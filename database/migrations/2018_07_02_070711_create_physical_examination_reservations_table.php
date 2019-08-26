<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhysicalExaminationReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('physical_examination_reservations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('physical_examination_qualification_id');
            $table->string('name');
            $table->string('mobile', 16);
            $table->string('identity', 32);
            $table->date('date');
            $table->string('org_id', 64);
            $table->string('org_name', 64);
            $table->string('org_address');
            $table->timestamps();
            $table->softDeletes();
            $table->index('physical_examination_qualification_id', 'peq_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('physical_examination_reservations');
    }
}
