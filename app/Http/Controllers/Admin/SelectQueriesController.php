<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Excel;
use App\Http\Requests;
use Illuminate\Http\Request;

class SelectQueriesController extends Controller
{
    /**
     * load view HTML
    **/
    public function index()
    {
        return view('pages.admin.select-queries.index');
    }
    /**
     * show all table DB
     * return json_encode
     **/
    public function getAllTableDbAjax(Request $request)
    {

        $tables = DB::select('SHOW TABLES');
        if(count($tables)>0){

            $response['success']='1';
            $response['data']=$tables;
        }
        else{
            $response['success']='0';
            $response['message']="Empty Table";
        }
        echo json_encode($response);
    }
    /**
     * show SQl Query  DB
     * return json_encode
     **/
    public function getPreviousDataTableAjax()
    {

        $data = DB::table('select_queries')->select()->limit(10)->orderby('id', 'desc')->get();
        if(count($data)>0){

            $response['success']='1';
            $response['data']=$data;
        }
        else{
            $response['success']='0';
            $response['message']="Empty Record";
        }
        echo json_encode($response);
    }
    /**
     * show Sql Query String
     * return json_encode
     **/
    public function getQueryStringAjax(Request $request)
    {


        if ($request->has('select_field_name') && $request->get('select_field_name')!=null) {
            $select_field_name = $request->get('select_field_name');
        }
        else {
            $select_field_name = '*';

        }
        $table =$request->get('table_name');
        if($request->has('where-clause-string') && $request->get('where-clause-string')!=null) {
            $where_clause_string =  " WHERE " . $request->get('where-clause-string');
        }

        else {
            $where_clause_string = '';
        }

        $query_string = "SELECT " . $select_field_name . " FROM " . $table . $where_clause_string . "";

        $result_array = array();
        $result_array['query_string'] = $query_string;
        echo json_encode($result_array);
    }
     /**
     * show Sql Query Data DB
      * save Sql Query DB
      * return json_encode
     */
    public function GetShowDataTabelAjax(Request $request)
    {

        if ($request->has('sql_query_string') && $request->get('sql_query_string')!=null) {
            $sql_query_string = $request->get('sql_query_string');
        }
        $result_array = array();

        try {
            $showdatatable= DB::select( DB::raw($sql_query_string." limit 50"));
            if(count($showdatatable)>0) {

                $total_count=count(DB::select( DB::raw($sql_query_string)));//last time total count
                $query_string = $sql_query_string;
                $data = array('name_query' => $query_string, 'date' => date('Y-m-d'), 'last_time_results'=>$total_count, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now());
                $query_id=DB::table('select_queries')->insertGetId($data);
                $previousshowdatatable = DB::table('select_queries')->select()->limit(10)->orderby('id', 'desc')->get();

                $result_array['success']="1";
                $result_array['query_id'] = $query_id;
                $result_array['showdatatable'] = $showdatatable;
                $result_array['previousshowdatatable'] = $previousshowdatatable;
            }
            else{
                $result_array['success']="0";
                $result_array['message']="Record not found";
            }

        } catch(\Illuminate\Database\QueryException $ex){

            $result_array['success']="0";
            $result_array['message']=$ex->getMessage();
        }

        echo json_encode($result_array);
    }
    /**
     * show Sql Query Data DB
     * return json_encode
     **/
    public function  getShowActionDataTableAjax(Request $request)

    {
        if ($request->has('id') && $request->get('id')!=null) {
            $query_id = $request->get('id');
            $get_query_string = DB::table('select_queries')->select()->where('id',$query_id)->get();
            if(count($get_query_string)>0){

                $query=$get_query_string[0]->name_query;
                $data=DB::select(DB::raw($query." limit 50"));
                $result_arr['success']="1";
                $result_arr['data']=$data;
            }
        }
        else{

            $result_arr['success']="0";
            $result_arr['message']='Record not found';
        }

        echo json_encode($result_arr);
    }
    /**
     * Download CSV File DB
     *using Maatawebsite plugin
     **/
    public function downloadCsvDb(Request $request)
    {

        ini_set('max_execution_time',600 );
        //increase Allowed Memory Size of this script:
        ini_set('memory_limit','2G');
        set_time_limit(0);
        $query_string='';

        if($request->has('query_id') && $request->get('query_id')!=null){

            $query_id=$request->get('query_id');
            $query_results= DB::table('select_queries')->select()->where('id',$query_id)->get();

            if(count($query_results)>0){

                $query_string=$query_results[0]->name_query;
            }
        }

        else {

            if ($request->has('select_field_name') && $request->get('select_field_name')!= '') {
                 $select_field_name = $request->get('select_field_name');
            }
            else {
                $select_field_name = '*';

            }
            $table =$request->get('table_name');
            if ($request->has('where-clause-string') && $request->get('where-clause-string')!= '') {
                $select_field_name =  " WHERE " . $request->get('where-clause-string');
            }

            else {
                $where_clause_string = '';
            }

            $query_string = "SELECT  " . $select_field_name . " FROM " . $table . $where_clause_string . "";

        }

        try {
            $results = DB::select(DB::raw($query_string));

            $results = json_decode(json_encode($results), true);

            Excel::create('csv-report', function ($excel) use ($results) {
                $excel->sheet('Sheet 1', function ($sheet) use ($results) {
                    $sheet->fromArray($results);
                });
            })->download('csv');//download csv file on popup
        }
        catch(\Illuminate\Database\QueryException $ex){

            dd($ex->getMessage());
        }
    }
}
