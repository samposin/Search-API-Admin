<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\Helpers\EmailHelpers;
use Illuminate\Http\Request;
use File;
use DB;
use DateTime;
use App\Http\Requests;
use App\Publisher;
use Excel;
use Touki\FTP\Connection\Connection;
use Touki\FTP\FTPFactory;
use Touki\FTP\FTPWrapper;
use Touki\FTP\Model\Directory;
use App\Http\Controllers\Controller;
use App\SearchClick;

class TwengaController extends Controller
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
        return view('pages.admin.twenga.index')->with('publishers',$publishers);
    }
    /*
     * Show DataTable
     * @param request getting all post variable
     * return @param records
     */
    public function show(Request $request){

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
            'estimated_revenue',
        );

        // local variables for POST variables for searching columns
        $search_month="";
       // $search_api="";
        $search_publisher="";
        if($request->has('search_month') && $request->get('search_month')!=null)
            $search_month=trim($request->get('search_month'));

       /* if($request->has('search_api') && $request->get('search_api')!=null)
            $search_api=trim($request->get('search_api'));*/

        if($request->has('search_publisher') && $request->get('search_publisher')!=null)
            $search_publisher=trim($request->get('search_publisher'));

        $current_year = date("Y");//getting current year
        $query=DB::table('twenga_report');// Building query for search


        $iDisplayLength = intval($request->get('length'));  // getting rows per page value for paging
        $iDisplayStart = intval($request->get('start'));    // getting offset value for paging
        $sEcho = intval($request->get('draw'));

        $query_order_array=$request->get('order', array(array('column'=>1,'dir'=>'asc')));
        $query_order_column=$query_order_array[0]['column'];
        $query_order_direction=$query_order_array[0]['dir'];


       /* if($search_api!=null) {

            $query->where('api',$search_api);
        }*/
        if($search_month!=null) {

            $query->whereRaw('extract(month from date) = ?', [$search_month]);
        }
        if($search_publisher!=null) {

            $query->where('dl_source',$search_publisher);
        }



        $query->whereRaw('extract(year from date) = ?', [$current_year]);
        $query->select('date','dl_source','sub_dl_source','widget','country_code',DB::raw('round(sum(clicks))as clicks'),DB::raw('round(sum(estimated_revenue),2) as estimated_revenue'));
        $query->groupby('widget' ,'date','country_code','dl_source','sub_dl_source');
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
            $records['data'][$i][]= $result->estimated_revenue;;
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
     * Download Twenga csv file from server FTP
     * @param request getting  all post variable
     *
     */
    public function twenga_generate_daily_report(Request $request){

        $response['success']=0;

       $yesterDayDate = (new DateTime(date('Y-m-d')))->modify('-1 days');
        $yesterDayDateFormate=$yesterDayDate->format('Y-m-d');
        $results=DB::table('twenga_report')->select('date')->where('date',$yesterDayDateFormate)->get();
        if(count($results)>0){

            $response['success']=1;
            $response['message']="Already Exit  Today Report";
            return $response;

        }

        $connection = new Connection('ftp-01.twenga.com', 'Ileviathan', 'g3kgc2u983Jk', $port = 21, $timeout = 90, $passive = true);
        $connection->open();
        $factory = new FTPFactory;
        $ftp = $factory->build($connection);
        $list = $ftp->findFilesystems(new Directory("/subid"));
        $ftp_file_arr=array();
        foreach($list as $l)
        {
            $realPath=$l->getRealpath();
            $pathinfo=pathinfo($realPath);
            $fileName=$pathinfo['filename'];
            $ext=$pathinfo['extension'];
            $fileNameExp=explode('_',$fileName);
            $date=$fileNameExp[2];
            $ftp_file_arr[]=array("real_path"=>$realPath,"datetime"=>$date.' 00:00:00');
        }

        if(count($ftp_file_arr)==0)
        {
            $ftp_file_arr[0]=array();
        }

        usort($ftp_file_arr, array("App\Http\Controllers\Admin\TwengaController", "date_compare"));//sort latest file

        $ftp_file_arr[0];//get latest file directory

        $real_path_file_name=$ftp_file_arr[0]['real_path'];//get file directory name

        if(!empty($real_path_file_name)) {
            $file_real_path=$this->downloadFileFromFtp($ftp, $real_path_file_name);
            if($file_real_path){

               $response=$this->twengaCsvFileAndSaveInDB($file_real_path);

            }
        }
        else{

            $response['success']='0';
            $response['message']='Not found latest file';
        }

    echo  json_encode($response);
           $connection->close();
    }
    /**
     * Comapre Date latest file Twenga csv file
     *
     */
    function date_compare($a, $b)
    {

        $t1 = strtotime($a['datetime']);
        $t2 = strtotime($b['datetime']);

        return $t2 - $t1;
    }
    /**
     * Download file to store  server Path
     * @param ftp to connction ftp server
     * @param real_path_file_name server ftp file name
     * return response true/false
     */
    function downloadFileFromFtp($ftp,$real_path_file_name){

        $real_path_exp=explode('/',$real_path_file_name);//seprate file name
        $twenga_file_name =$real_path_exp[2];//get file name
        $file= $ftp->findFileByName($real_path_file_name);//gey object file name
        $file_ftp_twenga_folder=base_path() . '/public/files/ftp/twenga/';//file upload path
        File::makeDirectory($file_ftp_twenga_folder, $mode = 0777, true, true);//create directory
        $file_real_path=$file_ftp_twenga_folder.$twenga_file_name;//file upload path with file name
        $ftp->download($file_real_path,$file);//download file from ftp twenga server and upload file folder
        return $file_real_path;
    }

    /**
     * get downlaod file from  server Path to save DB
     * @param file_real_path server file path
     * @param real_path_file_name server file path
     *
     */
    function twengaCsvFileAndSaveInDB($file_real_path){

        $results_twenga_db='';
        $twenga_csv_file = $this->readTwengaCsvFile($file_real_path);

        for ($i = 1; $i <count($twenga_csv_file); $i++) {

            if ($twenga_csv_file[$i][0]!= '') {

                $symbol = 'EUR';
                $subid = $twenga_csv_file[$i][2];
                $date = $twenga_csv_file[$i][0];
                $revenue = $twenga_csv_file[$i][7];
                $country = $twenga_csv_file[$i][1];
                $clicks = $twenga_csv_file[$i][3];

                $data_arr_currency_rate=array('from_currency'=>'USD','to_currency'=>$symbol,'date'=>$date);

                $results=DB::table('currency_exchange_rates')->select('*')->where($data_arr_currency_rate)->get();

                if(count($results)>0) {
                    foreach ($results as $result) {

                       $exchange_rate = $result->rate;
                    }

                    if ($exchange_rate != '0' && $exchange_rate != '') {
                        $revenue = $revenue / $exchange_rate;
                    }
                }
                else{

                    $response['success']='0';
                    $response['message']='please import latest table  Currency_Exchange_Rate';
                    return $response;

                }
                if($clicks!=0 && $clicks!='') {
                    $revenu_per_click = $revenue / $clicks;
                }
                if ($subid != 'default') {

                    $publishers='';

                    $data_arr_publishers=array('publishers.is_delete'=>'0', 'advertisers_publishers.publisher_id1'=>$subid);
                    $results_publishers = DB::table('advertisers_publishers')->select('publishers.name')->leftJoin('publishers', 'advertisers_publishers.publisher_id', '=', 'publishers.id')->where($data_arr_publishers)->get();
                    foreach ($results_publishers as $result_publisher ) {

                      $publishers = $result_publisher->name;
                    }

                    $data_arr_search_clicks=array('api'=>'twenga','date'=>$date,'dl_source'=>$publishers,'country_code'=>$country);

                    $results_total_sum_search_clicks =DB::table('search_clicks')->select(DB::raw('sum(clicks)as sum_total_clicks'))->where($data_arr_search_clicks)->get();

                    if(count($results_total_sum_search_clicks[0]->sum_total_clicks)>0){


                        foreach($results_total_sum_search_clicks as $result_total_sum_search_clicks) {

                            $sum_total_clicks = $result_total_sum_search_clicks->sum_total_clicks;
                            if($sum_total_clicks!=0) {
                                $click_ratio = $clicks / $sum_total_clicks;
                            }
                            $results_sum_search_clicks =DB::table('search_clicks')->select('*',DB::raw('sum(clicks)as sum_of_clicks_db'))->where($data_arr_search_clicks)->groupby('api','dl_source','sub_dl_source','widget','country_code','date')->get();
                            foreach($results_sum_search_clicks as $result_sum_search_clicks) {


                                $country_code = $result_sum_search_clicks->country_code;
                                $dl_source = $result_sum_search_clicks->dl_source;
                                $sub_dl_source = $result_sum_search_clicks->sub_dl_source;
                                $widget = $result_sum_search_clicks->widget;
                                $datedb = $result_sum_search_clicks->date;
                                $clicksdb = ($result_sum_search_clicks->sum_of_clicks_db * $click_ratio);
                                $estimated_revenue = ($result_sum_search_clicks->sum_of_clicks_db * $click_ratio * $revenu_per_click);


                                $data_arr_twenga_db = array('date' => $datedb, 'dl_source' => $dl_source, 'sub_dl_source' => $sub_dl_source, 'widget' => $widget, 'country_code' => $country_code, 'clicks' => $clicksdb, 'estimated_revenue' => $estimated_revenue);
                                $results_twenga_db = DB::table('twenga_report')->insert($data_arr_twenga_db);
                            }
                        }
                    }
                    else{

                        $response['success']='0';
                        $response['message']='please import latest table  Search_Clicks';
                        return $response;
                    }
                }
                else{

                    $data_arr_search_clicks=array('api'=>'twenga','date'=>$date,'country_code'=>$country);

                    $results_total_sum_search_clicks =DB::table('search_clicks')->select(DB::raw('sum(clicks)as sum_total_clicks'))->where($data_arr_search_clicks)->get();

                    if(count($results_total_sum_search_clicks)>0){

                        foreach($results_total_sum_search_clicks as $result_total_sum_search_clicks) {

                            $sum_total_clicks = $result_total_sum_search_clicks->sum_total_clicks;
                            if($sum_total_clicks!=0) {
                                $click_ratio = $clicks / $sum_total_clicks;
                            }

                            $results_sum_search_clicks = DB::table('search_clicks')->select('*', DB::raw('sum(clicks)as sum_of_clicks_db'))->where($data_arr_search_clicks)->groupby('api', 'dl_source', 'sub_dl_source', 'widget', 'country_code', 'date')->get();


                            foreach ($results_sum_search_clicks as $result_sum_search_clicks) {


                                $country_code = $result_sum_search_clicks->country_code;
                                $dl_source = $result_sum_search_clicks->dl_source;
                                $sub_dl_source = $result_sum_search_clicks->sub_dl_source;
                                $widget = $result_sum_search_clicks->widget;
                                $datedb = $result_sum_search_clicks->date;
                                $clicksdb = ($result_sum_search_clicks->sum_of_clicks_db * $click_ratio);
                                $estimated_revenue = ($result_sum_search_clicks->sum_of_clicks_db * $click_ratio * $revenu_per_click);


                                $data_arr_twenga_db = array('date' => $datedb, 'dl_source' => $dl_source, 'sub_dl_source' => $sub_dl_source, 'widget' => $widget, 'country_code' => $country_code, 'clicks' => $clicksdb, 'estimated_revenue' => $estimated_revenue);
                                $results_twenga_db = DB::table('twenga_report')->insert($data_arr_twenga_db);

                            }
                        }

                    }
                    else{

                        $response['success']='0';
                        $response['message']='please import latest table  Search_Clicks';
                        return $response;
                    }
                }
            }
        }

        if ($results_twenga_db) {

            $response['success'] = '1';
            $response['message'] = 'Report Generate successfull';
            return $response;


        }
        else {

            $response['success'] = '0';
            $response['message'] = 'Report not Generate successfull';
            return $response;


        }
    }
   /**
    * read twenga csv file
    * return file
    **/
    function readTwengaCsvFile($file_real_path)
    {
        $file_handle = fopen($file_real_path, 'r');
        while (!feof($file_handle)) {
            $line_of_text[] = fgetcsv($file_handle, 0, "\t");
        }
        fclose($file_handle);
        return $line_of_text;
    }

    /**
     * Download Report
     * @param request getting  all post variable
     *create csv report file  for using Maatawebsite pluging
     */
    public function twenga_download(Request $request){

        //increase max execution time of this script to 150 min:
        ini_set('max_execution_time',600 );
        //increase Allowed Memory Size of this script:
        ini_set('memory_limit','2G');
        set_time_limit(0);

        $current_year = date("Y");//getting current year
        $export_array=array();//define array varaible for using heading custom column name table
        $export_array[]=array('Date','Dl_Source','Sub_Dl_Source','Widget','Country Code','Searches','Clicks','Estimated_Revenue_In_USD' );

        $query=DB::table('twenga_report');
        /*if($request->has('api') && $request->get('api')!=null) {
            $api=$request->get('api');
            $query->where('api',$api);
        }*/
        if($request->has('month') && $request->get('month')!=null) {
            $month=$request->get('month');
            $query->whereRaw('extract(month from date) = ?', [$month]);
        }
        if($request->has('publisher') && $request->get('publisher')!=null) {
            $publisher=$request->get('publisher');
            $query->where('dl_source',$publisher);
        }
        $query->whereRaw('extract(year from date) = ?', [$current_year]);
        $query->select('date','dl_source','sub_dl_source','widget','country_code',DB::raw('round(sum(clicks))as clicks'),DB::raw('round(sum(estimated_revenue),2) as estimated_revenue'));
        $query->groupby('widget' ,'date','country_code','dl_source','sub_dl_source');
        $query->orderby('date');
        $exports=$query->get();
        foreach($exports as $export){
            $export_array[]=array($export->date,$export->dl_source,$export->sub_dl_source,$export->widget,$export->country_code,'',$export->clicks,$export->estimated_revenue);
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
    public function twenga_email_send(Request $request){

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

        $query=DB::table('twenga_report');
        if($request->has('email') && $request->get('email')!=null) {
            $email=$request->get('email');
            $email_array=explode(',',$email);//seprate email without comma seprate using explode()
        }

        /*if($request->has('api') && $request->get('api')!=null) {
            $api=$request->get('api');
            $query->where('api',$api);
        }*/
        if($request->has('month') && $request->get('month')!=null) {
            $month=$request->get('month');
            $csv_report_file_name = 'csv-report_twenga_2016_' . $month;//define csv file name month
            $query->whereRaw('extract(month from date) = ?', [$month]);
        }

        if($request->has('publisher') && $request->get('publisher')!=null) {
            $publisher=$request->get('publisher');
            $query->where('dl_source',$publisher);
        }

        $query->whereRaw('extract(year from date) = ?', [$current_year]);
        $query->select('date','dl_source','sub_dl_source','widget','country_code',DB::raw('round(sum(clicks))as clicks'),DB::raw('round(sum(estimated_revenue),2) as estimated_revenue'));
        $query->groupby('widget' ,'date','country_code','dl_source','sub_dl_source');
        $query->orderby('date');
        $exports=$query->get();

        $path=storage_path()."/files/csv/search_clicks/";//define csv file path
        File::makeDirectory($path, $mode = 0777, true, true);//create csv report file folder
        if($csv_report_file_name!='') {
            $csv_report_path = $path . $csv_report_file_name . '.csv';//csv report file path
        }
        else{
            $csv_report_file_name='csv-report_twenga_all_2016';//define csv file name
            $csv_report_path = $path . $csv_report_file_name . '.csv';//csv report file path

        }
        foreach($exports as $export){
            $export_array[]=array($export->date,$export->dl_source,$export->sub_dl_source,$export->widget,$export->country_code,'',$export->clicks,$export->estimated_revenue);
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