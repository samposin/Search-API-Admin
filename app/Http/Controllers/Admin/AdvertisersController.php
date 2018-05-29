<?php

namespace App\Http\Controllers\Admin;

use App\Advertiser;
use App\AdvertiserSearchDefault;
use App\AdvertiserType;
use App\AdvertiserWidget;
use App\Helpers\DateHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Flash;
use Illuminate\Support\Facades\Redirect;

class AdvertisersController extends Controller
{
    public function index()
    {
        $advertisers=Advertiser::where('is_delete', '=', 0)->orderBy('name','asc')->get();
        //$advertiser_widgets=AdvertiserWidget::where('is_delete', '=', 0)->orderBy('name','asc')->get();
        $advertiser_types=AdvertiserType::where('is_delete', '=', 0)->orderBy('type','asc')->get();

        return view('pages.admin.advertisers.index',compact('advertisers','advertiser_types'));
    }

    public function advertisers_list_ajax(Request $request)
    {
        // DB columns array
        $columns=array(
            "",
            "id",
            "name",
            "advertiser_types.type",

            "",
            "created_at"
        );

        // local variables for POST variables for searching columns
        $advertiser_id="";
        $advertiser_name="";
        $advertiser_type_id="";
        $advertiser_widget_string="";

        $advertiser_created_from="";
        $advertiser_created_to="";

        // Assigning POST values to local variables

        if($request->has('advertiser_id') && $request->get('advertiser_id')!=null)
            $advertiser_id=trim($request->get('advertiser_id'));

        if($request->has('advertiser_name') && $request->get('advertiser_name')!=null)
            $advertiser_name=trim($request->get('advertiser_name'));

        if($request->has('advertiser_type') && $request->get('advertiser_type')!=null)
            $advertiser_type_id=trim($request->get('advertiser_type'));

        if($request->has('advertiser_widgets') && $request->get('advertiser_widgets')!=null)
            $advertiser_widget_string=trim($request->get('advertiser_widgets'));


        if($request->has('advertiser_created_from') && $request->get('advertiser_created_from')!=null) {
            $advertiser_created_from = $request->get('advertiser_created_from');
            $advertiser_created_from_obj = DateHelper::dateStringToCarbon($advertiser_created_from ,'d/m/Y');
            $advertiser_created_from =$advertiser_created_from_obj->format('Y-m-d 00:00:00');
        }

        if($request->has('advertiser_created_to') && $request->get('advertiser_created_to')!=null) {
            $advertiser_created_to = $request->get('advertiser_created_to');
            $advertiser_created_to_obj = DateHelper::dateStringToCarbon($advertiser_created_to ,'d/m/Y');
            $advertiser_created_to =$advertiser_created_to_obj->format('Y-m-d 23:59:59');
        }

        $iDisplayLength = intval($request->get('length'));  // getting rows per page value for paging
        $iDisplayStart = intval($request->get('start'));    // getting offset value for paging
        $sEcho = intval($request->get('draw'));

        $query_order_array=$request->get('order', array(array('column'=>1,'dir'=>'asc')));
        $query_order_column=$query_order_array[0]['column'];
        $query_order_direction=$query_order_array[0]['dir'];

        // Building query for search
        $query = DB::table('advertisers');
        $query->leftjoin('advertiser_types','advertiser_types.id','=','advertisers.type_id');
        $query->leftjoin('advertiser_widgets_advertisers','advertiser_widgets_advertisers.advertiser_id','=','advertisers.id');
        $query->leftjoin('advertiser_widgets','advertiser_widgets.id','=','advertiser_widgets_advertisers.advertiser_widget_id');
        $query->select(DB::raw("GROUP_CONCAT(DISTINCT advertiser_widgets.name ORDER BY advertiser_widgets.name SEPARATOR ', ') as advertiserwidgets"),'advertisers.*', 'advertiser_types.type as advertiser_type');
        //$query->select('advertisers.*', 'advertiser_types.type as advertiser_type');
        $query->where('advertisers.is_delete','=',0);

        /*$tags[]=1;
        $tags[]=2;
        $query->whereHas('advertiser_widgets', function($query) use($tags) {
            $query->whereIn('name', $tags);
        });
        */

        if($advertiser_id!=null)
            $query->where('advertisers.id','=',$advertiser_id);

        if($advertiser_name!=null)
            $query->where('advertisers.name','LIKE','%'.$advertiser_name.'%');


        if($advertiser_type_id!=null)
            $query->where('advertisers.type_id','=',$advertiser_type_id);

        if($advertiser_created_from!=null)
            $query->where('advertisers.created_at','>=',$advertiser_created_from);

        if($advertiser_created_to!=null)
            $query->where('advertisers.created_at','<=',$advertiser_created_to);


        $query->groupBy('advertisers.id');
        if($advertiser_widget_string!=null)
            $query->havingRaw("GROUP_CONCAT(DISTINCT advertiser_widgets.name ORDER BY advertiser_widgets.name SEPARATOR ', ') LIKE '%".$advertiser_widget_string."%'");

        // copying query for total records
        //$copy_query = $query;
        //$iTotalRecords=$copy_query->count();

        $sql=$query->toSql();

        $count = DB::table( DB::raw("($sql) as sub") )
		    ->mergeBindings($query) // you need to get underlying Query Builder
		    ->count();

		$iTotalRecords=$count;

        //$iTotalRecords= DB::table(DB::raw("($sql) AS a"))->count();

        $query->orderBy($columns[$query_order_column], $query_order_direction);

        if($iDisplayLength>0)
            $query->limit($iDisplayLength)->offset($iDisplayStart);

        //getting searched records
        $advertisers=$query->get();

        $i=0;
        $records = array();
        $records["data"] = array();
        foreach($advertisers as $advertiser)
        {
            $advertiserwidgets=$advertiser->advertiserwidgets;
            if(trim($advertiserwidgets)=="")
                $advertiserwidgets="N/A";

            $advertisertype=$advertiser->advertiser_type;
            if(trim($advertisertype)=="")
                $advertisertype="N/A";


            $advertiser->created_at =  Carbon::parse($advertiser->created_at);
            $records['data'][$i][]='<input type="checkbox" name="id[]" value="'.$advertiser->id.'">';
            $records['data'][$i][]=$advertiser->id;
            $records['data'][$i][]=$advertiser->name;
            $records['data'][$i][]=$advertisertype;
            $records['data'][$i][]=$advertiserwidgets;

            $records['data'][$i][]=$advertiser->created_at->format('m-d-Y h:i A');
            $records['data'][$i][]='
                <div class="btn-group" role="group">
                    <a href="'.url('admin/advertisers', [$advertiser->id]).'" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></a>
                    <a href="javascript:void(0);" onclick="func_Delete_Advertiser(\''.$advertiser->id.'\')" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></a>
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
            'name.required'  => 'Please provide advertiser name.'
        ];

        $this->validate($request, [
            'name' => 'required'
        ],$messages);

        //get all posted values
        $input = $request->all();

        $input['type_id']=0;
        if($request->has('advertiser_type_id') && $request->get('advertiser_type_id')!="") {
            $input['type_id'] = $request->get('advertiser_type_id');
        }

        //echo "<pre>";
        //print_r($input);
        //echo "</pre>";

        //die();

        // insert into db
        $advertiser= Advertiser::create($input);

        if($request->has('advertiser_widget_ids') && $request->get('advertiser_widget_ids')!="") {
            $advertiser_widget_ids = $request->get('advertiser_widget_ids');
            $advertiser_widget_ids_arr=explode(',',$advertiser_widget_ids);

            if (count($advertiser_widget_ids_arr) > 0)
                $advertiser->advertiser_widgets()->attach($advertiser_widget_ids_arr);
        }

        if( $request->ajax())
        {
            return array(
                'success' => true,
            );
        }
        else {
            flash()->Success("Advertiser created successfully.");
            return redirect('admin/advertisers');
        }

    }

