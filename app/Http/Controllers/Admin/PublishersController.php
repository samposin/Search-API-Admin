<?php

namespace App\Http\Controllers\Admin;

use App\Advertiser;
use App\AdvertiserPublisherSearchDefault;
use App\AdvertiserSearchDefault;
use App\Helpers\DateHelper;
use App\Publisher;
use App\PublisherOnboarding;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Laracasts\Flash\Flash;

class PublishersController extends Controller
{
    public function index()
    {
        $publishers=Publisher::where('is_delete', '=', 0)->orderBy('name','asc')->get();
        return view('pages.admin.publishers.index',compact('publishers'));
    }

    public function publishers_list_ajax(Request $request)
    {
        // DB columns array
        $columns=array(
            "",
            "id",
            "name",
            "email",
            "created_at"
        );


        // local variables for POST variables for searching columns
        $publisher_id="";
        $publisher_name="";
        $publisher_email="";
        $publisher_created_from="";
        $publisher_created_to="";


        // Assigning POST values to local variables

        if($request->has('publisher_id') && $request->get('publisher_id')!=null)
            $publisher_id=trim($request->get('publisher_id'));

        if($request->has('publisher_name') && $request->get('publisher_name')!=null)
            $publisher_name=trim($request->get('publisher_name'));

        if($request->has('publisher_email') && $request->get('publisher_email')!=null)
            $publisher_email=trim($request->get('publisher_email'));


        if($request->has('publisher_created_from') && $request->get('publisher_created_from')!=null) {
            $publisher_created_from = $request->get('publisher_created_from');
            $publisher_created_from_obj = DateHelper::dateStringToCarbon($publisher_created_from ,'d/m/Y');
            $publisher_created_from =$publisher_created_from_obj->format('Y-m-d 00:00:00');
        }

        if($request->has('publisher_created_to') && $request->get('publisher_created_to')!=null) {
            $publisher_created_to = $request->get('publisher_created_to');
            $publisher_created_to_obj = DateHelper::dateStringToCarbon($publisher_created_to ,'d/m/Y');
            $publisher_created_to =$publisher_created_to_obj->format('Y-m-d 23:59:59');
        }



        $iDisplayLength = intval($request->get('length'));  // getting rows per page value for paging
        $iDisplayStart = intval($request->get('start'));    // getting offset value for paging
        $sEcho = intval($request->get('draw'));


        $query_order_array=$request->get('order', array(array('column'=>1,'dir'=>'asc')));
        $query_order_column=$query_order_array[0]['column'];
        $query_order_direction=$query_order_array[0]['dir'];


        // Building query for search
        $query = DB::table('publishers');
        $query->select('publishers.*');
        $query->where('publishers.is_delete','=',0);

        if($publisher_id!=null)
            $query->where('publishers.publisher_id','=',$publisher_id);

        if($publisher_name!=null)
            $query->where('publishers.name','LIKE','%'.$publisher_name.'%');

        if($publisher_email!=null)
            $query->where('publishers.email','LIKE','%'.$publisher_email.'%');

        if($publisher_created_from!=null)
            $query->where('publishers.created_at','>=',$publisher_created_from);

        if($publisher_created_to!=null)
            $query->where('publishers.created_at','<=',$publisher_created_to);


        // copying query for total records
        $copy_query = $query;
        $iTotalRecords=$copy_query->count();

        $query->orderBy($columns[$query_order_column], $query_order_direction);

        if($iDisplayLength>0)
            $query->limit($iDisplayLength)->offset($iDisplayStart);


        //getting searched records
        $publishers=$query->get();


        $i=0;
        $records = array();
        $records["data"] = array();
        foreach($publishers as $publisher)
        {
            $publisher->created_at =  Carbon::parse($publisher->created_at);
            $records['data'][$i][]='<input type="checkbox" name="id[]" value="'.$publisher->id.'">';
            $records['data'][$i][]=$publisher->id;
            $records['data'][$i][]=$publisher->name;

            if(trim($publisher->email)=="")
                $records['data'][$i][]="N/A";
            else
                $records['data'][$i][]=$publisher->email;


            $records['data'][$i][]=$publisher->created_at->format('m-d-Y h:i A');
            $records['data'][$i][]='
                <div class="btn-group" role="group">
                    <a href="'.url('admin/publishers', [$publisher->id]).'" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></a>
                    <a href="javascript:void(0);" onclick="func_Delete_Publisher(\''.$publisher->id.'\')" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></a>
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

    public function create()
    {
        $advertisers=Advertiser::where('is_delete','=',0)->orderBy('id','asc')->get();

        return view('pages.admin.publishers.create',compact('advertisers'));
    }

    public function store(Request $request)
    {
        $publisher_input=[];


        $messages = [
            'name.required'  => 'Please provide publisher name.'
        ];

        $this->validate($request, [
            'name' => 'required'
        ],$messages);

        //get all posted values
        $input = $request->all();
        $publisher_input['name']=$input['name'];
        $publisher_input['email']=$input['email'];
        $publisher_input['is_delete']=0;


        // insert into db
        $publisher=Publisher::create($publisher_input);

        $data=[];

        for($i=0;$i<count($input['publisher_id1s']);$i++)
        {
            if(trim($input['publisher_id1s'][$i])=="")
                $input['shares'][$i]="";
            $data[$input['advertizer_ids'][$i]]=array('publisher_id1' => $input['publisher_id1s'][$i],'share' => $input['shares'][$i]);
        }

        $publisher->advertisers()->sync($data);

        if( $request->ajax())
        {
            return array(
                'success' => true,
            );
        }
        else {
            flash()->Success("Publisher created successfully.");
            return redirect('admin/publishers');
        }
    }

    public function show($id)
    {
        $publisher = Publisher::find($id);
        if ($publisher == null)
        {
            Flash::error('That publisher doesn\'t exist.');
            return Redirect::route('publishers-home')->with('fail', "That publisher doesn't exist.");
        }

        $advertisers = DB::table('advertisers')
            ->leftJoin('advertisers_publishers', function($join) use ($id)
            {
                $join->on('advertisers.id', '=', 'advertisers_publishers.advertiser_id');
                $join->on('advertisers_publishers.publisher_id','=',DB::raw("'".$id."'"));
            })
            ->leftJoin('publishers', 'publishers.id', '=', 'advertisers_publishers.publisher_id')
            ->where( 'advertisers.is_delete', '=', 0 )
            ->select(
                'advertisers.id AS advertiserid',
                'publishers.id AS publisherid',
                'advertisers.name AS advertisername',
                'publisher_id1',
                'share',
                DB::raw('IF(publisher_id1="","",IF(`share`=0.00,"",CONCAT(`share`," %"))) AS display_share')
            )
            ->orderBy('advertisers.id','asc')
            ->get();


		$country_available_api_arr=\Config::get('custom.country_available_api_arr');

		$advertiser_publisher_search_defaults=AdvertiserPublisherSearchDefault::where('publisher_id','=',$id)->orderBy('geo','asc')->first();

		if ($advertiser_publisher_search_defaults == null)
        {
			$advertiser_search_defaults=AdvertiserSearchDefault::orderBy('geo','asc')->get();

			$advertiser_publisher_search_defaults_input['publisher_id']=$id;
			foreach($advertiser_search_defaults as $advertiser_search_default)
			{

				$advertiser_publisher_search_defaults_input['geo']=$advertiser_search_default->geo;
				$advertiser_publisher_search_defaults_input['main_api']=$advertiser_search_default->main_api;
				$advertiser_publisher_search_defaults_input['first_backfill_api']=$advertiser_search_default->first_backfill_api;
				$advertiser_publisher_search_defaults_input['second_backfill_api']=$advertiser_search_default->second_backfill_api;
				AdvertiserPublisherSearchDefault::create($advertiser_publisher_search_defaults_input);
			}

        }

        $advertiser_publisher_search_defaults=AdvertiserPublisherSearchDefault::where('publisher_id','=',$id)->orderBy('geo','asc')->get();
		//echo "<pre>";
		//print_r($advertiser_publisher_search_defaults);
		$advertiser_publisher_search_defaults_arr=array();
		foreach($advertiser_publisher_search_defaults as $advertiser_publisher_search_default)
		{

			$advertiser_publisher_search_defaults_arr[$advertiser_publisher_search_default->geo]=array('main'=>$advertiser_publisher_search_default->main_api,"first"=>$advertiser_publisher_search_default->first_backfill_api,"second"=>$advertiser_publisher_search_default->second_backfill_api);

		}
		//print_r($advertiser_publisher_search_defaults_arr);


        return view('pages.admin.publishers.detail',compact('publisher','advertisers','country_available_api_arr','advertiser_publisher_search_defaults_arr'))->with('publisher_id',$id);
            //->with('advertiser_id',$advertiser_id);
    }

    public function update($id, Request $request)
    {
        $publisher = Publisher::find($id);
        if ($publisher == null)
        {
            Flash::error('That publisher doesn\'t exist.');
            return Redirect::route('publishers-home')->with('fail', "That publisher doesn't exist.");
        }

        $messages = [
            'name.required'  => 'Please provide publisher name.',
        ];

        $this->validate($request, [
            'name' => 'required',
        ],$messages);


        //get all posted values
        $input = $request->all();

        $publisher_input['name']=$input['name'];
        $publisher_input['email']=$input['email'];


        // update into db
        $publisher->fill($publisher_input)->save();

        $data=[];

        for($i=0;$i<count($input['publisher_id1s']);$i++)
        {
            if(trim($input['publisher_id1s'][$i])=="")
                $input['shares'][$i]="";
            $data[$input['advertizer_ids'][$i]]=array('publisher_id1' => $input['publisher_id1s'][$i],'share' => $input['shares'][$i]);
        }

        $publisher->advertisers()->sync($data);

        if( $request->ajax())
        {
            return array(
                'success' => true,
            );
        }
        else {
            flash()->Success("Publisher updated successfully.");
            return redirect()->back();
        }
    }

    public function delete(Request $request)
    {
        //get all posted values
        //$input=$request->all();
        $id=$request->get('hdn_publisher_id');

        $publisher = Publisher::find($id);
        if ($publisher == null)
        {
            Flash::error('That publisher doesn\'t exist.');
            return Redirect::route('publishers-home')->with('fail', "That publisher doesn't exist.");
        }

        $input['is_delete'] = 1;

        // update into db
        $publisher->fill($input)->save();

        if( $request->ajax())
        {
            return array(
                'success' => true,
            );
        }
        else {
            flash()->Success("Publisher deleted successfully.");
            return redirect()->back();
        }
    }

    public function on_boarding_index()
    {

        return view('pages.admin.publishers.on-boarding.index');
    }

    public function on_boarding_list_ajax(Request $request)
    {
        // DB columns array
        $columns=array(
            "name",
            "publisher_onboarding.created_publisher_from_configurator",
            "publisher_onboarding.admin_section_add_publisher_name_email",
            "publisher_onboarding.email_sent_to_publisher_with_js",
            "publisher_onboarding.cross_check_analytics_profile_for_this_publisher",
            "publisher_onboarding.test_generated_js_working_or_not",
            "publisher_onboarding.test_generated_js_entering_data_on_analytics_or_not",
            "publishers.created_at"
        );


        // local variables for POST variables for searching columns
        $publisher_name="";
        $publisher_created_from="";
        $publisher_created_to="";


        // Assigning POST values to local variables


        if($request->has('publisher_name') && $request->get('publisher_name')!=null)
            $publisher_name=trim($request->get('publisher_name'));


        if($request->has('publisher_created_from') && $request->get('publisher_created_from')!=null) {
            $publisher_created_from = $request->get('publisher_created_from');
            $publisher_created_from_obj = DateHelper::dateStringToCarbon($publisher_created_from ,'d/m/Y');
            $publisher_created_from =$publisher_created_from_obj->format('Y-m-d 00:00:00');
        }

        if($request->has('publisher_created_to') && $request->get('publisher_created_to')!=null) {
            $publisher_created_to = $request->get('publisher_created_to');
            $publisher_created_to_obj = DateHelper::dateStringToCarbon($publisher_created_to ,'d/m/Y');
            $publisher_created_to =$publisher_created_to_obj->format('Y-m-d 23:59:59');
        }



        $iDisplayLength = intval($request->get('length'));  // getting rows per page value for paging
        $iDisplayStart = intval($request->get('start'));    // getting offset value for paging
        $sEcho = intval($request->get('draw'));


        $query_order_array=$request->get('order', array(array('column'=>0,'dir'=>'asc')));
        $query_order_column=$query_order_array[0]['column'];
        $query_order_direction=$query_order_array[0]['dir'];


        $query = DB::table('publishers');
        $query->leftjoin('publisher_onboarding','publishers.id','=','publisher_onboarding.publisher_id');
        $query->select(
            'publishers.id as publisherid',
            'publishers.created_at as publisher_created_at',
            'publishers.*',
            'publisher_onboarding.*'
		);
		$query->where('publishers.is_delete','=',0);



        if($publisher_name!=null)
            $query->where('publishers.name','LIKE','%'.$publisher_name.'%');


        if($publisher_created_from!=null)
            $query->where('publishers.created_at','>=',$publisher_created_from);

        if($publisher_created_to!=null)
            $query->where('publishers.created_at','<=',$publisher_created_to);


        // copying query for total records
        $copy_query = $query;
        $iTotalRecords=$copy_query->count();

        $query->orderBy($columns[$query_order_column], $query_order_direction);

        if($iDisplayLength>0)
            $query->limit($iDisplayLength)->offset($iDisplayStart);


        //getting searched records
        $publishers=$query->get();


        $i=0;
        $records = array();
        $records["data"] = array();
        foreach($publishers as $publisher)
        {
            $checked_string1="";
            if($publisher->created_publisher_from_configurator==null || $publisher->created_publisher_from_configurator==0)
                $checked_string1='';
			else
				$checked_string1='checked="checked"';

			$checked_string2="";
            if($publisher->admin_section_add_publisher_name_email==null || $publisher->admin_section_add_publisher_name_email==0)
                $checked_string2='';
			else
				$checked_string2='checked="checked"';


			$checked_string3="";
            if($publisher->email_sent_to_publisher_with_js==null || $publisher->email_sent_to_publisher_with_js==0)
                $checked_string3='';
			else
				$checked_string3='checked="checked"';


			$checked_string4="";
            if($publisher->cross_check_analytics_profile_for_this_publisher==null || $publisher->cross_check_analytics_profile_for_this_publisher==0)
                $checked_string4='';
			else
				$checked_string4='checked="checked"';


			$checked_string5="";
            if($publisher->test_generated_js_working_or_not==null || $publisher->test_generated_js_working_or_not==0)
                $checked_string5='';
			else
				$checked_string5='checked="checked"';

			$checked_string6="";
            if($publisher->test_generated_js_entering_data_on_analytics_or_not==null || $publisher->test_generated_js_entering_data_on_analytics_or_not==0)
                $checked_string6='';
			else
			    $checked_string6='checked="checked"';


            $publisher->publisher_created_at =  Carbon::parse($publisher->publisher_created_at);
            $records['data'][$i][]=$publisher->name;



			$records['data'][$i][]='<input data-field-name="created_publisher_from_configurator" type="checkbox" '.$checked_string1.' class="chk_publisher_on_boarding chk_'.$publisher->publisherid.'" name="chk_'.$publisher->publisherid.'_field1" value="'.$publisher->publisherid.'">';
			$records['data'][$i][]='<input data-field-name="admin_section_add_publisher_name_email" type="checkbox" '.$checked_string2.' class="chk_publisher_on_boarding chk_'.$publisher->publisherid.'"  name="chk_'.$publisher->publisherid.'_field2" value="'.$publisher->publisherid.'">';
			$records['data'][$i][]='<input data-field-name="email_sent_to_publisher_with_js" type="checkbox" '.$checked_string3.' class="chk_publisher_on_boarding chk_'.$publisher->publisherid.'"  name="chk_'.$publisher->publisherid.'_field3" value="'.$publisher->publisherid.'">';
			$records['data'][$i][]='<input data-field-name="cross_check_analytics_profile_for_this_publisher" type="checkbox" '.$checked_string4.' class="chk_publisher_on_boarding chk_'.$publisher->publisherid.'"  name="chk_'.$publisher->publisherid.'_field4" value="'.$publisher->publisherid.'">';
			$records['data'][$i][]='<input data-field-name="test_generated_js_working_or_not" type="checkbox" '.$checked_string5.' class="chk_publisher_on_boarding chk_'.$publisher->publisherid.'"  name="chk_'.$publisher->publisherid.'_field5" value="'.$publisher->publisherid.'">';
            $records['data'][$i][]='<input data-field-name="test_generated_js_entering_data_on_analytics_or_not" type="checkbox" '.$checked_string6.' class="chk_publisher_on_boarding chk_'.$publisher->publisherid.'" name="chk_'.$publisher->publisherid.'_field6" value="'.$publisher->publisherid.'">';
            $records['data'][$i][]=$publisher->publisher_created_at->format('m-d-Y h:i A');
            $records['data'][$i][]="";
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

    public function on_boarding_store(Request $request)
    {
        $publisher_on_boarding_input=[];


        //get all posted values
        $input = $request->all();
        $publisher_on_boarding_input['publisher_id']=$input['publisher_id'];
        $publisher_on_boarding_input['created_publisher_from_configurator']=$input['created_publisher_from_configurator'];
        $publisher_on_boarding_input['admin_section_add_publisher_name_email']=$input['admin_section_add_publisher_name_email'];
        $publisher_on_boarding_input['email_sent_to_publisher_with_js']=$input['email_sent_to_publisher_with_js'];
        $publisher_on_boarding_input['cross_check_analytics_profile_for_this_publisher']=$input['cross_check_analytics_profile_for_this_publisher'];
        $publisher_on_boarding_input['test_generated_js_working_or_not']=$input['test_generated_js_working_or_not'];
        $publisher_on_boarding_input['test_generated_js_entering_data_on_analytics_or_not']=$input['test_generated_js_entering_data_on_analytics_or_not'];


		$publisher_on_boarding = PublisherOnboarding::where('publisher_id', '=', $input['publisher_id'])->first();
		if ($publisher_on_boarding === null) {
		    // record doesn't exist
		    // insert into db
            $publisher_on_boarding=PublisherOnboarding::create($publisher_on_boarding_input);
		}
		else
		{
			// record exists
			// update into db
            $publisher_on_boarding->fill($publisher_on_boarding_input)->save();
		}


        if( $request->ajax())
        {
            return array(
                'success' => true,
            );
        }
        else {
            flash()->Success("Information updated successfully.");
            return redirect('admin/publishers/on-boarding');
        }
    }

    public function search_defaults_store(Request $request)
    {

		$advertiser_publisher_search_defaults_input=[];

        //get all posted values
        $input = $request->all();

        $advertiser_publisher_search_defaults_input['geo']=$input['geo'];
        $advertiser_publisher_search_defaults_input['publisher_id']=$input['publisher_id'];
        if($input['api_priority']=='main')
            $advertiser_publisher_search_defaults_input['main_api']=$input['api_name'];
        elseif($input['api_priority']=='first')
            $advertiser_publisher_search_defaults_input['first_backfill_api']=$input['api_name'];
		elseif($input['api_priority']=='second')
            $advertiser_publisher_search_defaults_input['second_backfill_api']=$input['api_name'];

        //echo "<pre>";
        //print_r($input);
        //echo "</pre>";

        //die();

        $advertiser_publisher_search_default = AdvertiserPublisherSearchDefault::where('geo', '=', $input['geo'])->where('publisher_id','=',$input['publisher_id'])->first();
		if ($advertiser_publisher_search_default === null) {
			// record doesn't exist
		    // insert into db
			$advertiser_publisher_search_default= AdvertiserPublisherSearchDefault::create($advertiser_publisher_search_defaults_input);
		}
		else
		{
			// record exists
			// update into db
            $advertiser_publisher_search_default->fill($advertiser_publisher_search_defaults_input)->save();
		}

        if( $request->ajax())
        {
            return array(
                'success' => true,
            );
        }
        else {
            flash()->Success("Information updated successfully.");
            return redirect('admin/publishers/advertiser-search-defaults');
        }

    }
}
