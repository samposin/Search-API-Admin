@extends('app')

@section('title', 'Twenga ')

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
            word-break: break-all

        }
    </style>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Begin: life time stats -->
            <div class="portlet light">
                <div class="portlet-title">

                    <div class="row`">
                        <div class="col-md-4 col-lg-4 col-sm-4">
                            <div class="caption">
                                <i class="fa fa-user font-blue-sharp"></i>
                                <span class="caption-subject font-blue-sharp bold uppercase">Baseify Click Tracker - Daily Report</span>

                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3 col-sm-3">
                            <span style="text-align: center;padding:10px" class="bg-red-intense">All dates/times are PST</span>
                        </div>
                        <div class="col-md-5 col-lg-5 col-sm-5">
                            <div class="pull-right">
                                <a  class="btn-success btn  blue" id="btn_generate_daily_report"   data-toggle="modal" href="" type="button">Generate Daily Report</a>
                                <a  class="btn-success btn  blue" id="btn_send_daily_report"   data-toggle="modal" href="#basic" type="button">Send Daily Report</a>
                                <div id="message-daily-report">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="portlet-body form">
                    @include('flash::message')
                    @include('partials.flash')
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true" >&times;</button>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel-body">
                               {{-- <label>
                                    Select Advertiser
                                    <select class="form-control" id="api" name="api">

                                        <option value="connexity">connexity</option>
                                        <option value="dealspricer">dealspricer</option>
                                        <option value="ebay">ebay</option>
                                        <option value="kelkoo">kelkoo</option>
                                        <option value="twenga">twenga</option>
                                        <option value="zoom">zoom</option>

                                    </select>

                                </label>&nbsp;&nbsp;--}}
                                <label>
                                    Select Month
                                    <select name="month" id="month" class="form-control">
                                        <option value="">All</option>
                                        <option value="1">January</option>
                                        <option value="2">February</option>
                                        <option value="3">March</option>
                                        <option value="4">April</option>
                                        <option value="5">May</option>
                                        <option value="6">June</option>
                                        <option value="7">July</option>
                                        <option value="8">August</option>
                                        <option value="9">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                </label>
                                &nbsp;&nbsp;
                                <label>
                                    Select Publisher
                                    <select name="publisher" id="publisher" class="form-control">
                                        <option value="">All</option>
                                        @foreach($publishers as $publisher)
                                            <option value="{{$publisher->name}}">{{$publisher->name}}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <div class="col-md-12">
                                    <div id="loading" style="text-align:center;display: none"><img src="{{asset('img/loading-spinner-default.gif')}}"></div>
                                    <form name="frm_datatable" id="frm_datatable" method="post">
                                        {!! csrf_field() !!}
                                        <input type="hidden" name="action" id="action" value="">
                                        <div class="table-container">
                                            <table class="table table-striped table-bordered table-hover" id="datatable_ajax">
                                                <thead>
                                                <tr role="row" class="heading">

                                                    <th  class="no-sort">
                                                        Date
                                                    </th>
                                                    <th class="no-sort">
                                                        Dl_Source
                                                    </th>
                                                    <th width="10%" class="no-sort">
                                                        Sub_Dl_Source
                                                    </th>

                                                    <th width="15%" class="no-sort">
                                                        Widgets
                                                    </th>
                                                    <th width="10%" class="no-sort">
                                                        Country Code
                                                    </th>
                                                    <th width="15%" class="no-sort">
                                                        Searches
                                                    </th>

                                                    <th style="width: 220px;" class="no-sort">
                                                        Clicks
                                                    </th>
                                                    <th style="width: 220px;" class="no-sort">
                                                        Estimated_Revenue_In_USD
                                                    </th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- End: life time stats -->
        </div>

    </div>

    <div class="modal fade" id="basic" tabindex="-1" role="basic" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">Sending daily report To</h4>
                    </div>
                    <div class="modal-body">
                        <input type="email" class="form-control email" name="email">
                        <div class="pull-right">You can add upto 5 email ID's seprated with comma</div>

                        </br>
                        </br>
                        <div id="loading" style="text-align:center;display: none"><img src="{{asset('img/loading-spinner-default.gif')}}">
                        </div>
                        <div class="email-success" style="text-align:center"></div>
                        </br>
                        <div style="text-align: center">From &nbsp; <span id="fromdate"></span> &nbsp; To&nbsp; <span id="todate"></span>
                            <br> <br>You can <a  id="csv_download">Download</a> CSV file to cross check
                        </div>
                        <br>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn default" data-dismiss="modal">Cancel</button>
                        <button  type="button" class="btn blue" id="btn_email_daily_report">Email Daily Report</button>
                    </div>
                </form>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
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
        var csrf_token ='{{ csrf_token() }}';
        var url_twenga_show_ajax='{{url('admin/twenga/show')}}';
        var  url_twenga_generate_daily_report_ajax='{{url('admin/twenga/generate-daily-report')}}';
        var  url_twenga_download_ajax='{{url('admin/twenga/download')}}';
        var  url_twenga_email_send_ajax='{{url('admin/twenga/email_send')}}';
    </script>
    <script src="{{ URL::asset('pages/scripts/admin/twenga.js')}}"></script>
@stop