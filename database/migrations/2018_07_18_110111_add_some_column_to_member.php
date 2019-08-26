<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeColumnToMember extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('identity', 32)->nullable();
            $table->text('identity_files')->nullable();
            $table->tinyInteger('identify_status')->nullable();
            $table->date('valid_date')->comment('身份证有效期')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['identity', 'identify_status', 'identity_files', 'valid_date']);
        });
    }
}
