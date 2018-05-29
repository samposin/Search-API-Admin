jQuery(document).ready(function (){
	/*
	* initialize CsvReportDataTableAjax
	* */
	CsvReportDataTableAjax.init();
});

    var CsvReportDataTableAjax = function (){
		var handleRecords = function (){

			/*
			* get object Datatable
		    */
			var grid = new Datatable();

			var month=$('#month').val();
			var api=$('#api').val();
			var publisher=$('#publisher').val();
			grid.setAjaxParam("search_month", month);
			grid.setAjaxParam("search_api", api);
			grid.setAjaxParam("search_publisher", publisher);
			$('select[name=api]').change(function() {
				var month=$('#month').val();
				var api=$('#api').val();
				var publisher=$('#publisher').val();
				grid.setAjaxParam("search_month", month);
				grid.setAjaxParam("search_api", api);
				grid.setAjaxParam("search_publisher", publisher);
				grid.getDataTable().ajax.reload(function(json){
					$('div.loading').remove();
					if(json.data==""){
						$("#btn_send_daily_report").hide()//hide button send daily report

					}
					else{
						$("#btn_send_daily_report").show()//show button send daily report

					}

				});

			});

			$('select[name=month]').change(function() {
				var month=$('#month').val();
				var api=$('#api').val();
				var publisher=$('#publisher').val();
				grid.setAjaxParam("search_month", month);
				grid.setAjaxParam("search_api", api);
				grid.setAjaxParam("search_publisher", publisher);
				grid.getDataTable().ajax.reload(function(json){
					$('div.loading').remove();
					if(json.data==""){
						$("#btn_send_daily_report").hide()//hide button send daily report

					}
					else{
						$("#btn_send_daily_report").show()//show button send daily report

					}

				});
			});

			$('select[name=publisher]').change(function() {
				var month=$('#month').val();
				var api=$('#api').val();
				var publisher=$('#publisher').val();
				grid.setAjaxParam("search_month", month);
				grid.setAjaxParam("search_api", api);
				grid.setAjaxParam("search_publisher", publisher);
				grid.getDataTable().ajax.reload(function(json){
					$('div.loading').remove();
					if(json.data==""){
						$("#btn_send_daily_report").hide()//hide button send daily report

					}
					else{
						$("#btn_send_daily_report").show()//show button send daily report

					}

				});



			});

			var grid_ele = $("#datatable_ajax");

			grid.init({
				src: grid_ele,
				onSuccess: function (grid){

					// execute some code after table records loaded
				},

				onError: function (grid){
					// execute some code on network or other general error
				},
				onDataLoad: function (grid){

					//console.log(grid); // execute some code on ajax data load
				},

				loadingMessage: 'Loading...',
				dataTable: { // here you can define a typical datatable settings from http://datatables.net/usage/options

					// Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
					// setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/scripts/datatable.js).
					// So when dropdowns used the scrollable div should be removed.
					//"dom": "<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'<'table-group-actions pull-right'>>r>t<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'>>",

					"bStateSave": true, // save datatable state(pagination, sort, etc) in cookie.
					"dom": "<'row'<'col-xs-3 col-sm-3 col-md-3 col-lg-3'><'col-xs-9 col-sm-9 col-md-9 col-lg-9 text-right'pli><'col-md-4 col-sm-12'<'table-group-actions pull-right'>>r><'table-scrollable't><'row' <'col-xs-3 col-sm-3 col-md-3 col-lg-3 anc_add_contacts_cont clear'> <'col-xs-9 col-sm-9 col-md-9 col-lg-9 text-right'pli><'col-md-4 col-sm-12'>>", // datatable layout
					"lengthMenu": [
						[10, 20, 50, 100, 150, -1],
						[10, 20, 50, 100, 150, "All"] // change per page values here
					],
					"pageLength": 10, // default record count per page
					"ajax": {
						"url": url_dashboard_api_ajax, // ajax source
						"method": "post",
						"dataType": 'json',
						headers: {'X-CSRF-TOKEN': csrf_token},

						timeout:0, //Set your timeout value in milliseconds or 0 for unlimited
						//success: function(response) { alert(response); },
						error: function(jqXHR, textStatus, errorThrown) {
							//consol.log(jqXHR);
							//consol.log(textStatus);
							//consol.log(errorThrown);

						}


					},
					"initComplete": function( settings, json ) {
						$('div.loading').remove();
						if(json.data==""){
						$("#btn_send_daily_report").hide() //hide button send daily report

						}
						else{
							$("#btn_send_daily_report").show()//show button send daily report


						}
					},

					"order": [
						[1, "asc"]
					],// set first column as a default sort by asc
					"columnDefs": [
						{
							"targets": 'no-sort',
							"orderable": false,
						},
						{
							"className": "text-center",
							"targets": [5]
						}],
				}
			});
		}

		return {
			//main function to initiate the module
			init: function (){
				handleRecords();

			}
		};

	}();

