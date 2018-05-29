@extends('app')

@section('title', 'Select Queries')

@section('page_level_styles')
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/global/plugins/select2/select2.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')}}"/>
@stop

@section('theme_level_styles')
    <link href="{{ URL::asset('assets/global/css/components-rounded.css')}}" id="style_components" rel="stylesheet" type="text/css"/>
    <link href="{{ URL::asset('assets/global/css/plugins.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{ URL::asset('css/layout.css')}}" rel="stylesheet" type="text/css"/>
    <link id="style_color" href="{{ URL::asset('css/themes/light.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{ URL::asset('css/custom.css')}}" rel="stylesheet" type="text/css"/>
    <style>
        .break_word{
            word-break: break-all;
        }

    </style>

@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Begin: life time stats -->
            <div class="portlet light">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-user font-blue-sharp"></i>
                        <span class="caption-subject font-blue-sharp bold">SQL Query - only Select queries </span>
                    </div>
                    <div class="pull-right">
                        <a   id="btn_download_csv" href="" class="btn-success btn  blue" style="display: none">Download CSV</a>
                    </div>
                </div>
                <div class="portlet-body form">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel-body">
                                <form action="{{url('home/show')}}" method="post">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="row">
                                                <div class="form-group">
                                                    <label  class="control-label col-md-3">
                                                        SELECT
                                                    </label>
                                                    <div class="col-md-9">
                                                        <input type="text" class="form-control"  name="select_field_name"  placeholder="*" >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="row">
                                                <div class="form-group">
                                                    <label  class="control-label col-md-3">
                                                        FROM
                                                    </label>
                                                    <div class="col-md-9">
                                                        <select id="table_name_db"  class="form-control table_change" name="table_name" >
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="row">
                                                <div class="form-group">
                                                    <label  class="control-label col-md-2">
                                                        WHERE
                                                    </label>
                                                    <div class="col-md-10">
                                                        <input type="text" id="table_field_name" class="form-control" name="where-clause-string"  style="margin-left: 15px">

                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-md-1">
                                            <button class="btn btn-success" id="btn_go"  type="button">Go</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="panel-body form-group" align="center">
                                    <label id="query_string_show"  style="text-align:center;color:red;display:none"></label>&nbsp;&nbsp;
                                </div>
                                <div class="form-group" align="center">
                                <button class="btn btn-primary btn_get_result"  type="button" style="display: none">Get Result</button>
                                </div>
                                <table id="dt" class="table table-bordered table-hover table table-condensed" align="center">
                                    <thead >
                                    <tr style="background-color: #B0BEC5" id="columns">
                                    </tr>
                                    </thead>
                                    <tbody class="get_show">
                                    </tbody>
                                </table>
                                <div class="panel panel-default">
                                    <div class="panel-heading"><h4 class="caption-subject font-blue-sharp bold">Previous 10 Queries</h4></div>
                                    <div class="panel-body">
                                        <table align="center" class="table table-bordered table-hover table table-condensed" id="dt">
                                            <thead>
                                            <tr style="background-color: #B0BEC5">

                                                <th> Date time of last Execution &nbsp;</th>
                                                <th> SQL Query&nbsp;</th>
                                                <th> Last Time Results</th>
                                                <th> Action </th>
                                            </tr>
                                            </thead>
                                            <tbody class="select">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End: life time stats -->
        </div>
    </div>


@stop

@section('page_level_plugins')
    <script type="text/javascript" src="{{ URL::asset('assets/global/plugins/select2/select2.min.js')}}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/global/plugins/datatables/media/js/jquery.dataTables.min.js')}}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js')}}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/global/plugins/jquery-validation/js/jquery.validate.min.js')}}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/global/plugins/jquery-validation/js/additional-methods.min.js')}}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/global/plugins/bootbox/bootbox.min.js')}}"></script>
@stop

@section('page_level_scripts')
    <script src="{{ URL::asset('assets/global/scripts/metronic.js')}}" type="text/javascript"></script>
    <script src="{{ URL::asset('js/layout.js')}}"  type="text/javascript"></script>
    <script src="{{ URL::asset('assets/global/scripts/datatable.js')}}"></script>
@stop

@section('footer')
    <script language="JavaScript" type="text/javascript">
        var url_query_string_ajax='{{url("admin/select-queries/query-string")}}';
        var url_get_all_table_db_ajax='{{url("admin/select-queries/all-table-db")}}';
        var url_get_table_field_name_ajax='{{url("admin/select-queries/table-field-name")}}';
        var url_show_data_table_ajax='{{url("admin/select-queries/show-data-table")}}';
        var url_action_show_data_table_ajax='{{url("admin/select-queries/action-show-data-table")}}';
        var url_previous_show_data_table_ajax='{{url("admin/select-queries/previous-show-data-table")}}';
        var url_download_csv_db='{{url("admin/select-queries/download-csv")}}';
        var token='{{csrf_token()}}';
    </script>
    <script src="{{ URL::asset('pages/scripts/admin/select-queries.js')}}"></script>
@stop