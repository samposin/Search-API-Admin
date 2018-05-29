<?php

	include('/var/www/html/public/custom/includes/config.php');
    include('/var/www/html/public/custom/includes/db.class.php');
    include('/var/www/html/public/custom/includes/functions.php');
    $db = new DB();


    $total_clicks_arr=getCurrentHourClicks();

    echo "<pre>";
    print_r($total_clicks_arr);

    //$webhookurl='https://hooks.slack.com/services/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';
	$webhookurl='https://hooks.slack.com/services/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';
    //$message="Total Clicks = ".$total_clicks_arr['total_clicks']." between ".$total_clicks_arr['hour_range'];
    $message="Hourly Click Tracking <http://admin.baseify.com|Analytics>";


	$username="Baseify App";
	$icon=":companylogo:";
	$data = json_encode(array(
        "username"       =>  $username,
        "text"          =>  $message,
        "icon_emoji"    =>  $icon,
		"attachments"=>array(
            array(
                "text"=> "Last Hour Clicks - ".$total_clicks_arr['total_clicks']."\n".$total_clicks_arr['hour_range']
            )
		)
    ));

	//echo post_on_slack($webhookurl,"Testing","samposin",":baseifylogo:",'awsposinbot');
	post_on_slack1($webhookurl,$data,"Baseify App",":baseifylogo:");

