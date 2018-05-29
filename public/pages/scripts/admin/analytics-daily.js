

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
         getData();



    };

    var getData=function(){

		Metronic.startPageLoading();
		jQuery.ajax({
            url: url_analytics_daily_ajax,
            type: "post",
            dataType : 'json',
            data: {'date_start':jQuery('input[name=analytics_order_date_start]').val(),'date_end':jQuery('input[name=analytics_order_date_end]').val(),  '_token': jQuery('input[name=_token]').val()},
            success: function(data){
                var generate_list="";

                if(data.length==0){
                    generate_list='<tr><td colspan="3" class="text-center"><span class="text-danger">No record found</span></td></tr>'
                    $(".graph-no-data-found").show();
                    $(".graph-show-data").hide();


                }
                else {
                    for (var i = 0; i < data.length; i++) {
                        //var generate_data = data[key];
                        generate_list += "<tr>" +
                            "<td>" + data[i].dates + "</td>" +
                            "<td>" + data[i].total_clicks + "</td>" +
                           // "<td>" + data[i].total_searches + "</td>" +
                            "</tr>";
                        //console.log(generate_list);

                    }
                     //var dataProvider=array();



                    var chart = AmCharts.makeChart("chart_1", {
                        "theme": "light",
                        "type": "serial",
                        "dataProvider": data,
                        "valueAxes": [{
                            "unit": "",
                            "position": "left",
                            "title": "Clicks",
                        }],
                        "startDuration": 1,
                        "graphs": [{
                            "balloonText": "Total Clicks [[category]] : <b>[[value]]</b>",
                            "fillAlphas": 0.9,
                            "lineAlpha": 0.2,
                            "title": "2004",
                            "type": "column",
                            "valueField": "total_clicks"
                        }],
                        "plotAreaFillAlphas": 0.1,
                        "categoryField": "dates",
                        "categoryAxis": {
                            "gridPosition": "start"
                        },
                        "export": {
                            "enabled": true
                        }

                    });
                    /*var chart = AmCharts.makeChart("chart_2", {
                        "theme": "light",
                        "type": "serial",
                        "dataProvider":data,
                        "valueAxes": [{
                            "unit": "",
                            "position": "left",
                            "title": " Searches",
                        }],
                        "startDuration": 1,
                        "graphs": [{
                            "balloonText": "Total Searches [[category]] : <b>[[value]]</b>",
                            "fillAlphas": 0.9,
                            "lineAlpha": 0.2,
                            "title": "2004",
                            "type": "column",
                            "valueField": "total_searches"
                        }],
                        "plotAreaFillAlphas": 0.1,
                        "categoryField": "dates",
                        "categoryAxis": {
                            "gridPosition": "start"
                        },
                        "export": {
                            "enabled": true
                        }

                    });
                    var chart = AmCharts.makeChart("chart_3", {
                        "theme": "light",
                        "type": "serial",
                        "dataProvider": data,
                        "valueAxes": [{
                            "unit": "",
                            "position": "left",
                            "title": "Clicks and Searches",
                        }],
                        "startDuration": 1,
                        "graphs": [{
                            "balloonText": "Total Clicks [[category]] : <b>[[value]]</b>",
                            "fillAlphas": 0.9,
                            "lineAlpha": 0.2,
                            "title": "2004",
                            "type": "column",
                            "valueField": "total_clicks"
                        }, {
                            "balloonText": "Total Searches [[category]] : <b>[[value]]</b>",
                            "fillAlphas": 0.9,
                            "lineAlpha": 0.2,
                            "title": "2005",
                            "type": "column",
                            "clustered":false,
                            "columnWidth":0.5,
                            "valueField": "total_searches"
                        }],
                        "plotAreaFillAlphas": 0.1,
                        "categoryField": "dates",
                        "categoryAxis": {
                            "gridPosition": "start"
                        },
                        "export": {
                            "enabled": true
                        }

                    });
                      /* var chart = AmCharts.makeChart("chart_1", {
                            "type": "serial",
                            "theme": "light",
                            "pathToImages": Metronic.getGlobalPluginsPath() + "amcharts/amcharts/images/",
                            "autoMargins": false,
                            "marginLeft": 30,
                            "marginRight": 8,
                            "marginTop": 10,
                            "marginBottom": 26,

                            "fontFamily": 'Open Sans',
                            "color":    '#888',

                            dataProvider:data,
                            "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                            "startDuration": 1,
                            "graphs": [{
                                "alphaField": "alpha",
                                "balloonText": "<span style='font-size:13px;'>[[title]] in [[category]]:<b>[[value]]</b> [[additional]]</span>",
                                "dashLengthField": "dashLengthColumn",
                                "fillAlphas": 1,
                                "title": "Total Clicks",
                                "type": "column",
                                "valueField": "total_clicks"
                            }, {
                                "balloonText": "<span style='font-size:13px;'>[[title]] in [[category]]:<b>[[value]]</b> [[additional]]</span>",
                                "bullet": "round",
                                "dashLengthField": "dashLengthLine",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "title": "Total Clicks",
                                "valueField": "total_clicks"
                            }],
                            "categoryField": "dates",
                            "categoryAxis": {
                                "gridPosition": "start",
                                "axisAlpha": 0,
                                "tickLength": 0
                            }
                        });*/

                    $('#chart_1').closest('.portlet').find('.fullscreen').click(function() {
                        chart.invalidateSize();
                    });
                    //$('#chart_2').closest('.portlet').find('.fullscreen').click(function() {
                        chart.invalidateSize();
                    //});
                   // $('#chart_3').closest('.portlet').find('.fullscreen').click(function() {
                        chart.invalidateSize();
                   // });
                    $(".graph-no-data-found").hide();
                    $(".graph-show-data").show();

                    /*for(key in data){

                        var temp=data[key];

                    }*/
                    generate_list+='<tr><td><b>Total</b></td><td><b>'+data['0']['total_sum_clicks']+'</b></td></tr>';
                }



                jQuery("tbody").html(generate_list);
                Metronic.stopPageLoading();
            }
        });

    };

    initPickers();

    jQuery('.btn_daily_ajax').click(function(){
	    getData();

    });
});