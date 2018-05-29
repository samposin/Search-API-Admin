<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\DateHelper;
use App\SearchFeed;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Flash;
use Illuminate\Support\Facades\Redirect;

class SearchFeedsController extends Controller
{
    public function index()
    {
        $search_feeds=SearchFeed::where('is_delete', '=', 0)->orderBy('client_name','asc')->get();

        return view('pages.admin.search-feeds.index',compact('search_feeds'));
    }

    public function search_feeds_list_ajax(Request $request)
    {
        // DB columns array
        $columns=array(
            "id",
            "client_name",
            "url",
            "is_active",
            "created_at"
        );

        // local variables for POST variables for searching columns
        $search_feed_id="";
        $search_feed_client_name="";
        $search_feed_url="";
        $search_feed_is_active="";
        $search_feed_created_from="";
        $search_feed_created_to="";

        // Assigning POST values to local variables

        if($request->has('search_feed_id') && $request->get('search_feed_id')!=null)
            $search_feed_id=trim($request->get('search_feed_id'));

        if($request->has('search_feed_client_name') && $request->get('search_feed_client_name')!=null)
            $search_feed_client_name=trim($request->get('search_feed_client_name'));

        if($request->has('search_feed_url') && $request->get('search_feed_url')!=null)
            $search_feed_url=trim($request->get('search_feed_url'));

        if($request->has('search_feed_is_active') && $request->get('search_feed_is_active')!=null)
            $search_feed_is_active=trim($request->get('search_feed_is_active'));


        if($request->has('search_feed_created_from') && $request->get('search_feed_created_from')!=null) {
            $search_feed_created_from = $request->get('search_feed_created_from');
            $search_feed_created_from_obj = DateHelper::dateStringToCarbon($search_feed_created_from ,'d/m/Y');
            $search_feed_created_from =$search_feed_created_from_obj->format('Y-m-d 00:00:00');
        }

        if($request->has('search_feed_created_to') && $request->get('search_feed_created_to')!=null) {
            $search_feed_created_to = $request->get('search_feed_created_to');
            $search_feed_created_to_obj = DateHelper::dateStringToCarbon($search_feed_created_to ,'d/m/Y');
            $search_feed_created_to =$search_feed_created_to_obj->format('Y-m-d 23:59:59');
        }

        $iDisplayLength = intval($request->get('length'));  // getting rows per page value for paging
        $iDisplayStart = intval($request->get('start'));    // getting offset value for paging
        $sEcho = intval($request->get('draw'));

        $query_order_array=$request->get('order', array(array('column'=>1,'dir'=>'asc')));
        $query_order_column=$query_order_array[0]['column'];
        $query_order_direction=$query_order_array[0]['dir'];

        // Building query for search
        $query = DB::table('search_feeds');
        $query->select('search_feeds.*');
        $query->where('search_feeds.is_delete','=',0);

        if($search_feed_id!=null)
            $query->where('search_feeds.id','=',$search_feed_id);

        if($search_feed_client_name!=null)
            $query->where('search_feeds.client_name','LIKE','%'.$search_feed_client_name.'%');

        if($search_feed_url!=null)
            $query->where('search_feeds.url','LIKE','%'.$search_feed_url.'%');

		if($search_feed_is_active!=null)
		{
			if($search_feed_is_active=='yes')
				$query->where('search_feeds.is_active','=',1);
			else
				$query->where('search_feeds.is_active','=',0);
		}

        if($search_feed_created_from!=null)
            $query->where('search_feeds.created_at','>=',$search_feed_created_from);

        if($search_feed_created_to!=null)
            $query->where('search_feeds.created_at','<=',$search_feed_created_to);

        // copying query for total records
        //$copy_query = $query;
        //$iTotalRecords=$copy_query->count();

        $sql=$query->toSql();

        $count = DB::table( DB::raw("($sql) as sub") )
		    ->mergeBindings($query) // you need to get underlying Query Builder
		    ->count();

		$iTotalRecords=$count;

        $query->orderBy($columns[$query_order_column], $query_order_direction);

        if($iDisplayLength>0)
            $query->limit($iDisplayLength)->offset($iDisplayStart);

        //getting searched records
        $search_feeds=$query->get();

        $i=0;
        $records = array();
        $records["data"] = array();
        foreach($search_feeds as $search_feed)
        {
            $search_feed_is_active=$search_feed->is_active;

            if(trim($search_feed_is_active)=="" || trim($search_feed_is_active)=="0")
                $search_feed_is_active_display="No";
            else
                $search_feed_is_active_display="Yes";

            $search_feed->created_at =  Carbon::parse($search_feed->created_at);
            $records['data'][$i][]=$search_feed->id;
            $records['data'][$i][]=$search_feed->client_name;
            $records['data'][$i][]=$search_feed->url;
            $records['data'][$i][]=$search_feed_is_active_display;
            $records['data'][$i][]=$search_feed->created_at->format('m-d-Y h:i A');
            $records['data'][$i][]='
                <div class="btn-group" role="group">
                    <a href="'.url('admin/search-feeds', [$search_feed->id]).'" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></a>
                    <a href="javascript:void(0);" onclick="func_Delete_Search_Feed(\''.$search_feed->id.'\')" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></a>
                </div>';
            $i++;
        }
        if ($request->get("customActionType")!==null && $request->get("customActionType") == "group_action") {
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Group action successfully has been completed. Well done!"; // pass custom message(useful for getting status of group actions)
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return $records;
    }

    public function store(Request $request)
    {
        $messages = [
            'client_name.required'  => 'Please provide client name.'
        ];

        $this->validate($request, [
            'client_name' => 'required'
        ],$messages);

        //get all posted values
        $input = $request->all();


        // insert into db
        $search_feed= SearchFeed::create($input);

        if( $request->ajax())
        {
            return array(
                'success' => true,
            );
        }
        else {
            flash()->Success("Search Feed created successfully.");
            return redirect('admin/search-feeds');
        }

    }

    public function show($id)
    {
        $search_feed = SearchFeed::find($id);

        if ($search_feed == null)
        {
            Flash::error('That search feed doesn\'t exist.');
            return Redirect::route('search-feeds-home')->with('fail', "That search feed doesn't exist.");
        }

        return view('pages.admin.search-feeds.detail',compact('search_feed'));
    }

    public function update($id, Request $request)
    {
        $search_feed = SearchFeed::find($id);
        if ($search_feed == null)
        {
            Flash::error('That search feed doesn\'t exist.');
            return Redirect::route('search-feeds-home')->with('fail', "That search feed doesn't exist.");
        }

        $messages = [
            'client_name.required'  => 'Please provide client name.',
        ];

        $this->validate($request, [
            'client_name' => 'required',
        ],$messages);

        //get all posted values
        $input = $request->all();

        // update into db
        $search_feed->fill($input)->save();

        if( $request->ajax())
        {
            return array(
                'success' => true,
            );
        }
        else {
            flash()->Success("Search feed updated successfully.");
            return redirect()->back();
        }
    }

    public function delete(Request $request)
    {
        //get all posted values
        //$input=$request->all();

        $id=$request->get('hdn_search_feed_id');

        $search_feed = SearchFeed::find($id);
        if ($search_feed == null)
        {
            Flash::error('That search feed doesn\'t exist.');
            return Redirect::route('search-feeds-home')->with('fail', "That search feed doesn't exist.");
        }

        $input['is_delete'] = 1;

        // update into db
        $search_feed->fill($input)->save();

        if( $request->ajax())
        {
            return array(
                'success' => true,
            );
        }
        else {
            flash()->Success("Search feed deleted successfully.");
            return redirect()->back();
        }
    }
}