    public function show($id)
    {
        $advertiser = Advertiser::find($id);

        if ($advertiser == null)
        {
            Flash::error('That advertiser doesn\'t exist.');
            return Redirect::route('advertisers-home')->with('fail', "That advertiser doesn't exist.");
        }

        $advertiser_types=AdvertiserType::where('is_delete', '=', 0)->orderBy('type','asc')->get();

        $advertiser_widgets = $advertiser->advertiser_widgets()->get(['advertiser_widgets.id','name']);

        $advertiser_widgets_arr=[];

        $advertiser_widgets_name_arr=[];

        foreach($advertiser_widgets as $advertiser_widget)
        {
            $advertiser_widgets_arr[]=array('id'=>$advertiser_widget->id,'text'=>$advertiser_widget->name);
            $advertiser_widgets_name_arr[]=$advertiser_widget->name;
        }

        $advertiser_widgets_json=json_encode($advertiser_widgets_arr);
        $advertiser_widgets_name_str=implode(', ',$advertiser_widgets_name_arr);

        if(trim($advertiser_widgets_name_str)=="")
            $advertiser_widgets_name_str="N/A";

        return view('pages.admin.advertisers.detail',compact('advertiser','advertiser_types'))->with('advertiser_widgets_json',$advertiser_widgets_json)->with('advertiser_widgets_name_str',$advertiser_widgets_name_str);
    }

