@extends('app')

@section('title', 'Wiki')

@section('page_level_styles')


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
            <div class="portlet light">
                <div class="portlet-title">
                    <div class="col-md-10">
                        <div class="caption">
                            <i class="icon-speech font-blue-sharp"></i>
                            <span class="caption-subject font-blue-sharp bold uppercase">Wiki </span>
                        </div>
                    </div>
                    <div class="col-md-2 pull-right">

                        <div class="form-group">
                            <a href="{{url('admin/wiki/add-new')}}" class="btn blue  ">Add New Wiki</a>
                        </div>
                    </div>


                </div>
                <div class="portlet-body">
                    <div class="row">
                        <div class="col-md-12 blog-page">
                            <div class="row">
                                <div class="col-md-12 col-sm-8 article-block">
                                    @foreach($wiki_saves as $wiki_post)
                                        <div class="row">

                                            <div class="col-md-4 blog-img blog-tag-data">

                                                <ul class="list-inline">
                                                    <li>
                                                        <i class="font-blue-sharp fa fa-calendar"></i>

                                                        <span class="font-blue-sharp ">{{$wiki_post->date}}</span>
                                                    </li>
                                                    <li>
                                                        <i class="font-blue-sharp fa fa-user"></i>

                                                        <span class="font-blue-sharp ">{{$wiki_post->user}}</span>
                                                    </li>
                                                </ul>
                                                <ul class="list-inline blog-tags">
                                                    <li>
                                                        <i class="font-blue-sharp fa fa-tags"></i>
                                                        <?php  $keyword_array=explode(',', $wiki_post->keyword);
                                                        ?>
                                                        @foreach($keyword_array as $keyword)
                                                            <span class="badge label-default badge-roundless">{{$keyword}} </span>
                                                        @endforeach
                                                    </li>
                                                </ul>


                                                <ul class="list-inline blog-tags">
                                                    <li>
                                                        <i class="font-blue-sharp fa fa-folder-open"></i>
                                                        <span class="">{{$wiki_post->category_name}} </span>
                                                    </li>

                                                </ul>
                                            </div>
                                            <div class="col-md-8 blog-article">
                                                <h3 class="font-blue-sharp">

                                                    {{ucfirst(strtolower($wiki_post->title))}}
                                                </h3>
                                                <p>
                                                    {!!$wiki_post->description !!}

                                                </p>

                                            </div>
                                        </div>
                                        <hr>
                                    @endforeach


                                </div>
                                <!--end col-md-9-->

                                <!--end col-md-3-->
                            </div>
                            <div class="pull-right"> {!! $wiki_saves->render() !!}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- Begin: life time stats -->

    </div>

@stop

@section('page_level_plugins')

@stop

@section('page_level_scripts')
    <script src="{{ URL::asset('assets/global/scripts/metronic.js')}}" type="text/javascript"></script>
    <script src="{{ URL::asset('js/layout.js')}}"  type="text/javascript"></script>

@stop

@section('footer')
    <script language="JavaScript" type="text/javascript">

        var csrf_token ='{{ csrf_token() }}';
        var url_get_states='{{url('api/get_states')}}';
        var url_get_advertiser_widgets='{{url('api/get_advertiser_widgets')}}';

    </script>
@stop