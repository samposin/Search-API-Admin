<?php
	
	include('db_config.php');
	$rowsperpage=20;
	
	$site_config['url']='http://'.$_SERVER['HTTP_HOST'];
	$site_config['domain']=$_SERVER['HTTP_HOST'];
	$site_config['name']='Site Name';
	$site_config['logo']='default_logo.png';
	$site_config['abs_path']=$_SERVER['DOCUMENT_ROOT'];
	$site_config['support']['email']='Support Email';
	$site_config['support']['name']='Support Name';
	$site_config['smtp_host']='';
	$site_config['smtp_user']='';
	$site_config['smtp_pwd']='';	
	$site_config['reply']['email']='';
	$site_config['reply']['name']='';
	$site_config['from']['email']='';
	$site_config['from']['name']='';
	$site_config['notification_email_signature']='';
	$site_config['notification_email_footer']='';
	$site_config['rowsperpage']=50;

	date_default_timezone_set('America/Los_Angeles');

	//local
	$site_config['abs_path'].='/custom';
    
    //server
    //$site_config['abs_path'].='';

    $weekdays_arr=array(
		'sunday',
		'monday',
		'tuesday',
		'wednesday',
		'thursday',
		'friday',
		'saturday'
	);
    $site_config['weekdays_arr']=$weekdays_arr;