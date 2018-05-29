<?php namespace App\Helpers;

use Akeneo\Component\SpreadsheetParser\SpreadsheetParser;
use App\Advertiser;
use App\VisionApiReport;
use DateTime;
use GrahamCampbell\Dropbox\Facades\Dropbox;
use Illuminate\Support\Facades\File;

class Ebay {


	private $geo_arr=array(
		"US","FR","UK","DE","AU"
	);




	private $geo_info_arr=array();


	private $db_publishers_arr=array();

	private $excel_file_dropbox_folder_name="";
	private $excel_file_server_upload_path="";
	private $dropbox_latest_file_info_arr=array();
	private $todays_date='';

	function date_compare($a, $b)
    {
        $t1 = strtotime($a['modified_datetime']);
        $t2 = strtotime($b['modified_datetime']);
        return $t2 - $t1;
    }

	public function init()
    {
        VisionApi::echo_printr("Start Ebay init at ".date("j F, Y, g:i a"));

        $this->todays_date=date("Y-m-d");


        $this->excel_file_dropbox_folder_name='/iLeviathan-Reporting/Ebay';

        $this->excel_file_server_upload_path=base_path() . '/public/files/excels/uploads/Ebay/';

        $this->dropbox_latest_file_info_arr=$this->getYesterdayFileInfoFromDropbox($this->excel_file_dropbox_folder_name);
		VisionApi::echo_printr($this->dropbox_latest_file_info_arr,"Latest file info");

        foreach($this->geo_arr as $geo)
        {
            VisionApi::echo_printr($geo,"geo");



			if($this->downloadFileFromDropboxByGeo($geo))
	        {
	            $this->processExcelAndSaveInDBByGeo($geo);
	        }
        }

        VisionApi::echo_printr($this->dropbox_latest_file_info_arr,"Latest file info");
        VisionApi::echo_printr($this->geo_info_arr,"geo_info_arr");
        //die();
    }

    public function getYesterdayFileInfoFromDropbox($dropbox_file_path)
    {
	    $metadata=Dropbox::getMetadataWithChildren($dropbox_file_path);
	    VisionApi::echo_printr($metadata,"metadata");

	    $dt = new DateTime($this->todays_date);
        VisionApi::echo_printr($dt->format("Y-m-d H:i:s"),"date");
        $dt->modify('-1 day');
        VisionApi::echo_printr($dt->format("Y-m-d H:i:s"),"date");

	    $fileMetaArr=array();
        if(isset($metadata['contents']))
        {
            VisionApi::echo_printr("metadata set");
	        if (count($metadata['contents']) > 0)
	        {
		        for ($i = 0; $i < count($metadata['contents']); $i++)
		        {
			        $row = $metadata['contents'][$i];

			        $basename=basename($row['path']);
			        VisionApi::echo_printr($basename,"basename");

			        foreach($this->geo_arr as $geo)
			        {
				        VisionApi::echo_printr($geo, "geo");

						if($geo=='UK')
							$basename_substr = 'ileviathan_GB_' . $dt->format("Ymd");
						else
				            $basename_substr = 'ileviathan_' . strtoupper($geo) . '_' . $dt->format("Ymd");

				        VisionApi::echo_printr($basename_substr, "basename_substr");

				        if (strpos($basename, $basename_substr) !== false)
				        {
						    VisionApi::echo_printr("Matched");

						    if (strpos($basename, '(1)') !== false) {

							    VisionApi::echo_printr("Contain (1)");
						    }
						    else
						    {
						         VisionApi::echo_printr("Not contain (1)");
						         $fileMetaArr[$geo]=$row;
						    }
						}
						else
						{
							VisionApi::echo_printr("Not matched");
						}
			        }
		        }
	        }
	        else
	        {
		        //$fileMetaArr[0] = array();
	        }
        }
        else
        {
            VisionApi::echo_printr("metadata not set");
            //$fileMetaArr[0] = array();
        }

         foreach($this->geo_arr as $geo)
         {
	         if(!isset($fileMetaArr[$geo]))
	         {
	            $fileMetaArr[$geo]=array();
	         }
         }

		return $fileMetaArr;
    }

    public function getLatestFileInfoFromDropbox($dropbox_file_path)
    {



        $metadata=Dropbox::getMetadataWithChildren($dropbox_file_path);

        VisionApi::echo_printr($metadata,"metadata");

        $fileMetaArr=array();
        if(isset($metadata['contents']))
        {
            VisionApi::echo_printr("metadata set");
	        if (count($metadata['contents']) > 0) {
		        for ($i = 0; $i < count($metadata['contents']); $i++) {
			        $row = $metadata['contents'][$i];
			        $dt = new DateTime($row['modified']);
			        $sNewFormat = $dt->format("Y-m-d H:i:s");
			        $row['modified_datetime'] = $sNewFormat;

			        $fileMetaArr[] = $row;
		        }
	        }
	        else {
		        $fileMetaArr[0] = array();
	        }
        }
        else
        {
            VisionApi::echo_printr("metadata not set");
            $fileMetaArr[0] = array();
        }

        VisionApi::echo_printr($fileMetaArr,"fileMetaArr");

        usort($fileMetaArr, array("App\Helpers\Ebay", "date_compare"));

        VisionApi::echo_printr($fileMetaArr,"fileMetaArr");

        return $fileMetaArr[0];
    }

