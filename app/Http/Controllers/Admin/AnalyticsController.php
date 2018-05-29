<?php

namespace App\Http\Controllers\Admin;


use App\Publisher;
use App\SearchClickHighVolumeWebsiteReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\DateHelper;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Laracasts\Flash\Flash;

class AnalyticsController extends Controller
{
	public function __construct()
	{
	    $this->middleware('php_ini_increase_time_memory', ['only' => ['high_volume_website_ajax']]);
	}

    public function index()
    {
        //$publishers=Publisher::where('is_delete', '=', 0)->orderBy('name','asc')->get();
        return view('pages.admin.analytics.index');
    }


    public function analytics_list_ajax(Request $request)
    {
        // DB columns array
        $columns=array(
            'id',
            'country_code',
            'clicks',
            'date',
        );


        // local variables for POST variables for searching columns

        $anaylytics_recent_type="";
        $anaylytics_time_period="";
        $analytics_order_date_start="";
        $analytics_order_date_end="";
        $analytics_group_by="";
        $analytics_then="";
        // Assigning POST values to local variables

        if($request->has('anaylytics_recent_type') && $request->get('anaylytics_recent_type')!=null)
            $anaylytics_recent_type=trim($request->get('anaylytics_recent_type'));

        if($request->has('advertiser_name') && $request->get('advertiser_name')!=null)
            $anaylytics_recent_type=trim($request->get('anaylytics_recent_type'));

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
        $query = DB::table('search_clicks');
        $query->select('search_clicks.*');
        //$query->where('publishers.is_delete','=',0);




        // copying query for total records
        $copy_query = $query;
        $iTotalRecords=$copy_query->count();

        $query->orderBy($columns[$query_order_column], $query_order_direction);

        if($iDisplayLength>0)
            $query->limit($iDisplayLength)->offset($iDisplayStart);


        //getting searched records
        //$analytics=$query->get();
        //print_r($analytics);
        $analytics = DB::table('search_clicks')
            ->select('*', DB::raw('count(*) as clicks'))
            ->groupBy('date')
            ->get();

        // print_r($analytics);
        // echo $ly =count($analytics);
        $i=0;
        $records = array();
        $records["data"] = array();
        foreach($analytics as $analytic)
        {



            //$publisher->created_at =  Carbon::parse($publisher->created_at);
            $records['data'][$i][]='<input type="checkbox" name="id[]" value="'.$analytic->id.'">';
            $records['data'][$i][]=$analytic->date;
            $records['data'][$i][]= $analytic->clicks;
            //$records['data'][$i][]=$publisher->created_at->format('m-d-Y h:i A');
            $records['data'][$i][]='
                <div class="btn-group" role="group">
                    <a href="'.url('admin/analytics', [$analytic->id]).'" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></a>
                    <a href="javascript:void(0);" onclick="func_Delete_Publisher(\''.$analytic->id.'\')" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></a>
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


    public function list_clicks_hourly_show()
    {

        return view('pages.admin.analytics.list.clicks.hourly');
    }

    public function list_clicks_hourly_ajax(Request $request)
    {

        $result_arr=array();
        $input=$request->all();

        $date=$input['date'];

        $date_exp=explode('-', $date);

        if(count($date_exp)==3)
        {
            $mm=$date_exp[0];
            $dd=$date_exp[1];
            $yy=$date_exp[2];

            $date = $yy . '-' . $mm . '-' . $dd;
        }


        $results = DB::table('search_clicks')
            ->select('*',
                DB::raw('CONCAT(LPAD(HOUR(created_at),2,"0"), ":00 - ",  LPAD(HOUR(created_at)+1,2,"0"), ":00") as hour_range ')  ,
                DB::raw('count(id) as total_clicks'),
                DB::raw('HOUR(created_at) as hour_created_at')
            )
            ->where('date','=', $date)
            ->groupBy('hour_range')
            ->orderBy('hour_created_at','asc')
            ->get();

        foreach($results as $result)
        {
            $result_arr[$result->hour_range]=$result->total_clicks;
        }

        for($i=0;$i<24;$i++)
        {
            $hour_range_arr[str_pad($i, 2, "0", STR_PAD_LEFT).':00 - '.str_pad(($i+1), 2, "0", STR_PAD_LEFT).':00']=0;

            $hour_range=str_pad($i, 2, "0", STR_PAD_LEFT).':00 - '.str_pad(($i+1), 2, "0", STR_PAD_LEFT).':00';

            if(isset($result_arr[$hour_range]))
            {
                $main_result_arr[]=array('hour_range'=>$hour_range,"total_clicks"=>$result_arr[$hour_range]);
            }
            else
            {
                $main_result_arr[]=array('hour_range'=>$hour_range,"total_clicks"=>0);
            }

        }

        //print_r($hour_range_arr);

        echo json_encode($main_result_arr);
    }


    public function list_searches_hourly_show()
    {

        return view('pages.admin.analytics.list.searches.hourly');
    }

    public function list_searches_hourly_ajax(Request $request)
    {

        $result_arr=array();
        $input=$request->all();

        $date=$input['date'];

        $date_exp=explode('-', $date);

        if(count($date_exp)==3)
        {
            $mm=$date_exp[0];
            $dd=$date_exp[1];
            $yy=$date_exp[2];

            $date = $yy . '-' . $mm . '-' . $dd;
        }


        $results = DB::table('search_info')
            ->select('*',
                DB::raw('CONCAT(LPAD(HOUR(created_at),2,"0"), ":00 - ",  LPAD(HOUR(created_at)+1,2,"0"), ":00") as hour_range ')  ,
                DB::raw('count(id) as total_clicks'),
                DB::raw('HOUR(created_at) as hour_created_at')
            )
            ->where(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d")'),'=', $date)
            ->groupBy('hour_range')
            ->orderBy('hour_created_at','asc')
            ->get();

        foreach($results as $result)
        {
            $result_arr[$result->hour_range]=$result->total_clicks;
        }

        for($i=0;$i<24;$i++)
        {
            $hour_range_arr[str_pad($i, 2, "0", STR_PAD_LEFT).':00 - '.str_pad(($i+1), 2, "0", STR_PAD_LEFT).':00']=0;

            $hour_range=str_pad($i, 2, "0", STR_PAD_LEFT).':00 - '.str_pad(($i+1), 2, "0", STR_PAD_LEFT).':00';

            if(isset($result_arr[$hour_range]))
            {
                $main_result_arr[]=array('hour_range'=>$hour_range,"total_clicks"=>$result_arr[$hour_range]);
            }
            else
            {
                $main_result_arr[]=array('hour_range'=>$hour_range,"total_clicks"=>0);
            }

        }

        //print_r($hour_range_arr);

        echo json_encode($main_result_arr);
    }




    public function daily_show()
    {

        return view('pages.admin.analytics.daily.index');
    }

    public function daily_ajax(Request $request)
    {

        $result_arr = array();
        $input = $request->all();


        $date_start = $input['date_start'];
        $date_end = $input['date_end'];

        $date_start_exp = explode('-', $date_start);
        $date_end_exp = explode('-', $date_end);

        if (count($date_start_exp) == 3) {
            $mm = $date_start_exp[0];
            $dd = $date_start_exp[1];
            $yy = $date_start_exp[2];

            $date_start = $yy . '-' . $mm . '-' . $dd;
        }

        if (count($date_end_exp) == 3) {
            $mm = $date_end_exp[0];
            $dd = $date_end_exp[1];
            $yy = $date_end_exp[2];

            $date_end = $yy . '-' . $mm . '-' . $dd;
        }




        $results = DB::table('search_click_report')
            ->select('*', DB::raw('sum(total_clicked) as sum_total_clicks'), DB::raw('sum(total_search) as sum_total_searches')
            )->where('date', '>=', $date_start)->where('date', '<=', $date_end)
            ->groupBy('date')
            ->get();

        $result_arr = array();
        $total_sum_clicks = 0;
        $total_sum_searches = 0;


        foreach ($results as $result) {


            if (!isset($result->total_clicked)) {

                $result_arr[$result->date]['sum_total_clicked'] = 0;

            }

            if (!isset($result->total_search)) {

                $result_arr[$result->date]['sum_total_searches'] = 0;

            }

            $total_sum_clicks += $result->sum_total_clicks;
            $total_sum_searches += $result->sum_total_searches;


            $result_arr[$result->date]['sum_total_clicks'] = $result->sum_total_clicks;
            $result_arr[$result->date]['sum_total_searches'] = $result->sum_total_searches;


        }


        $result_arr_mixed = array();

        foreach ($result_arr as $key => $value) {


            $result_arr_mixed[] = array('dates' => $key, "total_clicks" => $value['sum_total_clicks'], "total_searches" => $value['sum_total_searches'], "total_sum_clicks" => $total_sum_clicks, "total_sum_searches" => $total_sum_searches);


            //$result_arr_mixed=$result_arr+$result_arr_searches_info;

        }

        echo json_encode($result_arr_mixed);


    }




    public function hourly_show()
    {

        return view('pages.admin.analytics.hourly.index');
    }

    public function hourly_ajax(Request $request)
    {

        $result_arr = array();
        $input = $request->all();

        $date = $input['date_start'];


        $date_exp = explode('-', $date);


        if (count($date_exp) == 3) {
            $mm = $date_exp[0];
            $dd = $date_exp[1];
            $yy = $date_exp[2];

            $date = $yy . '-' . $mm . '-' . $dd;
        }
        $result_arr=array();

        $results = DB::table('search_click_report')
            ->select('*',
                'hour_display'
                ,DB::raw('sum(total_clicked) as sum_total_clicks'),
                DB::raw('sum(total_search) as sum_total_searches')
            )
            ->where('date','=', $date)
            ->groupBy('date')
            ->groupBy('hour_display')
            ->orderBy('id','asc')
            ->get();



        $total_sum_clicks=0;
        $total_sum_searches=0;
        foreach($results as $result)
        {

            // print_r($result);


            $result_arr[$result->hour_display]['sum_total_clicks']=$result->sum_total_clicks;
            $result_arr[$result->hour_display]['sum_total_searches']=$result->sum_total_searches;
            $total_sum_clicks+=$result->sum_total_clicks;
            $total_sum_searches+=$result->sum_total_searches;



        }
        $main_result_arr=array();
        //foreach ($result_arr as $key=>$value) {


        for ($i = 0; $i < 24; $i++) {
            $hour_range_arr[str_pad($i, 2, "0", STR_PAD_LEFT) . ':00 - ' . str_pad(($i + 1), 2, "0", STR_PAD_LEFT) . ':00'] = 0;

            $hour_range = str_pad($i, 2, "0", STR_PAD_LEFT) . ':00 - ' . str_pad(($i + 1), 2, "0", STR_PAD_LEFT) . ':00';

            if (isset($result_arr[$hour_range])) {

                $main_result_arr[] = array('hour_range' => $hour_range, "total_clicks" => $result_arr[$hour_range]['sum_total_clicks'], "total_searches" => $result_arr[$hour_range]['sum_total_searches'], "total_sum_clicks" => $total_sum_clicks, "total_sum_searches" => $total_sum_searches);
            } else {
                $main_result_arr[] = array('hour_range' => $hour_range, "total_clicks" => 0, "total_searches" => 0, "total_sum_clicks" => $total_sum_clicks, "total_sum_searches" => $total_sum_searches);
            }


        }
        echo json_encode($main_result_arr);

    }

    public function daily_jsver_show()
    {

        return view('pages.admin.analytics.daily-jsver.index');
    }

    public function daily_jsver_ajax(Request $request)
    {

        $result_arr = array();
        $input = $request->all();
        $widget=$input['jsver'];





        $results = DB::table('search_click_report')
            ->select('*', DB::raw('sum(total_clicked) as sum_total_clicks'), DB::raw('sum(total_search) as sum_total_searches')
            )
            ->groupBy('date')->groupBy('jsver')
            ->get();

        $result_arr = array();
        $result_arr_index1=array();
        $result_arr_index2=array();
        $result_arr_index3=array();
        $total_sum_clicks = 0;
        $total_sum_searches = 0;


        foreach ($results as $result)
        {


            if (!isset($result->total_clicked)) {

                $result_arr[$result->date]['sum_total_clicked'] = 0;

            }

            if (!isset($result->total_search)) {

                $result_arr[$result->date]['sum_total_searches'] = 0;

            }

            $total_sum_clicks += $result->sum_total_clicks;
            $total_sum_searches += $result->sum_total_searches;


            $result_arr[$result->date][$result->jsver]['sum_total_clicks'] = $result->sum_total_clicks;
            $result_arr[$result->date][$result->jsver]['sum_total_searches'] = $result->sum_total_searches;


        }



        $result_arr_mixed = array();
        $title=array();

        $j=0;
        foreach ($result_arr as $key => $value) {

            $result_arr_index1[$j]['dates']=$key;
            foreach($value as $key1=>$value1){

                $result_arr_index1[$j][$key1]=intval($value1['sum_total_clicks']);
                $result_arr_index2[] = array('dates' => $key, 'total_jsver'=>$key1, "total_clicks" => $value1['sum_total_clicks'], "total_searches" => $value1['sum_total_searches'], "total_sum_clicks" => $total_sum_clicks, "total_sum_searches" => $total_sum_searches);



                if($key1!=''){

                    $flag=0;

                    for($i=0;$i<count($title);$i++){

                        if($title[$i]==$key1){

                            $flag=1;

                        }



                    }

                    if($flag==0){

                        $title[]=$key1;


                    }


                }


            }

            $j++;

        }

        for($i=0;$i<count($title); $i++) {
            $result_arr_index3[] = array(
                "bullet" => "round",
                "bulletBorderAlpha" => 1,
                "bulletColor" => "#FFFFFF",
                "bulletSize" => 5,
                "hideBulletsCount" => 50,
                "lineThickness" => 2,
                "title" => $title[$i],
                "useLineColorForBulletBorder" => true,
                "valueField" => $title[$i],
                "balloonText" => "<span style='font-size:18px;'>$title[$i]:[[value]]</span>"

            );


        }

        $result_arr_mixed['graph_data']=$result_arr_index1;
        $result_arr_mixed['table_data']=$result_arr_index2;
        $result_arr_mixed['graph_categories']=$result_arr_index3;
        //$result_arr_mixed[3]=$result_arr_index4;
        echo json_encode($result_arr_mixed);

    }
    public function hourly_jsver_table_show()
    {

        return view('pages.admin.analytics.hourly-jsver.table.index');
    }

    public function hourly_jsver_table_ajax(Request $request)
    {

        $result_arr = array();
        $input = $request->all();
        $jsver=$input['jsver'];
        $date = $input['date_start'];
        $date_exp = explode('-', $date);
        if (count($date_exp) == 3) {
            $mm = $date_exp[0];
            $dd = $date_exp[1];
            $yy = $date_exp[2];
            $date = $yy . '-' . $mm . '-' . $dd;
        }
        $result_arr=array();

        $results = DB::table('search_click_report')
            ->select('*',
                'hour_display'
                ,DB::raw('sum(total_clicked) as sum_total_clicks'),
                DB::raw('sum(total_search) as sum_total_searches')
            )
            ->where('date','=', $date)
            ->groupBy('jsver')
            ->groupBy('date')
            ->groupBy('hour_display')
            ->orderBy('sum_total_clicks','desc')
            ->get();



        $total_sum_clicks=0;
        $total_sum_searches=0;
        $title=array();
        $result_arr_index1=array();
        $result_arr_index2=array();
        $result_arr_index3=array();
        $result_arr_mixed=array();

        foreach($results as $result)
        {

            $result_arr[$result->hour_display][$result->jsver]['sum_total_clicks']=$result->sum_total_clicks;
            $result_arr[$result->hour_display][$result->jsver]['sum_total_searches']=$result->sum_total_searches;
            $total_sum_clicks+=$result->sum_total_clicks;
            $total_sum_searches+=$result->sum_total_searches;

        }



        $j=0;
        $total_value_column='';
        foreach($result_arr as $key=>$value)
        {
            $result_arr_index1[$j]['hour_range']=$key;
            //$result_arr_index1[$j]['total_value_column']=$key;

            foreach($value as $key1=>$value1)
            {

                $result_arr_index1[$j][$key1]=intval($value1['sum_total_clicks']);



                if($key1!='') {

                    $flag = 0;

                    for ($i = 0; $i < count($title); $i++) {

                        if ($title[$i] == $key1) {

                            $flag = 1;

                        }


                    }

                    if ($flag == 0) {

                        $title[] = $key1;


                    }
                }

            }

            $j++;

        }

        $j=0;
        $total_value_column='';
        $vertical_total_array=array();

        //print_r($result_arr);
        foreach($result_arr as $key=>$value) {

            $horizontal_total = 0;
            foreach ($value as $key1 => $value1) {

                if(!isset($vertical_total_array[$key1]))
                {

                    $vertical_total_array[$key1]=0;

                }
                $vertical_total_array[$key1]=$vertical_total_array[$key1]+$value1['sum_total_clicks'];


                $result_arr_index2[$key]['jsver'][$key1]=$value1['sum_total_clicks'];
                $horizontal_total+=$value1['sum_total_clicks'];
            }
            $result_arr_index2[$key]['horizontal_total']=$horizontal_total;
            $j++;
        }

        foreach($vertical_total_array as $k=>$v)
        {
            foreach ($result_arr_index2 as $key1 => $value1) {
                if (!isset($result_arr_index2[$key1]['jsver'][$k])) {
                    $result_arr_index2[$key1]['jsver'][$k] = 0;
                }
            }

        }

        $result_arr_index2['Grand Total']['jsver']=$vertical_total_array;
        $total=0;
        foreach($vertical_total_array as $key=>$value) {
            $total+=$value;
        }

        $result_arr_index2['Grand Total']['horizontal_total']=$total;



        //$result_arr_mixed['graph_data']=$result_arr_index1;
        $result_arr_mixed['table_data']=$result_arr_index2;
        //$result_arr_mixed['graph_categories']=$result_arr_index3;
        //$result_arr_mixed['jsver_heading']=$title;
        echo json_encode($result_arr_mixed);

    }
    public function hourly_jsver_graph_show()
    {

        return view('pages.admin.analytics.hourly-jsver.graph.index');
    }

    public function hourly_jsver_graph_ajax(Request $request)
    {

        $result_arr = array();
        $input = $request->all();
        $jsver=$input['jsver'];
        $date = $input['date_start'];
        $date_exp = explode('-', $date);
        if (count($date_exp) == 3) {
            $mm = $date_exp[0];
            $dd = $date_exp[1];
            $yy = $date_exp[2];
            $date = $yy . '-' . $mm . '-' . $dd;
        }
        $result_arr=array();

        $results = DB::table('search_click_report')
            ->select('*',
                'hour_display'
                ,DB::raw('sum(total_clicked) as sum_total_clicks'),
                DB::raw('sum(total_search) as sum_total_searches')
            )
            ->where('date','=', $date)
            ->groupBy('jsver')
            ->groupBy('date')
            ->groupBy('hour_display')
            ->orderBy('hour_display','asc')
            ->get();



        $total_sum_clicks=0;
        $total_sum_searches=0;
        $title=array();
        $result_arr_index1=array();
        $result_arr_index2=array();
        $result_arr_index3=array();
        $result_arr_mixed=array();

        foreach($results as $result)
        {

            $result_arr[$result->hour_display][$result->jsver]['sum_total_clicks']=$result->sum_total_clicks;
            $result_arr[$result->hour_display][$result->jsver]['sum_total_searches']=$result->sum_total_searches;
            $total_sum_clicks+=$result->sum_total_clicks;
            $total_sum_searches+=$result->sum_total_searches;

        }



        $j=0;
        $total_value_column='';
        foreach($result_arr as $key=>$value)
        {
            $result_arr_index1[$j]['hour_range']=$key;
            //$result_arr_index1[$j]['total_value_column']=$key;

            foreach($value as $key1=>$value1)
            {

                $result_arr_index1[$j][$key1]=intval($value1['sum_total_clicks']);



                if($key1!='') {

                    $flag = 0;

                    for ($i = 0; $i < count($title); $i++) {

                        if ($title[$i] == $key1) {

                            $flag = 1;

                        }


                    }

                    if ($flag == 0) {

                        $title[] = $key1;


                    }
                }

            }

            $j++;

        }

        $j=0;
        $total_value_column='';


        for($i=0;$i<count($title); $i++) {
            $result_arr_index3[] = array(


                "bullet" => "round",
                "bulletBorderAlpha" => 1,
                "bulletColor" => "#FFFFFF",
                "bulletSize" => 5,
                "hideBulletsCount" => 30,
                "lineThickness" => 2,
                "title" => $title[$i],
                "useLineColorForBulletBorder" => true,
                "valueField" => $title[$i],
                "balloonText" => "<span style='font-size:18px;'>$title[$i]:[[value]]</span>"

            );



        }



        $result_arr_mixed['graph_data']=$result_arr_index1;
        //$result_arr_mixed['table_data']=$result_arr_index2;
        $result_arr_mixed['graph_categories']=$result_arr_index3;
       // $result_arr_mixed['jsver_heading']=$title;
        echo json_encode($result_arr_mixed);

    }
    public function daily_all_show()
    {

        $per_geo_arr=\Config::get('custom.country_available_api_arr');
        $per_publisher=DB::table('publishers')->where('is_delete','=',0)->get();
        $per_api=DB::table('search_click_report')->groupby('api')->get();
        $per_jsver=DB::table('search_click_report')->groupby('jsver')->get();
        $per_widget=DB::table('search_click_report')->groupby('widget')->get();
        $per_browser=DB::table('search_click_report')->groupby('browser')->get();

        return view('pages.admin.analytics.daily-all.index',compact('per_geo_arr','per_publisher','per_api','per_jsver','per_widget','per_browser'));
    }

    public function daily_all_ajax(Request $request)
    {

        $input = $request->all();
        $group_by=$input['group_by'];

        $query = DB::table('search_click_report');

        if(!empty($input['per_geo']))
            $query->where('country_code','=',$input['per_geo']);

        if(!empty($input['per_publisher']))
            $query->where('dl_source','=',$input['per_publisher']);

        if(!empty($input['per_api']))
            $query->where('api','=', $input['per_api']);

        if(!empty($input['per_jsver']))
            $query->where('jsver','=', $input['per_jsver']);

        if(!empty($input['per_widget']))
            $query->where('widget','=', $input['per_widget']);

        if(!empty($input['per_browser']))
            $query->where('browser','=', $input['per_browser']);

        $query->select('*', DB::raw('sum(total_clicked) as sum_total_clicks'), DB::raw('sum(total_search) as sum_total_searches'));
		$query->groupBy('date');

        if($group_by!='')
	        $query->groupBy($group_by);

        $results=$query->get();

        $result_arr = array();
        $result_arr_for_graph = array();
        $result_arr_for_table = array();
        $result_arr_for_graph_categories = array();
        $total_sum_clicks = 0;
        $total_sum_searches = 0;

        foreach ($results as $result)
        {
            if (!isset($result->total_clicked))
            {
                $result_arr[$result->date]['sum_total_clicked'] = 0;
            }

            if (!isset($result->total_search))
            {
                $result_arr[$result->date]['sum_total_searches'] = 0;
            }

            $total_sum_clicks += $result->sum_total_clicks;
            $total_sum_searches += $result->sum_total_searches;

            $result_arr[$result->date][$result->$group_by]['sum_total_clicks'] = $result->sum_total_clicks;
            $result_arr[$result->date][$result->$group_by]['sum_total_searches'] = $result->sum_total_searches;
        }

        $result_arr_mixed = array();
        $title_arr = array();

        $j=0;

        foreach ($result_arr as $key => $value)
        {
            $result_arr_for_graph[$j]['dates']=$key;

            foreach ($value as $key1 => $value1)
            {
                $result_arr_for_graph[$j][$key1]=intval($value1['sum_total_clicks']);
                $result_arr_for_table[] = array(
                    'dates' => $key,
                    'field'=>$group_by,
                    'total_all' => $key1,
                    "total_clicks" => $value1['sum_total_clicks'],
                    "total_searches" => $value1['sum_total_searches'],
                    "total_sum_clicks" => $total_sum_clicks,
                    "total_sum_searches" => $total_sum_searches
				);

                if ($key1 != '')
                {
                    if(!in_array($key1,$title_arr))
                        $title_arr[] = $key1;
                }
            }
            $j++;
        }

        for ($i = 0; $i < count($title_arr); $i++)
        {
            $result_arr_for_graph_categories[] = array(
                "bullet" => "round",
                "bulletBorderAlpha" => 1,
                "bulletColor" => "#FFFFFF",
                "bulletSize" => 5,
                "hideBulletsCount" => 50,
                "lineThickness" => 2,
                "title" => $title_arr[$i],
                "useLineColorForBulletBorder" => true,
                "valueField" => $title_arr[$i],
                "balloonText" => "<span style='font-size:18px;'>$title_arr[$i]:[[value]]</span>"
            );
        }
        $result_arr_mixed['graph_data']=$result_arr_for_graph;
        $result_arr_mixed['table_data']=$result_arr_for_table;
        $result_arr_mixed['graph_categories']=$result_arr_for_graph_categories;

        echo json_encode($result_arr_mixed);
    }

    public function hourly_all_show()
    {
        $per_geo_arr=\Config::get('custom.country_available_api_arr');
        $per_publisher=DB::table('publishers')->where('is_delete','=',0)->get();
        $per_api=DB::table('search_click_report')->groupby('api')->get();
        $per_jsver=DB::table('search_click_report')->groupby('jsver')->get();
        $per_widget=DB::table('search_click_report')->groupby('widget')->get();
        $per_browser=DB::table('search_click_report')->groupby('browser')->get();

        return view('pages.admin.analytics.hourly-all.index',compact('per_geo_arr','per_publisher','per_api','per_jsver','per_widget','per_browser'));
    }

    public function hourly_all_ajax(Request $request)
    {

        $result_arr = array();
        $input = $request->all();
        $group_by=$input['group_by'];

        $date = date('Y-m-d');
        $date_start = $input['date_start'];
        $date_exp = explode('-', $date_start);
        if (count($date_exp) == 3)
        {
            $mm = $date_exp[0];
            $dd = $date_exp[1];
            $yy = $date_exp[2];
            $date = $yy . '-' . $mm . '-' . $dd;
        }

        $query = DB::table('search_click_report');

        if(!empty($input['per_geo']))
            $query->where('country_code','=',$input['per_geo']);

        if(!empty($input['per_publisher']))
            $query->where('dl_source','=',$input['per_publisher']);

        if(!empty($input['per_api']))
            $query->where('api','=', $input['per_api']);

        if(!empty($input['per_jsver']))
            $query->where('jsver','=', $input['per_jsver']);

        if(!empty($input['per_widget']))
            $query->where('widget','=', $input['per_widget']);

        if(!empty($input['per_browser']))
            $query->where('browser','=', $input['per_browser']);

        $query->select('*', DB::raw('sum(total_clicked) as sum_total_clicks'), DB::raw('sum(total_search) as sum_total_searches'));
        $query->where('date', '=', $date);
        $query->groupBy('date');

        if($group_by!='')
	        $query->groupBy($group_by);

        $query->groupBy('hour_display');
        $query->orderBy('id','asc');

        $results=$query->get();

        $total_sum_clicks=0;
        $total_sum_searches=0;
        $title_arr=array();
        $result_arr_for_graph=array();
        $result_arr_for_table=array();
        $result_arr_for_graph_categories=array();
        $result_arr_mixed=array();

        foreach($results as $result)
        {
            $result_arr[$result->hour_display][$result->$group_by]['sum_total_clicks']=$result->sum_total_clicks;
            $result_arr[$result->hour_display][$result->$group_by]['sum_total_searches']=$result->sum_total_searches;
            $total_sum_clicks+=$result->sum_total_clicks;
            $total_sum_searches+=$result->sum_total_searches;
        }

        $j=0;
        foreach($result_arr as $key=>$value)
        {
            $result_arr_for_graph[$j]['hour_range']=$key;

            foreach($value as $key1=>$value1)
            {
                $result_arr_for_graph[$j][$key1]=intval($value1['sum_total_clicks']);

                $result_arr_for_table[] = array(
                    'hour_range'=>$key,
                    'total_all' =>$key1 ,
                    'field'=>$group_by,
                    "total_clicks" => $value1['sum_total_clicks'],
                    "total_searches" => $value1['sum_total_searches'],
                    "total_sum_clicks" => $total_sum_clicks,
                    "total_sum_searches" => $total_sum_searches
				);

                if ($key1 != '')
                {
                    if(!in_array($key1,$title_arr))
                        $title_arr[] = $key1;
                }
            }
            $j++;
        }

        for($i=0;$i<count($title_arr); $i++)
        {
            $result_arr_for_graph_categories[] = array(
                "bullet" => "round",
                "bulletBorderAlpha" => 1,
                "bulletColor" => "#FFFFFF",
                "bulletSize" => 5,
                "hideBulletsCount" => 30,
                "lineThickness" => 2,
                "title" => $title_arr[$i],
                "useLineColorForBulletBorder" => true,
                "valueField" => $title_arr[$i],
                "balloonText" => "<span style='font-size:18px;'>$title_arr[$i]:[[value]]</span>"
            );
        }

        $result_arr_mixed['graph_data']=$result_arr_for_graph;
        $result_arr_mixed['table_data']=$result_arr_for_table;
        $result_arr_mixed['graph_categories']=$result_arr_for_graph_categories;
        echo json_encode($result_arr_mixed);

    }



    public function clicks_ratio_show()
    {

        $advertiser_array=array('Ebay'=>'Ebay','Dealspricer'=>'Dealspricer');
        return view('pages.admin.analytics.clicks-ratio.index',compact('advertiser_array'));
    }

    public function clicks_ratio_ajax(Request $request)
    {

        $result_arr = array();
        $input = $request->all();
        $api=$input['api'];

        $date_start = $input['date_start'];
        $date_end = $input['date_end'];

        $date_start_exp = explode('-', $date_start);
        $date_end_exp = explode('-', $date_end);

        if (count($date_start_exp) == 3) {
            $mm = $date_start_exp[0];
            $dd = $date_start_exp[1];
            $yy = $date_start_exp[2];

            $date_start = $yy . '-' . $mm . '-' . $dd;
        }

        if (count($date_end_exp) == 3) {
            $mm = $date_end_exp[0];
            $dd = $date_end_exp[1];
            $yy = $date_end_exp[2];

            $date_end = $yy . '-' . $mm . '-' . $dd;
        }




        $results = DB::table('clicks_advertiser_internal')->where('date', '>=', $date_start)->where('date', '<=', $date_end)->where('api',$api)->get();


        $result_arr = array();
        $total_sum_ratio = 0;
        $total_avg_ratio = 0;
        $count=0;
        if(count($results)>0) {
            $count = count($results);
        }

        foreach ($results as $result) {

            $result_arr[$result->date]['total_ratio'] = $result->internal_clicks/$result->api_clicks;
            $total_sum_ratio+=$result->internal_clicks/$result->api_clicks;


        }
        if(count($results)>0) {
            $total_avg_ratio = $total_sum_ratio/$count;
        }
        $result_arr_mixed = array();

        foreach ($result_arr as $key => $value) {



            $result_arr_mixed[] = array('date' => $key, "total_ratio" => number_format((float)$value['total_ratio'],'2','.',''), "total_avg_ratio" => number_format((float)$total_avg_ratio,'2','.',''));


            //$result_arr_mixed=$result_arr+$result_arr_searches_info;

        }

        echo json_encode($result_arr_mixed);


    }
    public function clicked_keywords_show()
    {

        $advertiser_array=array('ebay'=>'ebay');
        return view('pages.admin.analytics.clicked-keywords.index',compact('advertiser_array'));
    }


    public function clicked_keywords_ajax(Request $request)
    {

        $result_arr = array();


        // DB columns array
        $columns=array(

            "id",
            "keyword",
            "clicks",
            "",
            ""


        );

        // local variables for POST variables for searching columns
        $date_start="";

        // Assigning POST values to local variables
        if($request->has('date_start') && $request->get('date_start')!=null) {
            $date_start = $request->get('date_start');
            //$date_start= DateHelper::dateStringToCarbon($date_start ,'m/d/Y');
            //$date_start =$date_start->format('Y-m-d');
            $date_start_exp = explode('-', $date_start);
            if (count($date_start_exp) == 3) {
                $mm = $date_start_exp[0];
                $dd = $date_start_exp[1];
                $yy = $date_start_exp[2];

                $date_start = $yy . '-' . $mm . '-' . $dd;
            }
        }
        $iDisplayLength = intval($request->get('length'));  // getting rows per page value for paging
        $iDisplayStart = intval($request->get('start'));    // getting offset value for paging
        $sEcho = intval($request->get('draw'));

        $query_order_array=$request->get('order', array(array('column'=>1,'dir'=>'asc')));
        $query_order_column=$query_order_array[0]['column'];
        $query_order_direction=$query_order_array[0]['dir'];

        // Building query for search
        $query = DB::table('search_clicks');
        $query ->select('*', DB::raw('sum(clicks) as total_clicks'));
        if($date_start!=null) {
            $query->where('date', $date_start);
        }
        else{
            $date_start=date("Y-m-d");
            $query->where('date',$date_start);

        }

        $query->groupBy('keyword');
        $sql=$query->toSql();
        $count = DB::table( DB::raw("($sql) as sub") )
            ->mergeBindings($query) // you need to get underlying Query Builder
            ->count();

        $iTotalRecords=$count;
        $query->orderBy('total_clicks','desc');
        // $query->orderBy('total_clicks');
        if($iDisplayLength>0)
            $query->limit($iDisplayLength)->offset($iDisplayStart);
        //getting searched records
        $keywords=$query->get();
        $i=0;
        $records = array();
        $records["data"] = array();
        $sub_total_clicks=0;
        $sub_total_clicks1=0;
        $total_rows= DB::table('search_clicks')->select('*',DB::raw('sum(clicks) as total_clicks1'))->where('date',$date_start)->get();
        foreach($total_rows as $total_row) {
            $sub_total_clicks+=$total_row->total_clicks1;
            $sub_total_clicks1+=$total_row->total_clicks1;

        }

        foreach($keywords as $keyword)
        {
            //echo  $keyword->date."\n".$keyword->keyword."\nclicks=" .$keyword->clicks."\ntotal_clicks=".$keyword->total_clicks."\n";
            // $sub_total_clicks+=$keyword->total_clicks;
            $records['data'][$i][]="<div style='word-break: break-all'>".$keyword->keyword."</div>";
            $records['data'][$i][]=number_format((float)($keyword->total_clicks/$sub_total_clicks)*100, 2, '.', '');
            $records['data'][$i][]=$keyword->total_clicks;
            $records['data'][$i][]='';

            $i++;
        }
        if(!empty($keyword)) {


            $records['data'][$i][] = '';
            $records['data'][$i][] = 'Total';
            $records['data'][$i][] = $sub_total_clicks1;
            $records['data'][$i][] = '';

        }
        if ($request->get("customActionType")!==null && $request->get("customActionType") == "group_action") {
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Group action successfully has been completed. Well done!"; // pass custom message(useful for getting status of group actions)
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        $records["sub_total_clicks"] = $sub_total_clicks1;
        return $records;
    }


    public function high_volume_website_show()
    {

        $per_geo_arr=\Config::get('custom.country_available_api_arr');
        $per_dl_source=Publisher::where('is_delete', '=', 0)->orderBy('name','asc')->get();

        return view('pages.admin.analytics.high-volume-website.index',compact('per_geo_arr','per_dl_source'));
    }

    public function high_volume_website_ajax(Request $request)
    {

		date_default_timezone_set('America/Los_Angeles');

		// DB columns array
        $columns = array(
            "domain",
            "widget",
            "",
            "day0",
            "",
            "",
	        ""
        );

        // local variables for POST variables for searching columns
        $date_start=date("Y-m-d");
        $geo="";
        $dl_source="";
        $domain_name="";

        // Assigning POST values to local variables
        if ($request->has('date_start') && $request->get('date_start') != null)
        {

            $date_start = $request->get('date_start');
            $date_start_exp = explode('-', $date_start);
            if (count($date_start_exp) == 3)
            {
                $mm = $date_start_exp[0];
                $dd = $date_start_exp[1];
                $yy = $date_start_exp[2];

                $date_start = $yy . '-' . $mm . '-' . $dd;
            }
        }

        $date_obj = Carbon::createFromFormat("Y-m-d", $date_start);
        $date_obj->hour   = 00;
		$date_obj->minute = 00;
		$date_obj->second = 00;


		$to_date=$date_obj->format('Y-m-d');
		$date_obj->subDays(29);
		$from_date=$date_obj->format('Y-m-d');


        if ($request->has('per_geo') && $request->get('per_geo') != null)
            $geo = $request->get('per_geo');

        if ($request->has('per_dl_source') && $request->get('per_dl_source') != null)
            $dl_source = $request->get('per_dl_source');

        if ($request->has('domain_name') && $request->get('domain_name') != null)
            $domain_name = $request->get('domain_name');


        $iDisplayLength = intval($request->get('length'));  // getting rows per page value for paging
        $iDisplayStart = intval($request->get('start'));    // getting offset value for paging
        $sEcho = intval($request->get('draw'));

        $query_order_array=$request->get('order', array(array('column'=>3,'dir'=>'desc')));
        $query_order_column=$query_order_array[0]['column'];
        $query_order_direction=$query_order_array[0]['dir'];


        $query = SearchClickHighVolumeWebsiteReport::selectWhereDateRangeForAdminGrid($from_date,$to_date);


		if($geo!=null)
			$query->where('country_code', $geo);

		if($dl_source!=null)
			 $query->where('dl_source', $dl_source);

		if($domain_name!=null)
			$query->where('domain','Like','%'. $domain_name.'%');


		$query->groupBy('domain');
        $query->groupBy('widget');


        $sql=$query->toSql();

        // for eloquent otherwise for query builder $sql1=$query;
        $sql1=$query->getQuery();


		// getting total records count
        $total_rows = DB::table( DB::raw("($sql) as sub") )
        ->mergeBindings($sql1) // you need to get underlying Query Builder
        ->count();


		// getting grand total clicks for current day
        $grand_total_clicks = DB::table( DB::raw("($sql) as sub") )
        ->mergeBindings($sql1) // you need to get underlying Query Builder
        ->sum('day0');


        $query->orderBy($columns[$query_order_column], $query_order_direction);

        if($iDisplayLength>0)
            $query->limit($iDisplayLength)->offset($iDisplayStart);

		$high_volumes=$query->get();

        $iTotalRecords=$total_rows;

		$i=0;
        $records = array();
        $records["data"] = array();

        foreach($high_volumes as $high_volume)
        {
            $high_volume_dl_source="";
            $high_volume_country_code="";

	        $records['data'][$i][]="<div style='word-break: break-all'>".$high_volume->domain."</div>";
			$records['data'][$i][]=$high_volume->widget;


			if(!empty($dl_source))
			    $high_volume_dl_source=$high_volume->dl_source;


			$records['data'][$i][]=$high_volume_dl_source;
			$records['data'][$i][]=$high_volume->day0;


			if(!empty($geo))
			    $high_volume_country_code = $high_volume->country_code;


			$records['data'][$i][] = $high_volume_country_code;
			$records['data'][$i][] ='<span class="spark" >'.$high_volume->last_thirty_days_clicks_csv_string.'</span>';
			$records['data'][$i][]='';

			$i++;

        }

        if(count($high_volumes)>0)
        {
            $records['data'][$i][] = '';
            $records['data'][$i][] = '';
            $records['data'][$i][] = "<b>".'Total'."<b>";
            $records['data'][$i][] = "<b>".$grand_total_clicks."<b>";
            $records['data'][$i][] = '';
            $records['data'][$i][] = '';
            $records['data'][$i][] = '';
        }

		$records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return $records;

    }
}
