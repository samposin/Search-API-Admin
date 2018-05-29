

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

        var jsver=$("#group-by").val();

		Metronic.startPageLoading();
		jQuery.ajax({
            url: url_analytics_hourly_jsver_ajax,
            type: "post",
            dataType : 'json',
            data: {'date_start':jQuery('input[name=analytics_order_date_start]').val(),'jsver':jsver,'_token': jQuery('input[name=_token]').val()},
            success: function(data){
                var generate_list="";

                var heading='';


                if(data['table_data']['Grand Total']['jsver'].length==0){
                    heading="<tr><td><b>Hour</b></td><td><b>Grand Total</b></td></tr>"
                    generate_list='<tr><td colspan="3" class="text-center"><span class="text-danger">No record found</span></td></tr>'
                    $(".graph-no-data-found").show();
                    $(".graph-show-data").hide();

                }
                else {

                    heading="<tr><td><b>Hours</b></td>";
                    for(key in data['table_data']['Grand Total']['jsver']){

                        heading+="<td><b>"+key+"</b></td>";

                    }
                    heading+="<td><b>Grand Total</b></td></tr>";



                        for (key in data['table_data']) {


                            //console.log(key);
                            generate_list += "<tr><td>" + key + "</td>";
                            var temp = data['table_data'][key];

                            for(key3 in data['table_data']['Grand Total']['jsver']) {
                            //for (key1 in data['table_data'][key]['jsver']) {


                                //var temp1 = temp[key1];
                                console.log(key + "\n");
                                //console.log(key1 + "\n");
                                //console.log(data['table_data'][key]['jsver'][key1] + "\n");
                                //if (key3 == key1) {
                                    generate_list += "<td>" + data['table_data'][key]['jsver'][key3] + "</td>";
                                //}


                                // alert(temp1);


                            //}
                            }
                            generate_list += "<td>" + data['table_data'][key]['horizontal_total'] + "</td></tr>";


                    }
                    //heading='<tr><th>Hour</th>'
                    //for(var jsver=0;  jsver < data['table_data'].length;jsver++){

                      //  alert(data['table_data'][jsver]['jsver'])
                       // heading+='<th>'+data['table_data'][jsver]['jsver']+'</th>'



                    //heading+='<th>Grand Total</th></tr>';

                  //  for (var i = 0; i < data['table_data'].length; i++) {


                        //var generate_data = data[key];
                       // generate_list += "<tr class='list_tr_"+i+"'>" +
                           // "<td>" + data['table_data'][i]+ "</td>" +
                           // "<td>" + data['table_data'][i] + "</td>" +
                           // "<td>" + data['table_data'][i] + "</td>" +

                           // "</tr>";


                   // }



                    var chart = AmCharts.makeChart("chart_1", {
                        "type": "serial",
                        "theme": "light",
                        "legend": {
                            "horizontalGap": 10,
                            "maxColumns": 1,
                            "position": "right",
                            "useGraphSettings": true,
                            "markerSize": 10
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
                            "labelRotation": 90,
                            "minorGridEnabled": true,

                        },
                        "export": {
                            "enabled": true,

                        },
                        "dataProvider":data['graph_data']
                    });


                    $('#chart_1').closest('.portlet').find('.fullscreen').click(function() {
                        chart.invalidateSize();
                    });



                    $(".graph-show-data").show();
                    $(".graph-no-data-found").hide();
                   // generate_list+='<tr><td><b>Total</b></td><td></td>td><td><b>'+data['table_data'][0]['total_sum_clicks']+'</b></td></tr>';

               // }}

                }

                   /*for(key in data){
                     var temp=data[key];

                   }*/
                //alert(data['0']['total_sum_clicks'])


                jQuery("thead").html(heading);
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