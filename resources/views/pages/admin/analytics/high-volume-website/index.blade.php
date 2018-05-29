@extends('app')

@section('title', 'Analytics - High Volume Websites')

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
                        <span class="caption-subject font-blue-sharp bold uppercase">High Volume Websites</span>
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
                        <div class="row">
                            <div class="alert alert-danger">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true" >&times;</button>
                                <p>(Reports available from Jun 02  onwards only)</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <form name="frm_datatable" id="frm_datatable" method="post">
                                {!! csrf_field() !!}
                                <input type="hidden" name="action" id="action" value="">
                                <div class="table-container">
                                    <table class="table table-striped table-bordered table-hover" id="datatable_ajax">
                                        <thead>
                                            <tr role="row" class="heading">
                                                <th>
                                                    Domain Name;#
                                                </th>
                                                <th>
                                                    Widget Name
                                                </th>
                                                <th width="15%" class="no-sort">
                                                    Publisher Name
                                                </th>
                                                <th width="10%">
                                                    Number of Clicks
                                                </th>
                                                <th width="8%" class="no-sort">
                                                    Geo
                                                </th>
                                                <th width="20%" class="no-sort">
                                                    Clicks of last 30 days
                                                </th>
                                                <th style="width: 220px;" class="no-sort">
                                                    Created
                                                </th>
                                            </tr>
                                            <tr role="row" class="filter">
                                                <td><input type="text" name="domain_name" class="form-control form-filter input-sm"></td>
                                                <td></td>
                                                <td>
                                                    <select id="per_dl_source" name="per_dl_source" class="form-control form-filter">
                                                        <option value="">All</option>
                                                        @foreach($per_dl_source as $k)
                                                            @if($k->name=='ALL')
                                                            @else
                                                                <option value="{{$k->name}}">{{$k->name}}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td></td>
                                                <td>
                                                    <select id="per_geo" name="per_geo" class="form-control form-filter">
                                                        <option value="">All</option>
                                                        @foreach($per_geo_arr as $k=>$v)
                                                            <option value="{{$k}}">{{$k}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td></td>
                                                <td>
                                                    <div class="input-group  date date-picker margin-bottom-5 pull-left " style="width: 130px;"   data-date-format="dd/mm/yyyy">
                                                        <input type="text" class="form-control form-filter input-sm"  name="date_start"  readonly id="date_start">
                                                        <input type="hidden" class="form-control hdn-form-filter input-sm" name="hdn_date_start_from">
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-sm default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                    <div class="margin-bottom-5 pull-right">
                                                        <button class="btn btn-sm yellow filter-submit margin-bottom"><i class="fa fa-search"></i> Search</button>
                                                    </div>
                                                </td>
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

    <!--Sparkline-->
    <script type="text/javascript" src="{{ URL::asset('assets/global/plugins/sparkline/jquery.sparkline.js')}}"></script>
@stop

@section('page_level_scripts')
    <script src="{{ URL::asset('assets/global/scripts/metronic.js')}}" type="text/javascript"></script>
    <script src="{{ URL::asset('js/layout.js')}}"  type="text/javascript"></script>
    <script src="{{ URL::asset('assets/global/scripts/datatable.js')}}"></script>
@stop

@section('footer')
    <script language="JavaScript" type="text/javascript">
        var csrf_token ='{{ csrf_token() }}';
        var url_analytics_high_volume_website_ajax='{{url('admin/analytics/high-volume-website-ajax')}}';
    </script>

    <script src="{{ URL::asset('pages/scripts/admin/analytics-high-volume-website.js')}}"></script>
@stop