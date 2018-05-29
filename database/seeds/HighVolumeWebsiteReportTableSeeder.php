<?php

use Illuminate\Database\Seeder;

class HighVolumeWebsiteReportTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$high_volume_website_report= new \App\CronHelpers\HighVolumeWebsiteReport();
	    $high_volume_website_report->seed_init();
    }
}