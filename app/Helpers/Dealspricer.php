<?php namespace App\Helpers;

use Akeneo\Component\SpreadsheetParser\SpreadsheetParser;
use App\Advertiser;
use App\Country;
use App\Publisher;
use App\VisionApiReport;
use DateTime;
use GrahamCampbell\Dropbox\Facades\Dropbox;
use Maatwebsite\Excel\Facades\Excel;

class Dealspricer {

    private $dropbox_latest_file_info="";

    private $excel_file_dropbox_folder_name="";
    private $excel_file_server_upload_path="";
    private $excel_file_server_upload_file_path="";
    private $excel_file_server_upload_file_name="";

    private $db_publishers_arr=array();

    function date_compare($a, $b)
    {
        $t1 = strtotime($a['modified_datetime']);
        $t2 = strtotime($b['modified_datetime']);
        return $t2 - $t1;
    }


    public function init()
    {
        VisionApi::echo_printr("Start Dealspricer init at ".date("j F, Y, g:i a"));


        $this->excel_file_dropbox_folder_name='/iLeviathan-Reporting/Dealspricer';

        $this->excel_file_server_upload_path=base_path() . '/public/files/excels/uploads/Dealspricer/';

        $this->dropbox_latest_file_info=$this->getLatestFileInfoFromDropbox();

        VisionApi::echo_printr($this->dropbox_latest_file_info,"Latest file info");


        if($this->downloadFileFromDropbox())
        {
            $this->processExcelAndSaveInDB();
        }

    }

    public function getLatestFileInfoFromDropbox()
    {

        $metadata=Dropbox::getMetadataWithChildren($this->excel_file_dropbox_folder_name);

        $fileMetaArr=array();
        if(count($metadata['contents'])>0)
        {
            for($i=0;$i<count($metadata['contents']);$i++)
            {
                $row=$metadata['contents'][$i];

                $arr=explode(".",strtolower(trim(basename($row['path']))));
                $ext=end($arr);

                if($ext=='csv') {
                    $dt = new DateTime($row['modified']);
                    $sNewFormat = $dt->format("Y-m-d H:i:s");
                    $row['modified_datetime'] = $sNewFormat;

                    $fileMetaArr[] = $row;
                }
            }
        }
        else
        {
            $fileMetaArr[0]=array();
        }

        usort($fileMetaArr, array("App\Helpers\Dealspricer", "date_compare"));

        return $fileMetaArr[0];
    }

    public function downloadFileFromDropbox()
    {
        if(count($this->dropbox_latest_file_info)>0) {

            $this->excel_file_server_upload_file_name=basename($this->dropbox_latest_file_info['path']);

            $this->excel_file_server_upload_file_path = $this->excel_file_server_upload_path . $this->excel_file_server_upload_file_name;

            $fd = fopen($this->excel_file_server_upload_file_path, "wb");

            $getFileMetadata = Dropbox::getFile($this->dropbox_latest_file_info['path'], $fd);

            VisionApi::echo_printr("Downloaded file from Dealspricer Dropbox named as ".$this->excel_file_server_upload_file_name." on ".date("j F, Y, g:i a"));

            fclose($fd);

            return true;
        }
        else
        {
            return false;
        }
    }

    public function getPublishersInfo()
    {
        $publishers_arr=[];

        $advertisers = Advertiser::where('name', '=', 'Dealspricer')->where('is_delete', '=', 0)->with(array('publishers' => function($query)
        {
            $query->where('is_delete', '=', 0);

        }))->get()->toArray();

        foreach($advertisers as $advertiser) {

            $publishers_arr[strtolower($advertiser['name'])]=array();

            for($i=0;$i<count($advertiser['publishers']);$i++)
            {
                $publisher=$advertiser['publishers'][$i];

                if(isset($publisher['pivot']['publisher_id1']) && $publisher['pivot']['publisher_id1']!="")
                {
                    $publishers_arr[strtolower($advertiser['name'])][strtolower($publisher['pivot']['publisher_id1'])] = $publisher;
                }

            }
        }

        return $publishers_arr;
    }

    public function getCurrenciesFromArray($arr)
    {
        $currency_codes_arr=array();
        for($i=0;$i<count($arr);$i++) {
            $result = $arr[$i];
            if(isset($result['Local Currency Symbol']) && $result['Local Currency Symbol']!="")
                $currency_codes_arr[$result['Local Currency Symbol']]=$result['Local Currency Symbol'];
        }
        return $currency_codes_arr;
    }

