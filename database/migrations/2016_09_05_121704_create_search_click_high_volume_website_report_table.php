<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSearchClickHighVolumeWebsiteReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_click_high_volume_website_report', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('total_clicks')->default(0);
            $table->string('dl_source');
            $table->string('widget');
            $table->string('domain');
            $table->string('country_code',50);
            $table->date('date');
            $table->timestamps();
            $table->index(['date','country_code','dl_source','domain'], 'date_country_code_dl_source_domain_index');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('search_click_high_volume_website_report');
    }
}
