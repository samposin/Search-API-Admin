

jQuery(document).ready(function(){

	var initPickers = function () {

        //init date pickers
        $('.date-picker').datepicker({
			format: 'mm-dd-yyyy',
            rtl: Metronic.isRTL(),
            autoclose: true
        });

        $(".date-picker").each(function() {
            $(this).datepicker('update', new Date());
            $(this).datepicker('update');
        });
    };

    $("#group-by").change(function(){
        changeData();
    });

    var changeData=function(){

        var change_value=$("#group-by").val()

        jQuery('.div_per_common').hide();
        jQuery('.div_for_'+change_value).show();
        jQuery('.select_per_common').val("");
    }

    var getData=function(){

        Metronic.startPageLoading();
        var group_by=$("#group-by").val();
        var per_geo=$("#per_geo").val();
        var per_publisher=$("#per_publisher").val();
        var per_api=$("#per_api").val();
        var per_jsver=$("#per_jsver").val();
        var per_widget=$("#per_widget").val();
        var per_browser=$("#per_browser").val();
        var date_start=jQuery('input[name=analytics_order_date_start]').val();

        jQuery.ajax({
            url: url_analytics_hourly_all_ajax,
            type: "post",
            dataType : 'json',
            data: {
                'date_start':date_start,
                'group_by':group_by,
                'per_geo':per_geo,
                'per_publisher':per_publisher,
                'per_api':per_api,
                'per_jsver':per_jsver,
                'per_widget':per_widget ,
                'per_browser':per_browser ,
                '_token': csrf_token
			},
            success: function(data){

                var generate_list="";
                var thead="";

                if(data['table_data'].length==0)
                {
                    var thead = '<tr><th>Hour</th><th>Widget</th><th>Total Clicks</th></tr>'
                    generate_list='<tr><td colspan="4" class="text-center"><span class="text-danger">No record found</span></td></tr>'
                    $(".graph-no-data-found").show();
                    $(".graph-show-data").hide();
                    $(".daily-jsver-show-graph").hide();

                }
                else
                {
                    for (i = 0; i < data['table_data'].length; i++)
                    {
                        if(data['table_data'][i].field=='dl_source')
                        {
                            var thead = '<tr><th>Hour</th><th>Publisher</th><th>Total Clicks</th></tr>'
                        }
                        if(data['table_data'][i].field=='sub_dl_source')
                        {
                            var thead = '<tr><th>Hour</th><th>Sub Dl Source</th><th>Total Clicks</th></tr>'
                        }
                        if(data['table_data'][i].field=='widget')
                        {
                            var thead = '<tr><th>Hour</th><th>Widget</th><th>Total Clicks</th></tr>'
                        }
                        if(data['table_data'][i].field=='api')
                        {
                            var thead = '<tr><th>Hour</th><th>Search Feed</th><th>Total Clicks</th></tr>'
                        }
                        if(data['table_data'][i].field=='jsver')
                        {
                            var thead = '<tr><th>Hour</th><th>JS Version</th><th>Total Clicks</th></tr>'
                        }
                        if(data['table_data'][i].field=='country_code')
                        {
                            var thead = '<tr><th>Hour</th><th>GEO</th><th>Total Clicks</th></tr>'
                        }

                        generate_list += "<tr>"+
                        "<td>" + data['table_data'][i].hour_range + "</td>" +
                        "<td>" + data['table_data'][i].total_all + "</td>" +
                        "<td>" + data['table_data'][i].total_clicks + "</td>" +
                        "</tr>";

                        if (i + 1 == data['table_data'].length)
                        {
                            generate_list += '<tr><td><b>Total</b></td><td></td><td><b>' + data['table_data']['0']['total_sum_clicks'] + '</b></td></tr>';
                        }
                    }

                    var chart = AmCharts.makeChart("chart_1", {
                        "type": "serial",
                        "theme": "light",
                        "legend": {
                            //"horizontalGap": 10,
                            //"maxColumns": 1,
                            //"position": "right",
                            "useGraphSettings": true,
                            //"markerSize": 10
                        },
                        "marginRight": 40,
                        "marginLeft": 40,
                        "autoMarginOffset": 20,
                        "mouseWheelZoomEnabled":false,
                        //"dataDateFormat": "HH",
                        "valueAxes": [{
                            "id": "v1",
                            "axisAlpha": 0,
                            "position": "left",
                            // "ignoreAxisWidth":true,
                            "title":"Clicks"
                        }],
                        "balloon": {
                            "borderThickness": 1,
                            "shadowAlpha": 0
                        },
                        "graphs": data['graph_categories'],
                        "chartCursor": {
                            "pan": true,
                            "valueLineEnabled": true,
                            "valueLineBalloonEnabled": true,
                            "cursorAlpha":1,
                            "cursorColor":"#258cbb",
                            "limitToGraph":"g1",
                            "valueLineAlpha":0.2,
                            "valueZoomable":true
                        },
                        "valueScrollbar":{
                            "oppositeAxis":false,
                            "offset":50,
                            "scrollbarHeight":10
                        },
                        "categoryField": "hour_range",
                        "categoryAxis": {
                            "parseDates":false,
                            "dashLength": 1,
                            "labelRotation": 45,
                            "minorGridEnabled": true,

                        },
                        /*"export": {
                            "enabled": true,

                        },*/
                        "dataProvider":data['graph_data']
                    });


                    $('#chart_1').closest('.portlet').find('.fullscreen').click(function() {
                        chart.invalidateSize();
                    });

                    $(".graph-show-data").show();
                    $(".graph-no-data-found").hide();

                }

                jQuery("tbody").html(generate_list);
                jQuery("thead").html(thead);

                Metronic.stopPageLoading();
            }
        });
    };

    initPickers();
    getData();
	changeData();

    jQuery('.btn_hourly_ajax').click(function(){
	    getData();
    });
});