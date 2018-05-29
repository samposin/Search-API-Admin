

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
            url: url_analytics_hourly_ajax,
            type: "post",
            dataType : 'json',
            data: {'date_start':jQuery('input[name=analytics_order_date_start]').val(),'_token': jQuery('input[name=_token]').val()},
            success: function(data){
                var generate_list="";


                if(data.length==0){
                    generate_list='<tr><td colspan="3" class="text-center"><span class="text-danger">No record found</span></td></tr>'
                    $(".graph-no-data-found").show();

                }
                else {
                    for (var i = 0; i < data.length; i++) {
                        //var generate_data = data[key];
                        generate_list += "<tr class='list_tr_"+i+"'>" +
                            "<td>" + data[i].hour_range + "</td>" +
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
                          }, ],
                          "plotAreaFillAlphas": 0.1,
                          "categoryField": "hour_range",
                          "categoryAxis": {
                              "gridPosition": "start",
                              "labelRotation": 90,
                              "equalSpacing" : true

                          },
                          "export": {
                              "enabled": true
                          }
                        });
                    /*var chart = AmCharts.makeChart("chart_2", {
                        "theme": "light",
                        "type": "serial",
                        "dataProvider": data,
                        "valueAxes": [{
                            "unit": "",
                            "position": "left",
                            "title": "Searches",
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
                        "categoryField": "hour_range",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "labelRotation": 90,
                            "equalSpacing" : true

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
                        "categoryField": "hour_range",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "labelRotation": 90,
                            "equalSpacing" : true

                        },
                        "export": {
                            "enabled": true
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
                    }

                   /*for(key in data){
                     var temp=data[key];

                   }*/
                //alert(data['0']['total_sum_clicks'])
                generate_list+='<tr><td><b>Total</b></td><td><b>'+data['0']['total_sum_clicks']+'</b></td></tr>';


                jQuery("tbody").html(generate_list);
                Metronic.stopPageLoading();
            }
        });

    };

    initPickers();

    jQuery('.btn_hourly_ajax').click(function(){
	    getData();

    });
});