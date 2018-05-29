<?php namespace App\Helpers;

use App\Advertiser;
use App\VisionApiReport;
use SoapBox\Formatter\Formatter;

class Kelkoo {

    private $trackings_arr="";
    private $db_publishers_arr=array();

    public function init()
    {
        VisionApi::echo_printr("Start Kelkoo init at ".date("j F, Y, g:i a"));

        $xmldata=$this->getXmlData();

        //VisionApi::echo_printr($xmldata,"xmldata");

        $formatter = Formatter::make($xmldata, Formatter::XML);

        $this->trackings_arr = $formatter->toArray();

        //VisionApi::echo_printr($this->trackings_arr,"trackings_arr");

        $this->processTrackingArrayAndSaveInDB();

    }

    public function processTrackingArrayAndSaveInDB()
    {

        if(isset($this->trackings_arr['tracking'])) {

            if (count($this->trackings_arr['tracking']) > 0) {

                $this->db_publishers_arr = $this->getPublishersInfo();

                VisionApi::echo_printr($this->db_publishers_arr,"Publishers info");

                $exchange_rates = VisionApi::$currency_exchange_rates;

                //VisionApi::echo_printr($exchange_rates,"Exchange Rates");

                for ($i = 0; $i < count($this->trackings_arr['tracking']); $i++) {

                    $result = $this->trackings_arr['tracking'][$i];

                    VisionApi::echo_printr($result,"trackings_arr i");

                    VisionApi::echo_printr(gettype($result['Custom1']),"Custom1");
                    VisionApi::echo_printr(gettype($result['Custom2']),"Custom2");
                    VisionApi::echo_printr(gettype($result['Custom3']),"Custom3");

                    if(gettype($result['Custom1'])=='array')
                        $custom1="";
                    else
                        $custom1=$result['Custom1'];

                    if(gettype($result['Custom2'])=='array')
                        $custom2="";
                    else
                        $custom2=$result['Custom2'];

                    if(gettype($result['Custom3'])=='array')
                        $custom3="";
                    else
                        $custom3=$result['Custom3'];

                    VisionApi::echo_printr($custom1,"custom1");
                    VisionApi::echo_printr($custom2,"custom2");
                    VisionApi::echo_printr($custom3,"custom3");


                    $symbol = $result['currency'];
                    $subid = $custom1;
                    $subid_name="";
                    $publisher_share=37.5;

                    $dt1 = strtotime($result['day']);
                    $date = date("Y-m-d", $dt1);

                    if (isset($this->db_publishers_arr['kelkoo'][strtolower($subid)]['pivot']['share'])) {
                        if ($this->db_publishers_arr['kelkoo'][strtolower($subid)]['pivot']['share'] > 0) {
                            $publisher_share = $this->db_publishers_arr['kelkoo'][strtolower($subid)]['pivot']['share'];
                        }
                    }

                    if(isset($this->db_publishers_arr['kelkoo'][strtolower($subid)]['name']))
                    {
                        if(trim($this->db_publishers_arr['kelkoo'][strtolower($subid)]['name'])!="")
                        {
                            $subid_name=trim($this->db_publishers_arr['kelkoo'][strtolower($subid)]['name']);
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

                    VisionApi::echo_printr($publisher_share,"publisher_share");

                    $payout_str = $result['revenue'];
                    VisionApi::echo_printr($payout_str,"Original Payout");
                    $payout = $payout_str;

                    //VisionApi::echo_printr($payout,"payout before");



                    VisionApi::echo_printr($payout,"Payout after conversion");

                    //VisionApi::echo_printr($payout,"payout after");

                    if ($publisher_share > 0)
                        $payout = ($payout) * ($publisher_share / 100);

                    //$payout = round($payout, 2);
                    //$payout_str = '$' . $payout;

                    $payout_str = $payout;
                    VisionApi::echo_printr($payout_str,"Final Payout");

                    $input['date'] = $date;
                    //$input['dl_source'] = "VisionAPI";
                    $input['dl_source'] = $subid_name;
                    $input['sub_dl_source'] = urldecode($custom2);
                    $input['widget'] = urldecode($custom3);
                    $input['country'] = strtoupper($result['country']);
                    $input['searches'] = '';
                    $input['clicks'] = $result['numberOfLeads'];
                    $input['estimated_revenue'] = $payout_str;

                    $input['advertiser_name'] = 'Kelkoo';

                    VisionApiReport::create($input);

                }
            }
        }
    }

    public function getPublishersInfo()
    {

        $publishers_arr=[];

        $advertisers = Advertiser::where('name', '=', 'Kelkoo')->where('is_delete', '=', 0)->with(array('publishers' => function($query)
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

    public function getXmlData()
    {
        // set HTTP header
        $headers = array(
            //'Content-Type: application/json',
            'Content-Type: application/xml'
        );



        $fields = array(
            'pageType' => 'custom',
            'username' => 'ileviathan-Kelkoo',
            'password' => 'jp478905',
	        'currency' => 'USD'
        );

        //$url = 'http://api.ipinfodb.com/v3/ip-country?' . http_build_query($fields);

        $url = 'https://partner.kelkoo.com/statsSelectionService.xml?' . http_build_query($fields);

        //VisionApi::echo_printr($url,"URL");

        // Open connection
        $ch = curl_init();

        // Set the url, number of GET vars, GET data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Execute request
        $result = curl_exec($ch);

        // Close connection
        curl_close($ch);

        return $result;
    }
}