    public function downloadFileFromDropboxByGeo($geo)
    {
        if(count($this->dropbox_latest_file_info_arr[$geo])>0) {

            $this->geo_info_arr[$geo]['excel_file_server_upload_file_name']=basename($this->dropbox_latest_file_info_arr[$geo]['path']);

            $this->geo_info_arr[$geo]['excel_file_server_upload_file_path'] = $this->excel_file_server_upload_path .$geo.'/'. $this->geo_info_arr[$geo]['excel_file_server_upload_file_name'];

            if (!File::exists($this->excel_file_server_upload_path))
            {
                VisionApi::echo_printr("ebay folder not exists");
                $result = File::makeDirectory($this->excel_file_server_upload_path, 0775, true, true);
            }
            else
            {
                VisionApi::echo_printr("ebay folder exists");
            }

            if (!File::exists($this->excel_file_server_upload_path.'/'.$geo))
            {
                VisionApi::echo_printr($geo." geo folder not exists");
                $result = File::makeDirectory($this->excel_file_server_upload_path.'/'.$geo, 0775, true, true);
            }
            else
            {
                VisionApi::echo_printr($geo." geo folder exists");
            }

            $fd = fopen( $this->geo_info_arr[$geo]['excel_file_server_upload_file_path'], "wb");

            $getFileMetadata = Dropbox::getFile($this->dropbox_latest_file_info_arr[$geo]['path'], $fd);

            VisionApi::echo_printr("Downloaded file from Ebay Dropbox named as ".$this->geo_info_arr[$geo]['excel_file_server_upload_file_name']." on ".date("j F, Y, g:i a"));

            fclose($fd);

            return true;
        }
        else
        {
            return false;
        }
    }

