jQuery(document).ready(function(){
    AdvertiserListDataTableAjax.init();

});
// Advertiser list through datatable logic
var AdvertiserListDataTableAjax = function () {

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

    var handleRecords = function () {



        var grid = new Datatable();
        var grid_ele=$("#datatable_ajax");
        $('textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])', grid_ele).each(function() {
            jQuery('[name="hdn_'+$(this).attr("name")+'"]').val($(this).val());


        });

        grid.init({
            src: grid_ele,

            onSuccess: function (grid) {
                //console.log(grid);
                // execute some code after table records loaded
            },
            onError: function (grid) {
                // execute some code on network or other general error  
            },
            onDataLoad: function(grid) {

               //console.log(grid('sub_total_clicks1')); // execute some code on ajax data load
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
                    "url": "clicked-keywords-ajax", // ajax source
                    "method":"post",
                    headers: { 'X-CSRF-TOKEN' : csrf_token },
                },
                "order": [
                    [1, "asc"]
                ],// set first column as a default sort by asc
                "columnDefs": [ {
                    "targets"  : 'no-sort',
                    "orderable": false,
                }]
            }
        });

        // handle group actionsubmit button click
        grid.getTableWrapper().on('click', '.table-group-action-submit', function (e) {
            e.preventDefault();

            var action = $(".table-group-action-input", grid.getTableWrapper());
            if (action.val() != "" && grid.getSelectedRowsCount() > 0) {
                grid.setAjaxParam("customActionType", "group_action");
                grid.setAjaxParam("customActionName", action.val());
                grid.setAjaxParam("id", grid.getSelectedRows());
                grid.getDataTable().ajax.reload();
                grid.clearAjaxParams();
            } else if (action.val() == "") {
                Metronic.alert({
                    type: 'danger',
                    icon: 'warning',
                    message: 'Please select an action',
                    container: grid.getTableWrapper(),
                    place: 'prepend'
                });
            } else if (grid.getSelectedRowsCount() === 0) {
                Metronic.alert({
                    type: 'danger',
                    icon: 'warning',
                    message: 'No record selected',
                    container: grid.getTableWrapper(),
                    place: 'prepend'
                });
            }
        });





        // handle filter submit button click
        grid_ele.on('click', '.filter-submit', function(e) {
            // get all typeable inputs
           $('textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])', grid_ele).each(function() {
               jQuery('[name="hdn_'+$(this).attr("name")+'"]').val($(this).val());

            });

            // get all checkboxes
            $('input.form-filter[type="checkbox"]:checked', grid_ele).each(function() {
                jQuery('[name="hdn_'+$(this).attr("name")+'"]').val($(this).val());
            });

            // get all radio buttons
            $('input.form-filter[type="radio"]:checked', grid_ele).each(function() {
                jQuery('[name="hdn_'+$(this).attr("name")+'"]').val($(this).val());
            });
        });

        console.log(grid_ele);

        // handle export drop down item click
        /*
         jQuery('#companies_export_dropdown').on('click', 'li a', function (e) {
         if(jQuery(this).data('export-type')=='csv') {
         console.log(jQuery(this).data('export-type'));

         var frm_datatable=grid_ele.closest('form');

         frm_datatable.prop('action','/companies/export/csv');
         jQuery('#action',frm_datatable).val('export_csv');
         frm_datatable.submit();

         }
         })
         */
    }

    return {

        //main function to initiate the module
        init: function () {

            initPickers();
            handleRecords();
        }

    };

}();