/*
 * get_search_clicks_ajax
 */
var get_search_clicks_ajax =function(){
	var month=$('#month').val();
	var api=$('#api').val();
	var publisher=$('#publisher').val();
	var month1='?month='+month;
	var api1='&api='+api;
	var publisher1='&publisher='+publisher;
	/*
	 *  get url csv-report download
	 */
	var url=url_dashboard_csv_download_ajax+month1+api1+publisher1;

	$('#csv_download').attr('href',url);
	$.ajax({
		type:"post",
		url:url_dashboard_api_ajax,
		dataType:"json",
		data:{
			"search_api":api,
			"_token":csrf_token,
			"search_month":month,
			"search_publisher":publisher,
		},

		success:function(data) {

			var html = "";
			var length=data['date'].length
			var i=0

			if(data['date'].length==0) {
				$('#fromdate').html('00.00.00')
				$('#todate').html('00.00.00');
				$('#btn_send_daily_report').hide();
				$('input[name="email"]').val('');


			}
			else {

				$('#btn_send_daily_report').show();

				for (key in data['date']) {
					i++;
					var temp=data['date'][key];
					if(i==1){
						$('#fromdate').html(temp);

					}
					if(i==length){
						$('#todate').html(temp);

					}
				}
			}

			$('#fetch').html(html);

		}
	});
}
jQuery(document).ready(function(){
	/*
	 * Show pop up model
	 */

	$('#btn_send_daily_report').click(function(){
		$('input[name="email"]').val('');
		/*
		 * calling get_search_clicks_ajax
		 */
		get_search_clicks_ajax();

	});

	/*
	 * send email by ajax
	 */
	$('#btn_email_daily_report').click(function() {


		var email = $('input[name="email"]').val();
		/*
		 * check email
		 */
		if (email == '') {
			alert("please type Email ID")
			$('input[name="email"]').focus();



		}
		else
		/*
		 * check email validation
		 */
		if(email!='') {
			var valideEmail_array = email.split(",");
			if(valideEmail_array.length>=6) {

				alert("you can send 5 email ID,please remove last comma.");
				return false;

			}
			else{
				for (i = 0; i < valideEmail_array.length; i++) {

					/*
					 * calling ValidateEmail() for check email validation
					 */
					if (!ValidateEmail(valideEmail_array[i])) {
						alert("Invalid email address.");
						return false;
					}

				}
			}


			var month = $('#month').val();
			var api = $('#api').val();
			var publisher = $('#publisher').val();
			$.ajax({
				type: "post",
				url: url_dashboard_csv_email_send_ajax,
				dataType: "json",
				data: {
					"email": email,
					"api": api,
					"_token": csrf_token,
					"month": month,
					"publisher": publisher,
				},
				beforeSend: function(){
					$("#loading").show();
				},
				complete: function(){
					$("#loading").hide();
				},
				success: function (data) {


					var html='';
					if(data['success']==1){


						html="<div class='alert alert-success''>"+data['msg']+"</div>";

					}
					if(data['success']==0){

						html="<div class='alert alert-danger'>"+data['msg']+"</div>";

					}
					$('input[name="email"]').val('');
					$('.email-success').html(html).delay(3000).fadeOut("slow");

				}
			});

		}
	});

});
function ValidateEmail(email) {

	var expr = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
	return expr.test(email);
};


