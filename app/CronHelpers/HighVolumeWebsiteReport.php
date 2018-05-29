<?php namespace App\CronHelpers;

use App\Helpers\LogCustom;
use App\SearchClick;
use App\SearchClickHighVolumeWebsiteReport;
use Illuminate\Support\Facades\DB;

class HighVolumeWebsiteReport {

	private $todays_date='';
	private $todays_date_time='';


	public function __construct()
    {
        LogCustom::query_listen();

        ini_set('max_execution_time', 18000); //300 min
        ini_set('memory_limit','2G');
        set_time_limit(0);

        date_default_timezone_set('America/Los_Angeles');

        $this->todays_date=date("Y-m-d");
        $this->todays_date_time=date("Y-m-d H:i:s");
        //$this->todays_date_time="2016-08-26 00:00:05";

    }


    function __destruct()
    {
    	$log_dir_name='high-volume-website-report';
		$log_file_name = date("Y-m-d",strtotime('-1 day',strtotime($this->todays_date_time))).'_high_volume_website_report_cronlog.txt';

		// Save log in file
		LogCustom::save_log($log_dir_name,$log_file_name);

		// echo log
		echo LogCustom::get_log();
    }


	/**
	 * This is initial function called from command
	 *
	 */

	public function init()
    {
        LogCustom::log_array_string("Todays date time = ".date("j F, Y, g:i a",strtotime($this->todays_date_time)));

		$yesterdays_date_time=date("Y-m-d H:i:s",strtotime('-1 day',strtotime($this->todays_date_time)));

		LogCustom::log_array_string("Yesterday date time = ".date("j F, Y, g:i a",strtotime($yesterdays_date_time)));

		$this->processDataFromSearchClick($yesterdays_date_time);
    }


	/**
	 * This function take result from search_click table for a date provided
	 * and store in search_click_high_volume_website_report table
	 *
	 * @param $date_time  //format:Y-m-d H:i:s
	 */

