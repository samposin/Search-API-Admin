<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwengaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twenga_report', function (Blueprint $table) {
            $table->increments('id');
            $table->string('date');
            $table->string('dl_source');
            $table->string('sub_dl_source');
            $table->string('widget');
            $table->string('country_code');
            $table->string('clicks');
            $table->string('estimated_revenue');
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
        Schema::drop('twenga_report');
    }
}
