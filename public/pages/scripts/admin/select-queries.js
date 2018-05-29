var get_all_table_db= function(){ //show all table dropdown list
	$.ajax({
		type: 'post',
		url: url_get_all_table_db_ajax,
		dataType: 'json',
		data: {
			'_token': token,
		},
		success: function (data) {
			var all_table_name_db = '';
			if (data['success'] == "0") {
				all_table_name_db += '<tr><td colspan="4"  style="text-align:center;color:red;font-size: 20px;">' + data['message'] + '</td></tr>';
			}
			if(data['success']=="1"){
				for (var i = 0; i < data['data'].length; i++) {
					$.each(data['data'][i], function (index, value) {
						all_table_name_db += '<option value="' + value + '">' + value + '</option>';
					});
				}
			}
			$("#table_name_db").html(all_table_name_db);
		}
	});
}

var get_show_previous_data_table=function(){//show previous query table
	$.ajax({
		type: 'post',
		url: url_previous_show_data_table_ajax,
		dataType: 'json',
		data: {

			'_token': $('input[name=_token]').val(),
		},
		success: function (data) {
			var html='';
			if(data['success']=="0") {
				html += '<tr><td colspan="4"  style="text-align:center;color:red;font-size: 20px;">' + data['message'] + '</td></tr>';
			}

			if(data['success']=="1") {
				for (key in data['data']) {
					var temp = data['data'][key];
					html += '<tr><td>' + temp.created_at + '</td>';
					html += '<td id="' + temp.id + '">' + temp.name_query + '</td>';
					html += '<td>'+temp.last_time_results+'</td>';
					html += '<td><button type="button" class="btn btn-info" id="act"  onclick="btn_action_show_data_table(' + temp.id + ');" >Action</button></td> </tr>';
				}
			}
			$('.select').html(html);

		}
	});
}
var get_query_string=function() {//show query string

	$.ajax({
		type: 'post',
		url: url_query_string_ajax,
		dataType: 'json',
		data: {
			'_token'   : $('input[name=_token]')   .val(),
			'select_field_name' : $('input[name=select_field_name]').val(),
			'table_name'    : $('select[name=table_name]').val(),
			'where-clause-string': $('input[name=where-clause-string]').val(),
		},
		success: function (data) {
			$('#query_string_show').show();
			$('.btn_get_result').show();
			$('#query_string_show').html(data['query_string']);
		}
	});
}
var get_show_data_table= function(){//show data table

	var sql_query_string= $("#query_string_show").html();
	$.ajax({
		type: 'post',
		url: url_show_data_table_ajax,
		dataType: 'json',
		data: {
			'_token': $('input[name=_token]').val(),
			'sql_query_string':sql_query_string,
		},
		success: function (data) {
			var previousshowdatatablehtml='';
			var column_table='';
			var showdatatable='';
			var temp1='';
			var temp2='';
			if(data['success']=="0"){

				showdatatable += '<tr><td  style="text-align:center;color:red;font-size: 20px;">' + data['message']+ '</td></tr>';
				$('#columns').html(column_table);
				$('.get_show').html(showdatatable);
				$('#btn_download_csv').hide();

			}

			if(data['success']=="1"){
				for (key in data['previousshowdatatable']) {

					var previousshowdatatable = data['previousshowdatatable'][key];
					previousshowdatatablehtml += '<tr><td>' + previousshowdatatable.created_at + '</td>';
					previousshowdatatablehtml += '<td id="' + previousshowdatatable.id + '">' + previousshowdatatable.name_query + '</td>';
					previousshowdatatablehtml += '<td>'+ previousshowdatatable.last_time_results + '</td>';
					previousshowdatatablehtml += '<td><button type="button" class="btn btn-info" id="action"  onclick="btn_action_show_data_table(' + previousshowdatatable.id + ');" >Action</button></td> </tr>';
				}


				for (var i = 0; i < data['showdatatable'].length; i++) {

					showdatatable += '<tr>';
					$.each(data['showdatatable'][i], function (index, value) {

						if (i == data['showdatatable'].length - 1) {

							column_table += '<td style="background-color: #B0BEC5;font-style:normal">' + index + '</td>';
						}
						showdatatable += '<td class="break_word">' + value + '</td>';
					});
					showdatatable += '</tr>';
				}

				$('.select').html(previousshowdatatablehtml);
				$('#columns').html(column_table);
				$('.get_show').html(showdatatable);
				$('#btn_download_csv').show();

				//concat url Download CSV File DB
				var query_id="?query_id="+data['query_id'];
			 	var url=url_download_csv_db+query_id;
				$('#btn_download_csv').attr('href',url);
			}

		}
	});
}
function btn_action_show_data_table(action) { //show data table on button action
	var sql_query_string= $("#"+action).html();

	$.ajax({
		type: 'post',
		url: url_action_show_data_table_ajax,
		dataType: 'json',
		data: {
			'_token'   : $('input[name=_token]').val(),
			'id':action,

		},
		success: function(data) {
			var showdatatable = '';
			var column_table = '';

			if (data['success'] == "0"){
				showdatatable += '<tr><td colspan="4"  style="text-align:center;color:red;font-size: 20px;">' + data['message'] + '</td></tr>';

			}
			if (data['success'] == "1"){
				for (var i = 0; i < data['data'].length; i++) {

					showdatatable += '<tr>';
					$.each(data['data'][i], function (index, value) {

						if (i == data['data'].length - 1) {

							column_table += '<td  style="background-color: #B0BEC5;font-style:normal">' + index + '</td>';
						}
						showdatatable += '<td class="break_word">' + value + '</td>';
					});
					showdatatable += '</tr>';
				}
		    }

			$('#columns').html(column_table);
			$('.get_show').html(showdatatable);
			$('#btn_download_csv').show();
			$('#query_string_show').show();
			var query_id="?query_id="+action;
			var url=url_download_csv_db+query_id;

			$('#btn_download_csv').attr('href',url);
			$(".btn_get_result").show();
			$('#query_string_show').html(sql_query_string);
		}
	});
}

$(document ).ready(function() {

	get_all_table_db();//call show all table
	get_show_previous_data_table();//call show previous Query table

	$('#btn_go').click(function(){
		get_query_string();//call get query string
	});

	$('.btn_get_result').click(function(){
		get_show_data_table();//call show data table
	});

});




