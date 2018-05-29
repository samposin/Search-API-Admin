<?php

namespace App\Http\Controllers\Admin;

use App\SearchRequestAll;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    public function index()
    {


	    $count1=SearchRequestAll::select(DB::raw("count(id) as tot"),'search_request_all_new.*','sub.*')
	    ->rightJoin(
			DB::raw("(SELECT 'connexity' AS apiiii, 'Connexity' as apiiii_name
	UNION
	SELECT 'ebay_commerce_network'   AS apiiii, 'Connexity' as apiiii_name
	UNION
	SELECT 'dealspricer' AS apiiii, 'Dealspricer' as apiiii_name
	UNION
	SELECT 'kelkoo' AS apiiii, 'Kelkoo' as apiiii_name
	UNION
	SELECT 'twenga' AS apiiii, 'Twenga' as apiiii_name
	UNION
	SELECT 'zoom' AS apiiii, 'Zoom' as apiiii_name
	 ) as sub") ,'search_request_all_new.api_used', '=', 'sub.apiiii'
	    )
	    ->groupBy('sub.apiiii')
		->get();




        return view('pages.admin.users.index',compact('advertisers','advertiser_types'));
    }
}