	public function processDataFromSearchClick($date_time)
    {
        $query = DB::table('search_clicks as a');
        $query->select(
            DB::raw('sum(clicks) as total_clicks'),
			DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date1'),
	        DB::raw('IF(a.dl_source IS NULL or a.dl_source = "", "N/A", a.dl_source) as dl_source '),
			DB::raw('IF(a.widget IS NULL or a.widget = "", "N/A", a.widget) as widget '),
			DB::raw('IF(a.country_code IS NULL or a.country_code = "", "N/A", a.country_code) as country_code '),
			DB::raw('IF(a.domain IS NULL or a.domain = "", "N/A", a.domain) as domain ')
        );
        $query->where(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d")'),'=', date('Y-m-d',strtotime($date_time)));
        $query->groupBy('dl_source');
		$query->groupBy('widget');
		$query->groupBy('country_code');
		$query->groupBy('date1');
		$query->groupBy('domain');
		$query->orderBy('date1','asc');
		$results=$query->get();

		foreach($results as $result)
        {

	        $search_click_high_volume_website_report_input['date']=$result->date1;
			$search_click_high_volume_website_report_input['dl_source']=$result->dl_source;
			$search_click_high_volume_website_report_input['widget']=$result->widget;
			$search_click_high_volume_website_report_input['country_code']=$result->country_code;
			$search_click_high_volume_website_report_input['domain']=$result->domain;
			$search_click_high_volume_website_report_input['total_clicks']=$result->total_clicks;

			$publisher=SearchClickHighVolumeWebsiteReport::create($search_click_high_volume_website_report_input);
        }
    }


	/**
	 * This is initial function called from seeder
	 *
	 */

	public function seed_init()
    {
        LogCustom::log_array_string("Todays date time = ".date("j F, Y, g:i a",strtotime($this->todays_date_time)));

		$yesterdays_date_time=date("Y-m-d H:i:s",strtotime('-1 day',strtotime($this->todays_date_time)));

		LogCustom::log_array_string("Yesterday date time = ".date("j F, Y, g:i a",strtotime($yesterdays_date_time)));

		$this->processAllDataFromSearchClick($yesterdays_date_time);
    }


	/**
	 *
	 * This function checks dates from beginning and take last continue date
	 * from search_click_high_volume_website_report table
	 *
	 * @return string
	 *
	 */

	public function getLastContinueDateFromSearchClickHighVolumeWebsiteReport()
    {
        $check_dates_results = SearchClickHighVolumeWebsiteReport::groupBy('date')
		->orderBy('date','asc')
		->get();

		$last_date="";
		$search_click_high_volume_website_report_date_arr=array();

		if (count($check_dates_results))
		{
			$start_date=$check_dates_results[0]->date;
			$tmp_start_date=$start_date;

			LogCustom::log_array_string($start_date, "first date in search click high table");

			foreach($check_dates_results as $check_dates_result)
			{
				$search_click_high_volume_website_report_date_arr[]=$check_dates_result->date;
			}

			for($i=0;$i<count($search_click_high_volume_website_report_date_arr);$i++)
			{
				$db_date=$search_click_high_volume_website_report_date_arr[$i];

				LogCustom::log_array_string($tmp_start_date." == ".$db_date,"tmp_start_date == db_date");

				if($tmp_start_date==$db_date)
				{
					$last_date=$db_date;
				}
				else
				{
					// Check in search click table if record for this date exists
					$search_click = SearchClick::where('date','=',$tmp_start_date)->first();
					if ($search_click !== null)
					{
						// if exists break loop
						LogCustom::log_array_string("break");
						break;
					}
					else
					{
						// otherwise match this date again
						$i--;
					}
				}

				$tmp_start_date=date("Y-m-d",strtotime('+1 day',strtotime($tmp_start_date)));
			}
		}

		LogCustom::log_array_string($last_date, "last_date");
		return $last_date;

    }


	/**
	 * This function will process all clicks from beginning
	 *
	 * @param $yesterdays_date_time
	 */

	public function processAllDataFromSearchClick($yesterdays_date_time)
    {

		$start_date="";
		$last_date=$this->getLastContinueDateFromSearchClickHighVolumeWebsiteReport();

		if ($last_date != "")
		{
			$start_date=$last_date;
			LogCustom::log_array_string($start_date,"start_date from search click high");
		}
		else
		{

			// if search_click_high_volume_website_report is empty then take oldest date from search_clicks table
			$search_click = SearchClick::groupBy('date')
			->orderBy('date','asc')
			->first();

			if ($search_click !== null)
			{

				$start_date = $search_click->date;
				LogCustom::log_array_string($start_date, "start_date from search click");
				//LogCustom::log_array_string($search_click, "search_click");

			}
		}

		LogCustom::log_array_string("Yesterday date time = ".date("j F, Y, g:i a",strtotime($yesterdays_date_time)));
		LogCustom::log_array_string("start_date  = ".date("j F, Y, g:i a",strtotime($start_date)));

		if($start_date)
		{
			$tmp_start_date_timestamp=strtotime($start_date);
			$tmp_yesterday_timestamp=strtotime(date("Y-m-d 00:00:00",strtotime($yesterdays_date_time)));

			LogCustom::log_array_string("Yesterday date time temp = ".date("j F, Y, g:i a",$tmp_yesterday_timestamp));

			$j=0;
			while($tmp_start_date_timestamp < $tmp_yesterday_timestamp)
			{
				LogCustom::log_array_string("tmp_start_date_timestamp  = ".date("j F, Y, g:i a",$tmp_start_date_timestamp));

				$this->processDataFromSearchClickWithCheck(date("Y-m-d H:i:s",$tmp_start_date_timestamp));

				$tmp_start_date_timestamp=strtotime('+1 day',$tmp_start_date_timestamp);
				$j++;

			}
		}
    }


	/**
	 * This function take result from search_click table for a date provided
	 * and store if not exists in search_click_high_volume_website_report table
	 *
	 * @param $date_time  //format:Y-m-d H:i:s
	 */

    public function processDataFromSearchClickWithCheck($date_time)
    {

	    $query = DB::table('search_clicks as a');
        $query->select(
            DB::raw('sum(clicks) as total_clicks'),
			DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date1'),
	        DB::raw('IF(a.dl_source IS NULL or a.dl_source = "", "N/A", a.dl_source) as dl_source '),
			DB::raw('IF(a.widget IS NULL or a.widget = "", "N/A", a.widget) as widget '),
			DB::raw('IF(a.country_code IS NULL or a.country_code = "", "N/A", a.country_code) as country_code '),
			DB::raw('IF(a.domain IS NULL or a.domain = "", "N/A", a.domain) as domain ')
        );
        $query->where('date','=', date('Y-m-d',strtotime($date_time)));
        $query->groupBy('dl_source');
		$query->groupBy('widget');
		$query->groupBy('country_code');
		$query->groupBy('date1');
		$query->groupBy('domain');
		$query->orderBy('date1','asc');
		$results=$query->get();

		foreach($results as $result)
        {

			LogCustom::log_array_string($result,"All Result");

			$search_click_high_volume_website_report_input['date']=$result->date1;
			$search_click_high_volume_website_report_input['dl_source']=$result->dl_source;
			$search_click_high_volume_website_report_input['widget']=$result->widget;
			$search_click_high_volume_website_report_input['country_code']=$result->country_code;
			$search_click_high_volume_website_report_input['domain']=$result->domain;
			$search_click_high_volume_website_report_input['total_clicks']=$result->total_clicks;

			$search_click_high_volume_website_report = SearchClickHighVolumeWebsiteReport::where('date', '=', $result->date1)
			->where('dl_source','=',$result->dl_source)
			->where('widget','=',$result->widget)
			->where('country_code','=',$result->country_code)
			->where('domain','=',$result->domain)
			->first();

			if ($search_click_high_volume_website_report === null)
			{
				$search_click_high_volume_website_report=SearchClickHighVolumeWebsiteReport::create($search_click_high_volume_website_report_input);
			}
        }
    }
}