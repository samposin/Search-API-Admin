<?php

	function getCurrentDayClicks()
    {
        global $table_prefix,$db;


        $todays_date_time=date("Y-m-d H:i:s");
        //$todays_date_time=date("2016-05-06 23:00:00");

        $todays_date=date('Y-m-d',strtotime($todays_date_time));

        $dt = new DateTime($todays_date);
        //echo "date ".$dt->format("Y-m-d H:i:s")."<br>";
        $dt->modify('-1 day');
        //echo "date ".$dt->format("Y-m-d H:i:s")."<br>";

        $yesterdays_date=$dt->format("Y-m-d");


        $total_clicks=0;

        $sql="
            SELECT count(id) as total_clicks,a.*
            FROM ".$table_prefix."search_clicks AS a
            WHERE
                DATE_FORMAT(created_at, '%Y-%m-%d')='".date('Y-m-d',strtotime($yesterdays_date))."'
        ";

        //echo "<br>".$sql."<br>";
        $result = $db->runQuery($sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $total_clicks=$row['total_clicks'];
        }

        return array('date'=>$yesterdays_date,'total_clicks'=>$total_clicks);

    }

    function getCurrentHourClicks()
    {
        global $table_prefix,$db;

		$todays_date_time=date("Y-m-d H:i:s");
        //$todays_date_time=date("2016-05-06 23:00:00");

        $todays_date=date('Y-m-d',strtotime($todays_date_time));

        $previous_hour_date_time=date("Y-m-d H:i:s",strtotime('-1 hour',strtotime($todays_date_time)));

        $current_hour_index= idate('H', strtotime($todays_date_time));

        $last_hour_index= idate('H', strtotime($previous_hour_date_time));

        $total_clicks=0;

        $sql="
            SELECT count(id) as total_clicks,CONCAT(LPAD(HOUR(created_at),2,'0'), ':00 - ',  LPAD(HOUR(created_at)+1,2,'0'), ':00') as hour_range, a.*
            FROM ".$table_prefix."search_clicks AS a
            WHERE
                HOUR(created_at)=".$last_hour_index." AND
                DATE_FORMAT(created_at, '%Y-%m-%d')='".date('Y-m-d',strtotime($previous_hour_date_time))."'
        ";

        //echo $sql;
        $result = $db->runQuery($sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $total_clicks=$row['total_clicks'];
            $hour_range=$row['hour_range'];
        }
        return array('hour_range'=>$hour_range,'total_clicks'=>$total_clicks);

    }


    function post_on_slack($webhookurl,$message, $username="Admin", $icon = ":longbox:") {

		//$room = ($room) ? $room : "awsposinbot";

		$data = json_encode(array(
            "username"       =>  $username,
            "text"          =>  $message,
            "icon_emoji"    =>  $icon
        ));

		$ch = curl_init($webhookurl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, array('payload' => $data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Content-Type: application/json',
		    //'Content-Length: ' . strlen($data_string)
		    )
		);
        $result = curl_exec($ch);
        //echo '<br>Curl error: ' . curl_error($ch)."<br>";
        curl_close($ch);
        return $result;

	}

	function post_on_slack1($webhookurl,$data, $username="Admin", $icon = ":longbox:") {

		//$room = ($room) ? $room : "awsposinbot";
		/*
		$data = json_encode(array(
            "username"       =>  $username,
            "text"          =>  $message,
            "icon_emoji"    =>  $icon
        ));
		*/

		$ch = curl_init($webhookurl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, array('payload' => $data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Content-Type: application/json',
		    //'Content-Length: ' . strlen($data_string)
		    )
		);
        $result = curl_exec($ch);
        //echo '<br>Curl error: ' . curl_error($ch)."<br>";
        curl_close($ch);
        return $result;

	}

	function func1($uid)
	{
		global $table_prefix,$db;
		

		$sql="
			
			INSERT INTO ".$table_prefix."users (
				userid
			) VALUES (
			 '".$uid."'
			 );
		";
		$result = $db->runQuery($sql);
		
	}
	function func2($userid)
	{
		global $table_prefix,$db;
		
		$sql="
			select * from ".$table_prefix."users where id=".$userid."
		";
		$result = $db->runQuery($sql);
		$row = mysqli_fetch_assoc($result);
		return $row;
		
	}

?>