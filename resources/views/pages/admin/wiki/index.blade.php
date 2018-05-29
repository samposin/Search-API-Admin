@extends('app')

@section('title', 'Wiki')

@section('page_level_styles')
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/global/plugins/select2/select2.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')}}"/>

    <link rel="stylesheet" href="{{asset('assets/global/css/bootstrap-tagsinput.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/css/app.css')}}">
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
                        <span class="caption-subject font-blue-sharp bold uppercase">wikibaseify </span>

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

                    <div class="alert alert-success" style="display: none">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true" >&times;</button>
                        Successfully Send
                    </div>
                    <div class="row">
                    <form id="wiki_form" method="post" class="form-horizontal" novalidate="novalidate">
                        {!! csrf_field() !!}
                        <div class="col-md-9">

                                <div class="form-group">
                                    <label class="control-label">User<span class="required">*</span></label>
                                    <select id="option" name="user" class="form-control" required="">
                                        @foreach($wiki_users as $wiki_user)
                                            <option value="{{$wiki_user->name}}">{{$wiki_user->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="control-label">Title<span class="required">*</span></label>
                                    <input type="text" class="form-control" name="title" required="" >
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Description<span class="required">*</span></label>
                                    <textarea  class="form-control" name="discription"  style="height: 150px;" required></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label">Keyword<span class="required">*</span></label>
                                    <div class="example example_markup">
                                        <div class="bs-example">
                                            <input type="text" id="tags" value="" name="keyword" data-role="tagsinput" required />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="submit" class="btn blue form-control " value="Submit" id="btn_submit">
                                </div>
                            </div>
                            <div class="col-md-3">

                                <label class="control-label">Category<span class="required">*</span> </label>
                                <div class="category_list"></div>
                            </div>
                    </form>
                </div>
                </div>
            </div>
            <!-- End: life time stats -->
        </div>
    </div>

@stop



@section('page_level_plugins')

        <!--Validation-->
    <script type="text/javascript" src="{{ URL::asset('assets/global/plugins/jquery-validation/js/jquery.validate.min.js')}}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/global/plugins/jquery-validation/js/additional-methods.min.js')}}"></script>
    <!--EndValidation-->

    <!--Tags-->
    <script src="{{URL::asset('assets/global/scripts/bootstrap-tagsinput.js')}}" type="text/javascript"></script>
    <script src="{{URL::asset('assets/global/plugins/tags/app.js')}}" type="text/javascript"></script>
    <!--EndTags-->

    <!--CkEditor -->
    <script src="{{asset('vendor/unisharp/laravel-ckeditor/ckeditor.js')}}"></script>
    <script src="{{asset('vendor/unisharp/laravel-ckeditor/adapters/jquery.js')}}"></script>
    <!--EndCKEditor-->
@stop

@section('page_level_scripts')
    <script src="{{ URL::asset('assets/global/scripts/metronic.js')}}" type="text/javascript"></script>
    <script src="{{ URL::asset('js/layout.js')}}"  type="text/javascript"></script>
    <script src="{{ URL::asset('assets/global/scripts/datatable.js')}}"></script>
@stop

@section('footer')
    <script language="JavaScript" type="text/javascript">

        var csrf_token ='{{ csrf_token() }}';
        var url_wiki_category_ajax='{{url('admin/wiki/category-ajax-show')}}';
        var url_wiki_category_ajax_save='{{url('admin/wiki/category-ajax-save')}}';
        var url_wiki_save='{{url('admin/wiki/save')}}';


    </script>
    <script src="{{ URL::asset('pages/scripts/admin/wiki.js')}}"></script>


@stop