    public function getCsvData()
    {

        $workbook = SpreadsheetParser::open($this->excel_file_server_upload_file_path);

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

    public function processExcelAndSaveInDB()
    {
        VisionApi::echo_printr($this->excel_file_server_upload_file_path,"excel_file_server_upload_file_path");

        if(file_exists($this->excel_file_server_upload_file_path))
        {
            $this->db_publishers_arr = $this->getPublishersInfo();
            $countries_arr=$this->getCountriesAssociativeArray();

            VisionApi::echo_printr($this->db_publishers_arr,"Publishers info");
            //VisionApi::echo_printr($countries_arr,"Countries Array");

            $csv_data=$this->getCsvData();
            //VisionApi::echo_printr($csv_data,"CSV Data");

            $exchange_rates=VisionApi::$currency_exchange_rates;

            //VisionApi::echo_printr($exchange_rates,"Exchange Rates");

            for($i=0;$i<count($csv_data);$i++)
            {
                $result=$csv_data[$i];

                VisionApi::echo_printr($result,"result i");
                $subid_str= $subid = $result['Sub ID'];

                VisionApi::echo_printr($subid_str,"subid_str");

                $subid_str_exp=explode('-',$subid_str);
                VisionApi::echo_printr($subid_str_exp,"subid_str_exp");

				$subid='';
                if(isset($subid_str_exp[1]))
                    $subid=$subid_str_exp[1];

				$sub_dl_source='';
                if(isset($subid_str_exp[2]))
                    $sub_dl_source=$subid_str_exp[2];

				$widget='';
                if(isset($subid_str_exp[3]))
                    $widget=$subid_str_exp[3];

				$sub_dl_source=urldecode($sub_dl_source);
				$sub_dl_source=str_replace('|','-',$sub_dl_source);
				$widget=urldecode($widget);
				$widget=str_replace('|','-',$widget);

				VisionApi::echo_printr($subid,"subid");
				VisionApi::echo_printr($sub_dl_source,"sub_dl_source");
				VisionApi::echo_printr($widget,"widget");


                $country_name=$result['Country'];
                $symbol = $result['Local Currency Symbol'];
                //$subid = $result['Sub ID'];
                $subid_name="";
                ////$publisher_share=37.5;
                $publisher_share=0;
                $result_date=$result['Date'];
                $clicks=$result['Clicks'];

                if($result_date!="") {

                    $result_date=str_replace('/', '-',$result_date);
                    //list($dd, $mm, $yy) = explode('-', $result_date);
                    $result_date_exp=explode('-', $result_date);

                    if(count($result_date_exp)==3)
                    {
                        $dd=$result_date_exp[0];
                        $mm=$result_date_exp[1];
                        $yy=$result_date_exp[2];

                        if (strlen($yy) == 2)
                            $yy = '20' . $yy;

                        $result_date = $dd . '-' . $mm . '-' . $yy;
                        VisionApi::echo_printr($result_date,"result_date");
                    }

                    $dt1 = strtotime($result_date);
                    $date = date("Y-m-d", $dt1);

                }
                else
                {
                    $date="";
                }
                VisionApi::echo_printr(($i+1),"Counter");
                VisionApi::echo_printr($date,"Date");
                VisionApi::echo_printr($subid,"Sub id");



                if(isset($this->db_publishers_arr['dealspricer'][strtolower($subid)]['name']))
                {
                    if(trim($this->db_publishers_arr['dealspricer'][strtolower($subid)]['name'])!="")
                    {
                        $subid_name=trim($this->db_publishers_arr['dealspricer'][strtolower($subid)]['name']);
                    }
                    else
                    {
                        $subid_name=$subid;
                    }
                }
                else
                {
                    $subid_name=$subid;
                }

                if(isset($countries_arr[strtolower($country_name)]) && $countries_arr[strtolower($country_name)]!="")
                    $country_name=$countries_arr[strtolower($country_name)];

                VisionApi::echo_printr($publisher_share,"publisher_share");

                $payout_str = $result['Vaa share in total Revenue'];
                VisionApi::echo_printr($payout_str,"Original Payout");

                $payout = $payout_str;


                VisionApi::echo_printr($payout,"Payout after conversion");

                if($publisher_share>0)
                    $payout = ($payout) * ($publisher_share / 100);


				$payout=$clicks*0.005;

                $payout_str = $payout;
                VisionApi::echo_printr($payout_str,"Final Payout");

                $input['date'] = $date;
                //$input['dl_source'] = $result['Partner Name'];
                $input['dl_source'] = $subid_name;
                $input['sub_dl_source'] = $sub_dl_source;
                $input['widget'] = $widget;
                $input['country'] = $country_name;
                $input['searches'] = '';
                $input['clicks'] = $clicks;
                $input['estimated_revenue'] = $payout_str;

                $input['advertiser_name'] = 'Dealspricer';

                VisionApiReport::create($input);

                //print_r($result);
            }
        }
    }

    public function getCountriesAssociativeArray()
    {
        $countries_arr=[];
        $countries=Country::orderBy('name','asc')->get()->toArray();

        foreach($countries as $country) {
            $countries_arr[strtolower($country['name'])]=$country['code'];
        }

        return $countries_arr;
    }
}