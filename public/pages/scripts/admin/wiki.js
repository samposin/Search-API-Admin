jQuery(document).ready(function(){
    //config option CkEditor
    var config = {
        toolbar:
            [
                ['Bold', 'Italic', 'Underline', '-', 'NumberedList', 'BulletedList', '-', 'Undo', 'Redo', '-', 'SelectAll'],
                [ 'Cut','Copy','Paste','PasteText','PasteFromWord' ],
                [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ],
                ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ],
                [ 'Link','Unlink','Anchor' ],
                [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak' ],
                [ 'Styles','Format','Font','FontSize' ],
                [ 'TextColor','BGColor' ],
                ['UIColor','Maximize', 'ShowBlocks'],

            ]
    };

    $('textarea').ckeditor(config);
    //$("#add_category").val('');
    category_show();
    //$('.bootstrap-tagsinput input[type=text]').attr('name','tags')
    $("#btn_add_category").click(function(){
        var category_name=$("#add_category").val();
        if(category_name=='')
        {
         $(".error").show();

        }
    else {
        $.ajax({
            'type': 'post',
            'url': url_wiki_category_ajax_save,
            'dataType': 'json',
            'data': {'name': category_name},
            headers: {'X-CSRF-TOKEN': csrf_token},
            success: function (data) {
                var categry_list_array = '';

                if (data == 1) {

                    get_show();
                    $(".error").hide();
                    //$("#add_category").val('');
                }
                else {


                }
            }


        });
    }

})
});

var category_show=function(){
    $.ajax({
        'type':'post',
        'url':url_wiki_category_ajax,
        'dataType':'json',
        headers: { 'X-CSRF-TOKEN' : csrf_token },
         success:function(data){
           var category_list_array='';

            if(data.length==0){

                category_list='No Found Record';

            }
            else{
                for(var i=0;i<data.length;i++ ){
                    category_list_array+='<input type="checkbox" name="category[]" id="category" required value="'+data[i].name+'">&nbsp'+data[i].name+'<br>';

                }
            }

           $(".category_list").html(category_list_array);

         }
    });
}
$(function() {
    // Setup form validation on the #register-form element
    $("#wiki_form").validate({
        errorClass:'text-danger',
        // Specify the validation rules
        rules: {
               title:'required',
               discription:'required',
               keyword:'required',
               category:'required'
        },
        // Specify the validation error messages
        messages: {
        },
        submitHandler: function(form) {

             var form_array=($("#wiki_form").serialize());
             var url=url_wiki_save;

             $.post(
                    url,
                    form_array,
                   function (data) {
                       if (data == 1) {
                       $(".alert-success").show();

                       }
                   }
             );
        }
    });

});



