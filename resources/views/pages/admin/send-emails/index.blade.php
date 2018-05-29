@extends('app')

@section('title', 'Analytics')

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
                        <i class="icon-envelope-open font-blue-sharp"></i>
                        <span class="caption-subject font-blue-sharp bold uppercase">Send Email</span>

                    </div>
                    <div class="actions">
                        <div class="btn-group">
                            <button class="btn blue btn_daily" type="button">Daily</button>&nbsp;&nbsp;

                        </div>
                    <div class="btn-group">
                        <button class="btn green btn_monthly" type="button">Monthly</button>


                    </div></div>

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
                        <form name="frm_datatable" id="frm_datatable" class="form-horizontal" enctype="multipart/form-data" action="{{url('admin/email/send')}}" method="post">
                            {!! csrf_field() !!}
                            <input type="hidden" name="action" id="action" value="">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Email ID</label>
                                    <div class="col-md-8">
                                        <input type="text" placeholder="Email ID" class="form-control" name="to">

                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Subject</label>
                                    <div class="col-md-8">
                                        <input type="text" placeholder="Subject" name="subject" class="form-control">

                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label">Message</label>
                                    <div class="col-md-8">
                                    <textarea class="form-control" name="message"></textarea>

                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label"></label>
                                    <div class="col-md-8">

                                        <input type="file"  name="file_attachment" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label"></label>
                                    <div class="col-md-8">
                                        <input class="btn green" type="submit" value="Send Email">

                                    </div>
                                </div>








                                <!--/span-->

                            </div>

                            <!--/row-->

                        </form>

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
                var csrf_token ='{{ csrf_token() }}';
                var url_get_states='{{url('api/get_states')}}';
                var url_get_advertiser_widgets='{{url('api/get_advertiser_widgets')}}';
            </script>
            <script src="{{ URL::asset('pages/scripts/admin/email-index.js')}}"></script>
@stop


