<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\Helpers\EmailHelpers;
use Illuminate\Http\Request;
use File;
use DB;
use App\Http\Requests;
use App\Publisher;
use Excel;
use App\Http\Controllers\Controller;

class CsvReportController extends Controller
{
    /**
     * when we load the page this function is call
     *fetch data and show in the table from search_clicks table
     *use tow filter api and month which show data according month and api
     * use one button like (send daily report) which show popup that  we can use sending email and download csv file
     */
    public function index()
    {
        $publishers=Publisher::where('is_delete','=',0)->groupBy('name')->orderby('name','asc')->get();
        return view('pages.admin.csv-report.index')->with('publishers',$publishers);
    }
    /*
     * Show DataTable
     * @param request getting all post variable
     * return @param records
     */
    public function api_show(Request $request){

        //increase max execution time of this script to 150 min:
        ini_set('max_execution_time',600 );
        //increase Allowed Memory Size of this script:
        ini_set('memory_limit','2G');
        set_time_limit(0);
        error_reporting(E_ALL);
        ini_set('display_errors',1);

        // DB columns array
        $columns=array(
            'date',
            'dl_source',
            'sub_dl_source',
            'widget',
            'country_code',
            '',
            'clicks',
            '',
        );

        // local variables for POST variables for searching columns
        $search_month="";
        $search_api="";
        $search_publisher="";
        if($request->has('search_month') && $request->get('search_month')!=null)
            $search_month=trim($request->get('search_month'));

        if($request->has('search_api') && $request->get('search_api')!=null)
            $search_api=trim($request->get('search_api'));

        if($request->has('search_publisher') && $request->get('search_publisher')!=null)
            $search_publisher=trim($request->get('search_publisher'));

        //$current_year = date("Y");//getting current year
        $current_year = 2016;//getting current year
        $query=DB::table('search_clicks');// Building query for search


        $iDisplayLength = intval($request->get('length'));  // getting rows per page value for paging
        $iDisplayStart = intval($request->get('start'));    // getting offset value for paging
        $sEcho = intval($request->get('draw'));

        $query_order_array=$request->get('order', array(array('column'=>1,'dir'=>'asc')));
        $query_order_column=$query_order_array[0]['column'];
        $query_order_direction=$query_order_array[0]['dir'];


        if($search_api!=null) {

            $query->where('api',$search_api);
        }
        if($search_month!=null) {

            $query->whereRaw('extract(month from date) = ?', [$search_month]);
        }
        if($search_publisher!=null) {

            $query->where('dl_source',$search_publisher);
        }



        $query->whereRaw('extract(year from date) = ?', [$current_year]);
        $query->select('date','dl_source','sub_dl_source','widget','country_code',DB::raw('sum(clicks)as clicks' ));
        $query->groupby('api','widget' ,'date','country_code','dl_source','sub_dl_source');
        $query->orderby('date');


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

        $results=$query->get();

        $i=0;
        $records = array();
        $records["data"] = array();
        $records["date"]=array();
        foreach($results as $result)
        {

            $records["date"][$i][]=$result->date;
            $records['data'][$i][]=$result->date;
            $records['data'][$i][]= $result->dl_source;
            $records['data'][$i][]= "<div style='word-break: break-all'>".$result->sub_dl_source."</div>";
            $records['data'][$i][]= $result->widget;
            $records['data'][$i][]= $result->country_code;
            $records['data'][$i][]= '';
            $records['data'][$i][]= $result->clicks;
            $records['data'][$i][]= '';
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

    /**
     * Download Report
     * @param request getting  all post variable
     *create csv report file  for using Maatawebsite pluging
     */
    public function csv_download(Request $request){
DB::listen(
		    function ($sql)
		    {
		        // $sql is an object with the properties:
		        //  sql: The query
		        //  bindings: the sql query variables
		        //  time: The execution time for the query
		        //  connectionName: The name of the connection

		        // To save the executed queries to file:
		        // Process the sql and the bindings:
		        foreach ($sql->bindings as $i => $binding) {
		            if ($binding instanceof \DateTime) {
		                $sql->bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
		            } else {
		                if (is_string($binding)) {
		                    $sql->bindings[$i] = "'$binding'";
		                }
		            }
		        }

		        // Insert bindings into query
		        $query = str_replace(array('%', '?'), array('%%', '%s'), $sql->sql);

		        $query = vsprintf($query, $sql->bindings);

		        echo "\n".$query."\n";

		    }
		);
        //increase max execution time of this script to 150 min:
        ini_set('max_execution_time',600 );
        //increase Allowed Memory Size of this script:
        ini_set('memory_limit','2G');
        set_time_limit(0);

        //$current_year = date("Y");//getting current year
        $current_year = 2016;//getting current year
        $export_array=array();//define array varaible for using heading custom column name table
        $export_array[]=array('Date','Dl_Source','Sub_Dl_Source','Widget','Country Code','Searches','Clicks','Estimated_Revenue_In_USD' );

        $query=DB::table('search_clicks');
        if($request->has('api') && $request->get('api')!=null) {
            $api=$request->get('api');
            $query->where('api',$api);
        }
        if($request->has('month') && $request->get('month')!=null) {
            $month=$request->get('month');
            $query->whereRaw('extract(month from date) = ?', [$month]);
        }
        if($request->has('publisher') && $request->get('publisher')!=null) {
            $publisher=$request->get('publisher');
            $query->where('dl_source',$publisher);
        }
        $query->whereRaw('extract(year from date) = ?', [$current_year]);
        $query->select('date','dl_source','sub_dl_source','widget','country_code',DB::raw('sum(clicks)as clicks' ));
        $query->groupby('api','widget' ,'date','country_code','dl_source','sub_dl_source');
        $query->orderby('date');
        $exports=$query->get();
        foreach($exports as $export){
            $export_array[]=array($export->date,$export->dl_source,$export->sub_dl_source,$export->widget,$export->country_code,'',$export->clicks,'');
        }

        Excel::create('csv-report',function($excel) use($export_array) {
            $excel->sheet('Sheet 1',function($sheet) use($export_array){
                $sheet->fromArray($export_array,null,'A1',false,false);
            });
        })->download('csv');//download csv file on popup

    }
    /**
     * sending email
     * @param Request $request getting  all post variable in array
     *create csv report file  for using Maatawebsite pluging
     * sending email using to call sendEmailToCsvReport();
     */
    public function csv_email_send(Request $request){

        //increase max execution time of this script to 150 min:
        ini_set('max_execution_time',600 );
        //increase Allowed Memory Size of this script:
        ini_set('memory_limit','2G');
        set_time_limit(0);

        $email_array['msg']='there is error';//define message not sending email
        $email_array['success']=0;
        $csv_report_file_name='';
        $current_year = date("Y");//getting current year
        $export_array=array();//define array varaible for using heading custom column name table
        $export_array[]=array('Date','Dl_Source','Sub_Dl_Source','Widget','Country Code','Searches','Clicks','Estimated_Revenue_In_USD' );

        $query=DB::table('search_clicks');
        if($request->has('email') && $request->get('email')!=null) {
            $email=$request->get('email');
            $email_array=explode(',',$email);//seprate email without comma seprate using explode()
        }

        if($request->has('api') && $request->get('api')!=null) {
            $api=$request->get('api');
            $query->where('api',$api);
        }
        if($request->has('month') && $request->get('month')!=null) {
            $month=$request->get('month');
            $csv_report_file_name = 'csv-report_' . $api . '_2016_' . $month;//define csv file name month
            $query->whereRaw('extract(month from date) = ?', [$month]);
        }

        if($request->has('publisher') && $request->get('publisher')!=null) {
            $publisher=$request->get('publisher');
            $query->where('dl_source',$publisher);
        }

        $query->whereRaw('extract(year from date) = ?', [$current_year]);
        $query->select('date','dl_source','sub_dl_source','widget','country_code',DB::raw('sum(clicks)as clicks' ));
        $query->groupby('api','widget' ,'date','country_code','dl_source','sub_dl_source');
        $query->orderby('date');
        $exports=$query->get();

        $path=storage_path()."/files/csv/search_clicks/";//define csv file path
        File::makeDirectory($path, $mode = 0777, true, true);//create csv report file folder
        if($csv_report_file_name!='') {
            $csv_report_path = $path . $csv_report_file_name . '.csv';//csv report file path
        }
        else{
            $csv_report_file_name='csv-report_'.$api.'_all_2016';//define csv file name
            $csv_report_path = $path . $csv_report_file_name . '.csv';//csv report file path

        }
        foreach($exports as $export){
            $export_array[]=array($export->date,$export->dl_source,$export->sub_dl_source,$export->widget,$export->country_code,'',$export->clicks,'');
        }

        Excel::create($csv_report_file_name,function($excel) use($export_array) {
            $excel->sheet('Sheet 1',function($sheet) use($export_array){
                $sheet->fromArray($export_array,null,'A1',false,false);
            });
        })->store('csv',$path);

        if(count($email_array)>0) {
            $email_arr=array();
            for($i=0;$i<count($email_array);$i++){
                if(trim($email_array[$i])!=''){
                    $email_arr[]=$email_array[$i];
                }
            }
            for($i=0;$i<count($email_arr);$i++){
                $email_to=$email_arr[$i];
                $subject='CSV-Report';
                $body='CSV-Report';
                //if(EmailHelpers::sendEmailToCsvReport($email_to,$csv_report_path,$subject,$body)) {//using sending email to call sendEmailToCsvReport()
                    $email_array['msg']='Email sent successfully';
                    $email_array['success']=1;
                //}
                //else{
                    $email_array['msg']='there is error';
                    $email_array['success']=0;
                //}
            }
        }
        echo json_encode($email_array);
    }
}