    public function update($id, Request $request)
    {
        $advertiser = Advertiser::find($id);
        if ($advertiser == null)
        {
            Flash::error('That advertiser doesn\'t exist.');
            return Redirect::route('advertisers-home')->with('fail', "That advertiser doesn't exist.");
        }

        $messages = [
            'name.required'  => 'Please provide advertiser name.',
        ];

        $this->validate($request, [
            'name' => 'required',
        ],$messages);


        //get all posted values
        $input = $request->all();

        $input['type_id']=0;
        if($request->has('advertiser_type_id') && $request->get('advertiser_type_id')!="") {
            $input['type_id'] = $request->get('advertiser_type_id');
        }

        // update into db
        $advertiser->fill($input)->save();

        if($request->has('advertiser_widget_ids') && trim($request->get('advertiser_widget_ids'))!="") {
            $advertiser_widget_ids = $request->get('advertiser_widget_ids');
            $advertiser_widget_ids_arr=explode(',',$advertiser_widget_ids);

            if (count($advertiser_widget_ids_arr) > 0)
                $advertiser->advertiser_widgets()->sync($advertiser_widget_ids_arr);
        }
        else
        {
            $advertiser_widget_ids_arr=array();
            $advertiser->advertiser_widgets()->sync($advertiser_widget_ids_arr);
        }

        if( $request->ajax())
        {
            return array(
                'success' => true,
            );
        }
        else {
            flash()->Success("Advertiser updated successfully.");
            return redirect()->back();
        }
    }



    public function delete(Request $request)
    {
        //get all posted values
        //$input=$request->all();

        $id=$request->get('hdn_advertiser_id');

        $advertiser = Advertiser::find($id);
        if ($advertiser == null)
        {
            Flash::error('That advertiser doesn\'t exist.');
            return Redirect::route('advertisers-home')->with('fail', "That advertiser doesn't exist.");
        }

        $input['is_delete'] = 1;

        // update into db
        $advertiser->fill($input)->save();

        if( $request->ajax())
        {
            return array(
                'success' => true,
            );
        }
        else {
            flash()->Success("Advertiser deleted successfully.");
            return redirect()->back();
        }
    }

    public function search_defaults_store(Request $request)
    {

		$advertiser_search_defaults_input=[];

        //get all posted values
        $input = $request->all();

        $advertiser_search_defaults_input['geo']=$input['geo'];
        if($input['api_priority']=='main')
            $advertiser_search_defaults_input['main_api']=$input['api_name'];
        elseif($input['api_priority']=='first')
            $advertiser_search_defaults_input['first_backfill_api']=$input['api_name'];
		elseif($input['api_priority']=='second')
            $advertiser_search_defaults_input['second_backfill_api']=$input['api_name'];

        //echo "<pre>";
        //print_r($input);
        //echo "</pre>";

        //die();

        $advertiser_search_default = AdvertiserSearchDefault::where('geo', '=', $input['geo'])->first();
		if ($advertiser_search_default === null) {
			// record doesn't exist
		    // insert into db
			$advertiser_search_default= AdvertiserSearchDefault::create($advertiser_search_defaults_input);
		}
		else
		{
			// record exists
			// update into db
            $advertiser_search_default->fill($advertiser_search_defaults_input)->save();
		}

        if( $request->ajax())
        {
            return array(
                'success' => true,
            );
        }
        else {
            flash()->Success("Information updated successfully.");
            return redirect('admin/advertisers/search-defaults');
        }

    }

    public function search_defaults_index()
    {


		$country_available_api_arr=\Config::get('custom.country_available_api_arr');
		$advertiser_search_defaults=AdvertiserSearchDefault::orderBy('geo','asc')->get();
		//echo "<pre>";
		$advertiser_search_defaults_arr=array();
		foreach($advertiser_search_defaults as $advertiser_search_default)
		{

			$advertiser_search_defaults_arr[$advertiser_search_default->geo]=array('main'=>$advertiser_search_default->main_api,"first"=>$advertiser_search_default->first_backfill_api,"second"=>$advertiser_search_default->second_backfill_api);

		}
		//print_r($advertiser_search_defaults_arr);
		//echo "</pre>";

        return view('pages.admin.advertisers.search-defaults.index',compact('country_available_api_arr','advertiser_search_defaults_arr'));
    }
}
