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
                        <form name="frm_datatab" id="frm_datatable" method="post">
                            {!! csrf_field() !!}
                            <div class="row">
                                <input type="hidden" name="action" id="action" value="">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label">Start:</label>
                                        <div class="input-group date date-picker " data-date-format="dd/mm/yyyy">
                                            <input type="text" class="form-control form-filter"  name="analytics_order_date_start" placeholder="">
											<span class="input-group-btn">
											<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
											</span>
                                            <!--<input type="hidden" name="hdn_analytics_click_date_start" class="form-control hdn-form-filter ">-->
                                        </div>
                                    </div>
                                </div>
                            <!--</div>
                            <div class="row">-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <div><label class="control-label">Group By </label></div>
                                        <select id="group-by" name="group-by" class="form-control">
                                            <option value="widget">Widget</option>
                                            <option value="jsver">JS Version</option>
                                            <option value="country_code">GEO</option>
                                            <option value="api">Search Feed</option>
                                            <option value="dl_source">Publisher</option>
                                            <option value="browser">Browser</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 div_per_common div_for_browser div_for_widget div_for_jsver div_for_api div_for_dl_source" id="per_geo_display">
                                    <div class="form-group">
                                        <div><label class="control-label" >Per Geo </label></div>
                                        <select id="per_geo" name="per_geo" class="form-control select_per_common">
                                            <option value="">All</option>
                                            @foreach($per_geo_arr as $k=>$v)
                                                <option value="{{$k}}">{{$k}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 div_per_common div_for_browser div_for_widget div_for_jsver div_for_api div_for_country_code" id="per_publisher_display">
                                    <div class="form-group">
                                        <div><label class="control-label">Per Publisher </label></div>
                                        <select id="per_publisher" name="per_publisher" class="form-control select_per_common">
                                            <option value="">All</option>
                                            @foreach($per_publisher as $puslisher)
                                                @if($puslisher->name=='N/A')
                                                    <?php $na=$puslisher->name;?>
                                                @else
                                                    <option value="{{$puslisher->name}}">{{$puslisher->name}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 div_per_common div_for_browser div_for_widget div_for_jsver div_for_dl_source div_for_country_code" id="per_api_display">
                                    <div class="form-group">
                                        <div><label class="control-label">Per API </label></div>
                                        <select id="per_api" name="per_api" class="form-control select_per_common">
                                            <option value="">All</option>
                                            @foreach($per_api as $api)
                                                @if($api->api=='N/A')
                                                    <?php $na=$api->api;?>
                                                @else
                                                    <option value="{{$api->api}}">{{$api->api}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 div_per_common div_for_browser div_for_widget div_for_api div_for_dl_source div_for_country_code" id="per_jsver_display">
                                    <div class="form-group">
                                        <div><label class="control-label">Per jsver </label></div>
                                        <select id="per_jsver" name="per_jsver" class="form-control select_per_common">
                                            <option value="">All</option>
                                            @foreach($per_jsver as $jsver)
                                                @if($jsver->jsver=='N/A')
                                                    <?php $na=$jsver->jsver;?>
                                                @else
                                                    <option value="{{$jsver->jsver}}">{{$jsver->jsver}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 div_per_common div_for_browser div_for_jsver div_for_api div_for_dl_source div_for_country_code" id="per_widget_display" style="display: none">
                                    <div class="form-group">
                                        <div><label class="control-label">Per Widget </label></div>
                                        <select id="per_widget" name="per_widget" class="form-control select_per_common">
                                            <option value="">All</option>
                                            @foreach($per_widget as $widget)
                                                @if($widget->widget=='N/A')
                                                    <?php $na=$widget->widget;?>
                                                @else
                                                    <option value="{{$widget->widget}}">{{$widget->widget}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 div_per_common div_for_widget div_for_jsver div_for_api div_for_dl_source div_for_country_code" id="per_browser_display" >
                                    <div class="form-group">
                                        <div><label class="control-label">Per Browser</label></div>
                                        <select id="per_browser" name="per_browser" class="form-control select_per_common">
                                            <option value="">All</option>
                                            @foreach($per_browser as $browser)
                                                @if($browser->browser=='N/A')
                                                    <?php $na=$widget->widget;?>
                                                @else
                                                <option value="{{$browser->browser}}">{{$browser->browser}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <div><label class="control-label">&nbsp;</label></div>
                                        <button  class="btn blue btn_hourly_ajax" type="button">Show</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="graph-show-data">
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
                                            <div id="chart_1" class="chart" style="height: 600px;"></div>
                                        </div>
                                    </div>
                                    <!-- END CHART PORTLET-->
                                </div>
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
                                                    Hour
                                                </th>
                                                <th>
                                                    Widget
                                                </th>
                                                <th>
                                                    Total Clicks
                                                </th>
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
        var url_analytics_hourly_all_ajax='{{url('admin/analytics/hourly-all-ajax')}}';
    </script>
    <script src="{{ URL::asset('pages/scripts/admin/analytics-hourly-all.js')}}"></script>
@stop