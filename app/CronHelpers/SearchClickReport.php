<?php
namespace App\CronHelpers;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

$log_str='';

class SearchClickReport {

	private $todays_date='';
	private $todays_date_time='';

	public function __construct()
    {
        DB::enableQueryLog();

		//increase max execution time of this script to 150 min:
        ini_set('max_execution_time', 9000);
        //increase Allowed Memory Size of this script:
        ini_set('memory_limit','960M');

        set_time_limit(0);

        SearchClickReport::echo_printr("");
        SearchClickReport::echo_printr("====================================================================================================");
        SearchClickReport::echo_printr("Date before setting default timezone ".date("j F, Y, g:i a"));

        date_default_timezone_set('America/Los_Angeles');

        SearchClickReport::echo_printr("Date after setting default timezone  ".date("j F, Y, g:i a"));
        SearchClickReport::echo_printr("Start SearchClickReport init at ".date("j F, Y, g:i a"));

        $this->todays_date=date("Y-m-d");
        $this->todays_date_time=date("Y-m-d H:i:s");
        //$this->todays_date_time="2016-08-24 23:00:00";
    }

    function __destruct()
    {
		global $log_str;

        $logDir=storage_path('logs/search-click-report');

        if (!File::exists($logDir))
        {
            SearchClickReport::echo_printr("search-click-report folder not exists");
            $result = File::makeDirectory($logDir, 0775, true, true);
        }
        else
        {
            SearchClickReport::echo_printr("search-click-report folder exists");
        }

		SearchClickReport::echo_printr("====================================================================================================");
        SearchClickReport::echo_printr("");

        echo $log_str;

        $log_str=str_replace("<pre>","",$log_str);
        $log_str=str_replace('<br />',"\n",$log_str);

        $logFile = $logDir.'/'.date("Y-m-d-H",strtotime('-1 hour',strtotime($this->todays_date_time))).'_search-click-report_cronlog.txt';

        $fp = fopen($logFile, 'a+');
        fwrite($fp, $log_str);
        fclose($fp);
    }

