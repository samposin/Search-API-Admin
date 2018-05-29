<?php
	error_reporting(E_ALL);
	include($_SERVER['DOCUMENT_ROOT'].'/custom/includes/config.php');
    include($site_config['abs_path'].'/includes/db.class.php');
    $db = new DB();

    function getCurrentDayClicks()
    {
        global $table_prefix,$db;

        $todays_date=date("Y-m-d");
        $todays_date_time=date("Y-m-d H:i:s");
        //$todays_date_time=date("2016-05-06 23:00:00");


        $total_clicks=0;

        $sql="
            SELECT count(id) as total_clicks,a.*
            FROM ".$table_prefix."search_clicks AS a
            WHERE
                DATE_FORMAT(created_at, '%Y-%m-%d')='".date('Y-m-d',strtotime($todays_date_time))."'
        ";

        //echo $sql;
        $result = $db->runQuery($sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $total_clicks=$row['total_clicks'];
        }
        return $total_clicks;

    }

    echo $total_clicks=getCurrentDayClicks();

