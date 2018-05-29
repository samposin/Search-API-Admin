
jQuery(document).ready(function(){

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

        jQuery.ajax({
            url: url_analytics_daily_all_ajax,
            type: "post",
            dataType : 'json',
            data: {
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
                    var thead = '<tr><th>Date</th><th>Widget</th><th>Total Clicks</th></tr>'
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
                            var thead = '<tr><th>Date</th><th>Publisher</th><th>Total Clicks</th></tr>'
                        }
                        if(data['table_data'][i].field=='sub_dl_source')
                        {
                            var thead = '<tr><th>Date</th><th>Sub Dl Source</th><th>Total Clicks</th></tr>'
                        }
                        if(data['table_data'][i].field=='widget')
                        {
                            var thead = '<tr><th>Date</th><th>Widget</th><th>Total Clicks</th></tr>'
                        }
                        if(data['table_data'][i].field=='api')
                        {
                            var thead = '<tr><th>Date</th><th>Search Feed</th><th>Total Clicks</th></tr>'
                        }
                        if(data['table_data'][i].field=='jsver')
                        {
                            var thead = '<tr><th>Date</th><th>JS Version</th><th>Total Clicks</th></tr>'
                        }
                        if(data['table_data'][i].field=='country_code')
                        {
                            var thead = '<tr><th>Date</th><th>GEO</th><th>Total Clicks</th></tr>'
                        }

                        generate_list += "<tr>"+
                        "<td>" + data['table_data'][i].dates + "</td>" +
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
                        "pathToImages": Metronic.getGlobalPluginsPath() + "amcharts/amcharts/images/",
                        "legend": {
                           // "horizontalGap": 10,
                            //"maxColumns": 1,
                           // "position": "bottom",
                            "useGraphSettings": true,
                           // "markerSize": 10
                        },
                        "chartScrollbar": {
                            "autoGridCount": true,

                            "scrollbarHeight": 40
                        },
                        "marginRight": 40,
                        "marginLeft": 40,
                        "autoMarginOffset": 20,
                        "mouseWheelZoomEnabled":true,
                        "dataDateFormat": "YYYY-MM-DD",
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
                        "categoryField": "dates",
                        "categoryAxis": {
                            "parseDates": true,
                            "dashLength": 1,
                            "minorGridEnabled": true
                        },
                        /*"export": {
						    "enabled": true,
						    "libs": {
						      "path": url_amchart_export_lib
						    },
						    "menu": [ {
						      class: "export-main",
						      label: "Export",
						      menu: [
						        {
						          label: "CSV",
						          click: function() {
						              this.toCSV( {
						                data: data['graph_data']
						              }, function( data ) {
						                this.download( data, this.defaults.formats.CSV.mimeType, "amCharts.csv" );
						              } );
						            }*/
						          /*menu: [ {
						            format: "CSV",
						            label: "Default"
						          }, {
						            label: "First Dataset",
						            click: function() {
						              this.toCSV( {
						                data: data['graph_data']
						              }, function( data ) {
						                this.download( data, this.defaults.formats.CSV.mimeType, "amCharts.csv" );
						              } );
						            }

						          } ]*//*
						        },
						      ]
						    } ]
						  },*/
                        "dataProvider":data['graph_data']
                    });

                    chart.addListener("rendered", zoomChart);
                    zoomChart();

                    function zoomChart() {
                        // different zoom methods can be used - zoomToIndexes, zoomToDates, zoomToCategoryValues
                        chart.zoomToIndexes(data['graph_data'].length - 40, data['graph_data'].length - 1);
                    }

                    $('#chart_1').closest('.portlet').find('.fullscreen').click(function() {
                        chart.invalidateSize();
                    });

                    $(".graph-no-data-found").hide();
                    $(".graph-show-data").show();

                }

                jQuery("thead").html(thead);
                jQuery("tbody").html(generate_list);

                Metronic.stopPageLoading();
            }
        });
    };

    getData();
    changeData();

    jQuery('.btn_daily_ajax').click(function(){
        getData();
    });
});