@extends('app')

@section('title', 'Analytics - Clicks Per hour')

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
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Begin: life time stats -->
            <div class="portlet light">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-user font-blue-sharp"></i>
                        <span class="caption-subject font-blue-sharp bold uppercase">Clicks Per hour</span>

                    </div>

                </div>
                <div class="portlet-body form">
                    {{-- qw
                      --}}
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

                    <div class="form-body">
                        <div class="row">
                            <div class="alert alert-danger">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true" >&times;</button>
                                <p>(Reports available from May 08 onwards only)</p>
                            </div>

                            <form name="frm_datatab" id="frm_datatable" method="post">
                                {!! csrf_field() !!}
                                <input type="hidden" name="action" id="action" value="">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label class="control-label">Start:</label>
                                        <div class="input-group date date-picker margin-bottom-5" data-date-format="dd/mm/yyyy">
                                            <input type="text" class="form-control form-filter input-sm"  name="analytics_order_date_start" placeholder="">
											<span class="input-group-btn">
											<button class="btn btn-sm default" type="button"><i class="fa fa-calendar"></i></button>
											</span>
                                            <input type="hidden" name="hdn_analytics_click_date_start" class="form-control hdn-form-filter input-sm">
                                        </div>
                                    </div></div>
                                <!--- <div class="form-group">
                                     <label class="control-label">Time Peroid</label>
                                     <select class="form-control form-filter" name="analytics_clicks_dropdown" id="analytics_clicks_dropdown">

                                         <option value="custom">Custom</option>
                                         <option value="week">Week</option>
                                         <option value="month">Month</option>
                                         <option value="quarterly">Quarterly </option>


                                     </select>
                                     <input type="hidden" name="hdn_anaylytics_clicks" class="form-control hdn-form-filter input-sm">
                                 </div>-->


                                <div class="col-md-2">
                                    <div class="form-group">
                                        <div><label class="control-label"></label></div>
                                        <button  class="btn blue btn_hourly_ajax" type="button">Show</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                            <!--/row-->


                        <div class="row">

                            <div class="col-md-12">


                                <!-- BEGIN CHART PORTLET-->
                                <div class="portlet box yellow">

                                    <div class="portlet-title">
                                        <div class="caption">
                                            <i class="icon-bar-chart "></i>
                                            <span class="caption-subject bold uppercase "> Sum of clicks</span>
                                            <!--<span class="caption-helper">column and line mix</span>-->
                                        </div>
                                        <div class="tools">
                                            <a href="javascript:;" class="collapse">
                                            </a>
                                            <!-- <a href="#portlet-config" data-toggle="modal" class="config">
                                             </a>
                                             <a href="javascript:;" class="reload">
                                             </a>-->
                                            <a href="javascript:;" class="fullscreen">
                                            </a>
                                            <!--<a href="javascript:;" class="remove">
                                            </a>-->
                                        </div>

                                    </div>
                                    <div class="portlet-body">
                                        <div class="text-center"><h4><b>Clicks Over Time</b></h4></div>
                                        <div class="text-center graph-no-data-found" style="display:none"><h3 class="text-danger">No Record Found</h3></div>

                                        <div id="chart_1" class="chart" style="height: 500px;">
                                        </div>
                                    </div>
                                </div>
                                <!-- END CHART PORTLET-->


                                <!-- BEGIN CHART PORTLET
                                <div class="portlet box yellow">

                                    <div class="portlet-title">
                                        <div class="caption">
                                            <i class="icon-bar-chart "></i>
                                            <span class="caption-subject bold uppercase "> Sum of searches</span>

                                        </div>
                                        <div class="tools">
                                            <a href="javascript:;" class="collapse">
                                            </a>

                                            <a href="javascript:;" class="fullscreen">
                                            </a>

                                        </div>

                                    </div>
                                    <div class="portlet-body">
                                        <div class="text-center"><h4><b>Searches Over Time</b></h4></div>
                                        <div class="text-center graph-no-data-found" style="display:none"><h3 class="text-danger">No Record Found</h3></div>

                                        <div id="chart_2" class="chart" style="height: 500px;">
                                        </div>
                                    </div>
                                </div>-->
                                <!-- END CHART PORTLET-->

                                <!-- BEGIN CHART PORTLET
                                <div class="portlet box yellow">

                                    <div class="portlet-title">
                                        <div class="caption">
                                            <i class="icon-bar-chart "></i>
                                            <span class="caption-subject bold uppercase "> Sum of clicks and searches</span>

                                        </div>
                                        <div class="tools">
                                            <a href="javascript:;" class="collapse">
                                            </a>

                                            <a href="javascript:;" class="fullscreen">
                                            </a>

                                        </div>


                                    <div class="portlet-body">
                                        <div class="text-center"><h4><b>Clicks and Searches Over Time</b></h4></div>
                                        <div class="text-center graph-no-data-found" style="display:none"><h3 class="text-danger">No Record Found</h3></div>

                                        <div id="chart_3" class="chart" style="height: 500px;">
                                        </div>
                                    </div>
                                </div>
                                <!-- END CHART PORTLET-->

                            </div>

                        </div>

                        <div class="row">



                            <div class="col-md-8">
                                <div class="table-container">
                                    <div class="table-scrollable">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                            <tr>
                                                <th width="50%">
                                                    Date
                                                </th>
                                                <th>
                                                    Total Clicks
                                                </th>
                                               <!-- <th>
                                                    Total Searches
                                                </th>-->
                                            </tr>
                                            </thead>
                                            <tbody><tr><td colspan="3" class="text-center"><span class="text-danger">Please Select Date</span></td></tr></tbody>
                                        </table>
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
            <script src="{{ URL::asset('assets/global/plugins/amcharts/amcharts/amcharts.js')}}" type="text/javascript"></script>
            <script src="{{ URL::asset('assets/global/plugins/amcharts/amcharts/serial.js')}}" type="text/javascript"></script>
            <script src="{{ URL::asset('assets/global/plugins/amcharts/amcharts/pie.js')}}" type="text/javascript"></script>
            <script src="{{ URL::asset('assets/global/plugins/amcharts/amcharts/radar.js')}}" type="text/javascript"></script>
            <script src="{{ URL::asset('assets/global/plugins/amcharts/amcharts/themes/light.js')}}" type="text/javascript"></script>
            <script src="{{ URL::asset('assets/global/plugins/amcharts/amcharts/themes/patterns.js')}}" type="text/javascript"></script>
            <script src="{{ URL::asset('assets/global/plugins/amcharts/amcharts/themes/chalk.js')}}" type="text/javascript"></script>
            <script src="{{ URL::asset('assets/global/plugins/amcharts/ammap/ammap.js')}}" type="text/javascript"></script>
            <script src="{{ URL::asset('assets/global/plugins/amcharts/ammap/maps/js/worldLow.js')}}" type="text/javascript"></script>
            <script src="{{ URL::asset('assets/global/plugins/amcharts/amstockcharts/amstock.js')}}" type="text/javascript"></script>
            <script type="text/javascript" src="{{ URL::asset('assets/global/plugins/charts/charts-amcharts.js')}}"></script>

        @stop

        @section('page_level_scripts')
            <script src="{{ URL::asset('assets/global/scripts/metronic.js')}}" type="text/javascript"></script>
            <script src="{{ URL::asset('js/layout.js')}}"  type="text/javascript"></script>
            <script src="{{ URL::asset('assets/global/scripts/datatable.js')}}"></script>
        @stop

        @section('footer')
            <script language="JavaScript" type="text/javascript">
                ChartsAmcharts.init();

                var csrf_token ='{{ csrf_token() }}';
                var url_get_states='{{url('api/get_states')}}';
                var url_get_advertiser_widgets='{{url('api/get_advertiser_widgets')}}';
                var url_analytics_hourly_ajax='{{url('admin/analytics/hourly-ajax')}}';
            </script>
            <script src="{{ URL::asset('pages/scripts/admin/analytics-hourly.js')}}"></script>


@stop