    public function getCsvData($geo)
    {

        $workbook = SpreadsheetParser::open($this->geo_info_arr[$geo]['excel_file_server_upload_file_path']);

        $iterator = $workbook->createRowIterator(
            0,
            [
                //'encoding'  => 'UTF-8',
                'length'    => null,
                'delimiter' => ',',
                'enclosure' => '"',
                'escape'    => '\\'
            ]
        );
        $i=0;
        $k=0;
        $arr=array();
        $column_arr=array();

        foreach ($iterator as $rowIndex => $values)
        {
            if($i==0)
            {
                for($j=0;$j<count($values);$j++)
                {
                    $values[$j] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $values[$j]);
                    $column_arr[$j]=trim($values[$j]);
                }
            }
            else
            {
                for($j=0;$j<count($values);$j++)
                {
                    $arr[$k][$column_arr[$j]]=$values[$j];
                }
                $k++;
            }
            $i++;
        }
        return $arr;
    }

    public function getPublishersInfo()
    {
        $publishers_arr=[];

        $advertisers = Advertiser::where('name', '=', 'Ebay')->where('is_delete', '=', 0)->with(array('publishers' => function($query)
        {
            $query->where('is_delete', '=', 0);

        }))->get()->toArray();

        foreach($advertisers as $advertiser) {

            $publishers_arr[strtolower($advertiser['name'])]=array();

            for($i=0;$i<count($advertiser['publishers']);$i++)
            {
                $publisher=$advertiser['publishers'][$i];

                $publishers_arr[strtolower($advertiser['name'])][strtolower($publisher['name'])] = $publisher;
            }
        }

        return $publishers_arr;
    }

    public function processExcelAndSaveInDBByGeo($geo)
    {
        if(file_exists($this->geo_info_arr[$geo]['excel_file_server_upload_file_path'])) {

            $this->db_publishers_arr = $this->getPublishersInfo();
            //$countries_arr=$this->getCountriesAssociativeArray();

            VisionApi::echo_printr($this->db_publishers_arr,"Publishers info");
            //VisionApi::echo_printr($countries_arr,"Countries Array");

            $csv_data=$this->getCsvData($geo);
            VisionApi::echo_printr($csv_data,"CSV Data");



            for($i=0;$i<count($csv_data);$i++) {
	            $result = $csv_data[$i];

	            $result_date=$result['DATE'];
	            $result_date_db=$result['DATE'];

	            if($result_date!="") {
		            $result_date=str_replace('/', '-',$result_date);
		            $result_date_exp=explode('-', $result_date);

		            if(count($result_date_exp)==3)
                    {
                        $mm=$result_date_exp[0];
                        $dd=$result_date_exp[1];
                        $yy=$result_date_exp[2];

                        if (strlen($yy) == 2)
                            $yy = '20' . $yy;

                        $result_date = $dd . '-' . $mm . '-' . $yy;
                        $result_date_db=$yy . '-' . $mm . '-' . $dd;
                        VisionApi::echo_printr($result_date,"result_date");
                        VisionApi::echo_printr($result_date_db,"result_date_db");

                        $this->processClicksByGeoAndDateAndAdvertiser($geo,$result_date_db,$result,'Ebay');
                    }
	            }
            }
        }
    }

    public function processClicksByGeoAndDateAndAdvertiser($geo,$result_date_db,$result,$api)
    {
		$result_searches=$result['API_QUERIES'];
		$result_clicks=$result['LEADS'];
		$result_cost_per_click=$result['REVENUE_PER_LEAD'];

        $query1 = \DB::table('search_clicks as a')
					->selectRaw('SUM(clicks) AS total_clicks1,a.*')
					->where('date', '=', $result_date_db)
					->where('api', '=', strtolower($api))
					->where('country_code', '=', strtoupper($geo))
					->groupBy('api')
					->groupBy('dl_source')
					->groupBy('sub_dl_source')
					->groupBy('widget')
					->groupBy('country_code')
					->groupBy('date')
					->orderBy('date');
					//->lists('total','total_clicks','id');

		$query2 = \DB::table(\DB::raw( "( {$query1->toSql()} ) as totalS" ))
	   ->mergeBindings($query1)
	   ->selectRaw('SUM(totalS.total_clicks1) AS total_clicks,totalS.api,totalS.country_code,totalS.date');

	    $db_reports2=$query2->first();
	    $db_reports1=$query1->get();

		if($db_reports2->total_clicks==null)
		{
			VisionApi::echo_printr("total_clicks is null");
			$total_clicks_db=0;
		}
		else
		{
			VisionApi::echo_printr("total_clicks is not null");
			$total_clicks_db=$db_reports2->total_clicks;
		}

		if($total_clicks_db!=0)
		{
			$click_ratio = $result_clicks / $total_clicks_db;

			VisionApi::echo_printr($result_clicks,"result_clicks");
			VisionApi::echo_printr($total_clicks_db,"total_clicks_db");
			VisionApi::echo_printr($click_ratio,"click_ratio");
			VisionApi::echo_printr(count($db_reports1),"count db_reports1");

			if (count($db_reports1) > 0) {

				$total_records=count($db_reports1);
				//$searches=round($result_searches/$total_records);
				$searches="";

				$db_reports = json_decode(json_encode($db_reports1), true);

				VisionApi::echo_printr(count($db_reports),"count db_reports");
				//VisionApi::echo_printr($db_reports,"db_reports");

				for ($i = 0; $i < count($db_reports); $i++)
				{
					VisionApi::echo_printr($db_reports[$i],"db report i");

					$publisher_share=37.5;
					//$publisher_share=0;
					$country_code=$db_reports[$i]['country_code'];
					$dl_source=$db_reports[$i]['dl_source'];
                    $sub_dl_source=$db_reports[$i]['sub_dl_source'];
                    $widget=$db_reports[$i]['widget'];
                    $date=$db_reports[$i]['date'];

                    $clicks=($db_reports[$i]['total_clicks1']*$click_ratio);

                    $estimated_revenue=$db_reports[$i]['total_clicks1']*$click_ratio*$result_cost_per_click;

					VisionApi::echo_printr(strtolower($dl_source),"strtolower dl_source");

                    if(isset($this->db_publishers_arr['ebay'][strtolower($dl_source)]['pivot']['share']))
	                {
	                    VisionApi::echo_printr($this->db_publishers_arr['ebay'][strtolower($dl_source)],"");
	                    if($this->db_publishers_arr['ebay'][strtolower($dl_source)]['pivot']['share']>0)
	                    {
	                        $publisher_share=$this->db_publishers_arr['ebay'][strtolower($dl_source)]['pivot']['share'];
	                    }
	                }

	                VisionApi::echo_printr($publisher_share,"publisher_share");
	                VisionApi::echo_printr($db_reports[$i]['total_clicks1'],"db report total_clicks");
					VisionApi::echo_printr($estimated_revenue,"estimated_revenue");

					if($publisher_share>0)
                        $estimated_revenue = ($estimated_revenue) * ($publisher_share / 100);

					VisionApi::echo_printr($estimated_revenue,"estimated_revenue");

                    $input['date'] = $date;
	                $input['dl_source'] = $dl_source;
	                $input['sub_dl_source'] = $sub_dl_source;
	                $input['widget'] = $widget;
	                $input['country'] = $country_code;
	                $input['searches'] = $searches;
	                $input['clicks'] = $clicks;
	                $input['estimated_revenue'] = $estimated_revenue;
	                $input['advertiser_name'] = 'Ebay';

	                VisionApiReport::create($input);

				}
			}
		}
	}
}