    public function init()
    {
		$result_arr=array();
		SearchClickReport::echo_printr("Todays date time = ".date("j F, Y, g:i a",strtotime($this->todays_date_time)));

		$previous_hour_date_time=date("Y-m-d H:i:s",strtotime('-1 hour',strtotime($this->todays_date_time)));

		SearchClickReport::echo_printr("Current hour date time = ".date("j F, Y, g:i a",strtotime($this->todays_date_time)));
		SearchClickReport::echo_printr("Previous hour date time = ".date("j F, Y, g:i a",strtotime($previous_hour_date_time)));

		$last_hour_index= idate('H', strtotime($previous_hour_date_time));

		SearchClickReport::echo_printr("last_hour_index = ".$last_hour_index);

		$search_apis_arr=array(
			'connexity'=>'connexity',
			'dealspricer'=>'dealspricer',
			'ebay_commerce_network'=>'ebay',
			'kelkoo'=>'kelkoo',
			'twenga'=>'twenga',
			'N/A'=>'N/A'
		);

		DB::listen(
		    function ($sql) {

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


				//echo $query;
				//SearchClickReport::echo_printr($query,"query");
		    }
		);



        $results = DB::table('search_clicks as a')
        ->select(
            DB::raw('CONCAT(LPAD(HOUR(created_at),2,"0"), ":00 - ",  LPAD(HOUR(created_at)+1,2,"0"), ":00") as hour_range ')  ,
            DB::raw('count(id) as total_clicks'),
            DB::raw('HOUR(created_at) as hour_created_at'),
			DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date1'),
	        DB::raw('IF(a.dl_source IS NULL or a.dl_source = "", "N/A", a.dl_source) as dl_source '),
			DB::raw('IF(a.sub_dl_source IS NULL or a.sub_dl_source = "", "N/A", a.sub_dl_source) as sub_dl_source '),
			DB::raw('IF(a.widget IS NULL or a.widget = "", "N/A", a.widget) as widget '),
			DB::raw('IF(a.country_code IS NULL or a.country_code = "", "N/A", a.country_code) as country_code '),
			DB::raw('IF(a.api IS NULL or a.api = "", "N/A", a.api) as api '),
			DB::raw('IF(a.jsver IS NULL or a.jsver = "", "N/A", a.jsver) as jsver '),
	        DB::raw('IF(a.browser IS NULL or a.browser = "", "N/A", a.browser) as browser ')
        )
        ->where(DB::raw('HOUR(created_at)'),'=', $last_hour_index)
        ->where(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d")'),'=', date('Y-m-d',strtotime($previous_hour_date_time)))
        ->groupBy('dl_source')
        ->groupBy('sub_dl_source')
        ->groupBy('widget')
        ->groupBy('country_code')
        ->groupBy('api')
        ->groupBy('jsver')
        ->groupBy('browser')
        ->groupBy('date1')
        ->groupBy('hour_range')
        ->orderBy('hour_created_at','asc')
        ->get();

        SearchClickReport::echo_printr(count($results),"count");

        foreach($results as $result)
        {
			$dl_source=$result->dl_source;
			$sub_dl_source=$result->sub_dl_source;
			$widget=$result->widget;
			$country_code=$result->country_code;
			$api=$result->api;
			$jsver=$result->jsver;
			$browser=$result->browser;

			$result_arr[$result->date1][$result->hour_created_at][$result->hour_range][$dl_source][$sub_dl_source][$widget][$api][$country_code][$jsver][$browser]['clicks']=$result->total_clicks;

        }

        //SearchClickReport::echo_printr($result_arr,"result_arr");

        foreach($result_arr as $k1=>$v1)
	    {
	        foreach($v1 as $k2=>$v2)
		    {
		        foreach($v2 as $k3=>$v3)
			    {
					foreach($v3 as $k4=>$v4)
				    {
						foreach($v4 as $k5=>$v5)
					    {
							foreach($v5 as $k6=>$v6)
						    {
								foreach($v6 as $k7=>$v7)
							    {
									foreach($v7 as $k8=>$v8)
								    {
										foreach($v8 as $k9=>$v9)
									    {
									        foreach($v9 as $k10=>$v10)
									        {
										        if (!isset($v10['clicks']))
										        {

											        $result_arr[$k1][$k2][$k3][$k4][$k5][$k6][$k7][$k8][$k9][$k10]['clicks'] = 0;
										        }
										        elseif (isset($v10['clicks']) && trim($v10['clicks']) == "")
										        {
											        $result_arr[$k1][$k2][$k3][$k4][$k5][$k6][$k7][$k8][$k9][$k10]['clicks'] = 0;
										        }

										        if (!isset($v10['searches']))
										        {

											        $result_arr[$k1][$k2][$k3][$k4][$k5][$k6][$k7][$k8][$k9][$k10]['searches'] = 0;
										        }
										        elseif (isset($v10['searches']) && trim($v10['searches']) == "")
										        {
											        $result_arr[$k1][$k2][$k3][$k4][$k5][$k6][$k7][$k8][$k9][$k10]['searches'] = 0;
										        }

										        $search_click_report_input['date'] = $k1;
										        $search_click_report_input['hour_int'] = $k2;
										        $search_click_report_input['hour_display'] = $k3;
										        $search_click_report_input['dl_source'] = $k4;
										        $search_click_report_input['sub_dl_source'] = $k5;
										        $search_click_report_input['widget'] = $k6;
										        $search_click_report_input['api'] = $k7;
										        $search_click_report_input['country_code'] = $k8;
										        $search_click_report_input['jsver'] = $k9;
										        $search_click_report_input['browser'] = $k10;
										        $search_click_report_input['total_search'] = $result_arr[$k1][$k2][$k3][$k4][$k5][$k6][$k7][$k8][$k9][$k10]['searches'];
										        $search_click_report_input['total_clicked'] = $result_arr[$k1][$k2][$k3][$k4][$k5][$k6][$k7][$k8][$k9][$k10]['clicks'];

										        $publisher=\App\SearchClickReport::create($search_click_report_input);
									        }
									    }
								    }
							    }
						    }
					    }
				    }
			    }
		    }
	    }
	    SearchClickReport::echo_printr($result_arr,"result_arr");
    }

    public static function echo_printr($val='',$label='')
    {
        global $log_str;

        $tmp_str="";

        if(is_array($val) || is_object($val))
        {
            if($label!='')
                $tmp_str.=$label."\n";
            $tmp_str.=print_r($val,true)."\n\n";
        }
        else
        {
            if($label!='')
                $tmp_str.=$label." = ";
            $tmp_str.=$val."\n\n";
        }

        if (\App::runningInConsole())
        {
            //echo "I'm in the console, baby!";
        }
        else
        {
            if(trim($log_str)=="")
                $log_str.="<pre>";

            $tmp_str = str_replace("\r", "", $tmp_str);  // Remove \r
            $tmp_str = str_replace("\n", "<br />", $tmp_str);  // Replace \n with <br />
        }
        $log_str.=$tmp_str;
    }
}