<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;


class AdvertisersTableSeeder extends Seeder
{

    public function run()
    {
        DB::table('advertisers')->delete();

        $advertisers = array(
            array('id'=>1,'name'=>"Dealspricer","is_delete"=>0,"created_at"=>Carbon::now(),"updated_at"=>Carbon::now()),
            array('id'=>2,'name'=>"Ebay","is_delete"=>0,"created_at"=>Carbon::now(),"updated_at"=>Carbon::now()),
            array('id'=>3,'name'=>"FoxyDeal","is_delete"=>0,"created_at"=>Carbon::now(),"updated_at"=>Carbon::now()),
            array('id'=>4,'name'=>"Kelkoo","is_delete"=>0,"created_at"=>Carbon::now(),"updated_at"=>Carbon::now()),
            array('id'=>5,'name'=>"Pricegrabber","is_delete"=>0,"created_at"=>Carbon::now(),"updated_at"=>Carbon::now()),
            array('id'=>6,'name'=>"Shopzilla","is_delete"=>0,"created_at"=>Carbon::now(),"updated_at"=>Carbon::now()),
            array('id'=>7,'name'=>"Twenga","is_delete"=>0,"created_at"=>Carbon::now(),"updated_at"=>Carbon::now()),
            array('id'=>8,'name'=>"Visicom","is_delete"=>0,"created_at"=>Carbon::now(),"updated_at"=>Carbon::now()),
            array('id'=>9,'name'=>"Zoom","is_delete"=>0,"created_at"=>Carbon::now(),"updated_at"=>Carbon::now()),
            array('id'=>10,'name'=>"AdWorks","is_delete"=>0,"created_at"=>Carbon::now(),"updated_at"=>Carbon::now())
        );

        DB::table('advertisers')->insert($advertisers);
    }
}