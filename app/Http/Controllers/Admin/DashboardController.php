<?php

namespace App\Http\Controllers\Admin;

use App\Advertiser;
use \App\Company;
use \App\Contact;
use App\Publisher;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display dashboard with total number of items
     * @return $this
     */
    public function index()
    {
        $total_advertisers= Advertiser::where('is_delete', '=', 0)->count();
        $total_publishers = Publisher::where('is_delete', '=', 0)->count();
        return view('pages.admin.dashboard')->with('total_advertisers',$total_advertisers)->with('total_publishers',$total_publishers);
    }
}