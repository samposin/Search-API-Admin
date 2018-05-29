<?php
return array(

    /*
	|--------------------------------------------------------------------------
	| Default FTP Connection Name
	|--------------------------------------------------------------------------
	|
	| Here you may specify which of the FTP connections below you wish
	| to use as your default connection for all ftp work.
	|
	*/

    'default' => 'connection1',

    /*
    |--------------------------------------------------------------------------
    | FTP Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the FTP connections setup for your application.
    |
    */

    'connections' => array(

        'twenga_ftp' => array(
            'host'   => 'ftp-01.ta.com',
            'port'  => 21,
            'username' => 'visionapi',
            'password'   => '0eqAxdcS2pKm',
            'passive'   => false,
        ),
    ),
);