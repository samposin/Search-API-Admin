<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/',array('uses' => 'HomeController@index', 'as' => 'home'));

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    Route::auth();

    // Search feed public routes
    Route::get('/search-feeds/schema/input',array('uses' => 'SearchFeedsController@getInputSchema', 'as' => 'search_schema_input'));
    Route::get('/search-feeds/schema/output',array('uses' => 'SearchFeedsController@getOutputSchema', 'as' => 'search_schema_output'));
});

/*
 * admin routes
 *
 */

Route::group(['prefix' => 'admin', 'middleware' => 'admin', 'namespace' => 'Admin'], function() {

    // Dashboard routes
    Route::get('/',array('uses' => 'DashboardController@index', 'as' => 'dashboard-home'));
    Route::get('dashboard',array('uses' => 'DashboardController@index', 'as' => 'dashboard-home'));


	Route::get('/users', ['middleware' => ['role:admin'], 'uses' => 'UsersController@index']);

    // Advertiser routes

    Route::get('advertisers/search-defaults',array('uses' => 'AdvertisersController@search_defaults_index', 'as' => 'advertisers-search-defaults-home'));
    Route::post('advertisers/search-defaults','AdvertisersController@search_defaults_store');


    Route::get('advertisers',array('uses' => 'AdvertisersController@index', 'as' => 'advertisers-home'));
    Route::post('advertisers-list-ajax','AdvertisersController@advertisers_list_ajax');
    Route::post('advertisers','AdvertisersController@store');
    Route::get('advertisers/{id}','AdvertisersController@show');
    Route::post('advertisers/{id}','AdvertisersController@update');
    Route::delete('advertisers',array('uses' => 'AdvertisersController@delete', 'as' => 'advertiser-delete'));


    // Publisher routes

    Route::post('publishers/advertiser-search-defaults','PublishersController@search_defaults_store');

    Route::get('publishers/on-boarding',array('uses' => 'PublishersController@on_boarding_index', 'as' => 'publishers-on-boarding-home'));
    Route::post('publishers-on-boarding-list-ajax','PublishersController@on_boarding_list_ajax');
    Route::post('publishers/on-boarding','PublishersController@on_boarding_store');


    Route::get('publishers',array('uses' => 'PublishersController@index', 'as' => 'publishers-home'));
    Route::post('publishers-list-ajax','PublishersController@publishers_list_ajax');
    Route::get('publishers/create','PublishersController@create');
    Route::post('publishers','PublishersController@store');
    Route::get('publishers/{id}','PublishersController@show');
    Route::post('publishers/{id}','PublishersController@update');
    Route::delete('publishers',array('uses' => 'PublishersController@delete', 'as' => 'publishers-delete'));

    //analytics/daily/jsver
    Route::get('analytics/daily/jsver',array('uses' => 'AnalyticsController@daily_jsver_show'));
    Route::post('analytics/daily-jsver-ajax',array('uses' => 'AnalyticsController@daily_jsver_ajax'));

    //analytics/daily/all
    Route::get('analytics/daily/all',array('uses' => 'AnalyticsController@daily_all_show'));
    Route::post('analytics/daily-all-ajax',array('uses' => 'AnalyticsController@daily_all_ajax'));

    //analytics/hourly/jsver/table
    Route::get('analytics/hourly/jsver/table',array('uses' => 'AnalyticsController@hourly_jsver_table_show'));
    Route::post('analytics/hourly/jsver/table-ajax',array('uses' => 'AnalyticsController@hourly_jsver_table_ajax'));

    //analytics/hourly/jsver/graph
    Route::get('analytics/hourly/jsver/graph',array('uses' => 'AnalyticsController@hourly_jsver_graph_show'));
    Route::post('analytics/hourly/jsver/graph-ajax',array('uses' => 'AnalyticsController@hourly_jsver_graph_ajax'));

    //analytics/hourly-all
    Route::get('analytics/hourly/all',array('uses' => 'AnalyticsController@hourly_all_show'));
    Route::post('analytics/hourly-all-ajax',array('uses' => 'AnalyticsController@hourly_all_ajax'));

    Route::get('analytics/clicks-ratio',array('uses' => 'AnalyticsController@clicks_ratio_show'));
    Route::post('analytics/clicks-ratio-ajax',array('uses' => 'AnalyticsController@clicks_ratio_ajax'));


    Route::get('analytics/clicked-keywords',array('uses' => 'AnalyticsController@clicked_keywords_show'));
    Route::post('analytics/clicked-keywords-ajax',array('uses' => 'AnalyticsController@clicked_keywords_ajax'));

    Route::get('analytics/high-volume-websites ',array('uses' => 'AnalyticsController@high_volume_website_show'));
    Route::post('analytics/high-volume-website-ajax',array('uses' => 'AnalyticsController@high_volume_website_ajax'));

    //email/send
    Route::get('email/send',array('uses' => 'EmailController@index', 'as' => 'email-send-home'));
    Route::post('email/send',array('uses' => 'EmailController@send_email'));

    Route::get('notes',array('uses' => 'NotesController@index', 'as' => 'notes-home'));


    // Search feed routes
    Route::get('search-feeds',array('uses' => 'SearchFeedsController@index', 'as' => 'search-feeds-home'));
    Route::post('search-feeds-list-ajax','SearchFeedsController@search_feeds_list_ajax');
    Route::post('search-feeds','SearchFeedsController@store');
    Route::get('search-feeds/{id}','SearchFeedsController@show');
    Route::post('search-feeds/{id}','SearchFeedsController@update');
    Route::delete('search-feeds',array('uses' => 'SearchFeedsController@delete', 'as' => 'search-feed-delete'));

    //wikis
    Route::get('wiki/add-new','WikiController@index');
    Route::post('wiki/category-ajax-show','WikiController@category_ajax_show');
    Route::post('wiki/category-ajax-save','WikiController@category_ajax_save');
    Route::post('wiki/save','WikiController@save');

    //Blog
    Route::get('wiki/{id}','BlogController@index');

    //Csv-Report routes
    Route::get('csv-report',array('uses' => 'CsvReportController@index', 'as' => 'csv-home'));
    Route::post('csv-report/api','CsvReportController@api_show');
    Route::get('csv-report/download','CsvReportController@csv_download');
    Route::post('csv-report/email_send','CsvReportController@csv_email_send');


    Route::get('twenga',array('uses' => 'TwengaController@index', 'as' => 'twenga-home'));
    Route::post('twenga/show','TwengaController@show');
    Route::post('twenga/generate-daily-report','TwengaController@twenga_generate_daily_report');
    Route::get('twenga/download','TwengaController@twenga_download');
    Route::post('twenga/email_send','TwengaController@twenga_email_send');



    //Select Queries routes
    Route::get('select-queries','SelectQueriesController@index');
    Route::post('select-queries/query-string', 'SelectQueriesController@getQueryStringAjax');
    Route::post('select-queries/table-field-name', 'SelectQueriesController@getTableFieldNameAjax');
    Route::post('select-queries/all-table-db', 'SelectQueriesController@getAllTableDbAjax');
    Route::post('select-queries/show-data-table', 'SelectQueriesController@GetShowDataTabelAjax');
    Route::post('select-queries/action-show-data-table', 'SelectQueriesController@getShowActionDataTableAjax');
    Route::post('select-queries/previous-show-data-table', 'SelectQueriesController@getPreviousDataTableAjax');
    Route::get('select-queries/download-csv', 'SelectQueriesController@downloadCsvDb');


});


// Route for getting advertisers
Route::get('api/get_advertisers', function(){
    $advertisers = \App\Advertiser::orderBy('name');
    return Response::make($advertisers->get(['id','name']));
});


// Route for getting advertiser's widgets
Route::get('api/get_advertiser_widgets', function(){
    $advertiser_widgets = \App\AdvertiserWidget::orderBy('name');
    return Response::make($advertiser_widgets->get(['id','name']));
});

Route::get('search_click_report', function () {

    $search_click_report= new \App\CronHelpers\SearchClickReport();
    //$search_click_report->init();

});

Route::get('high_volume_website_report', function () {

    $high_volume_website_report= new \App\CronHelpers\HighVolumeWebsiteReport();
    //$high_volume_website_report->init();
    //$high_volume_website_report->seed_init();

});