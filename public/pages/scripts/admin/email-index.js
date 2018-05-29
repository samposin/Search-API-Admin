
jQuery(document).ready(function(){
   var subject_daily='Baseify Revenue Report - <date>';
   var $message_daily="Hi <partner name> Team,\n\n"+
       "Daily click and revenue report - <Date>\n\n"+
       "Thanks,\n"+
       "Accounting @ Baseify\n"+
       "By iLeviathan LLC"



    var subject_monthly='Baseify Revenue Report - <month> <year>';
    var $message_monthly="Hi <partner> Team,\n\n"+
        "The total revenue for this month is $ <sum>.\n\n"+
    "Attached is the billing report for the month of <month> <year>.\n\n"+
    "Please reply with an invoice for the total indicated above and let us know if you have any questions.\n\n"+
        "IMPORTANT!\n"+
        "To ensure prompt payment, please email accounting@ileviathan.com with your invoice attached before the 20th of THIS month.\n\n"+

        "Thanks,\n"+
        "Accounting @ Baseify\n"+
    "By iLeviathan LLC"


    $("input[name=subject]").val(subject_daily);
    $("textarea[name=message]").val($message_daily);
    $("textarea[name=message]").css('height','200px');

    jQuery('.btn_daily').click(function(){

        $("input[name=subject]").val(subject_daily);
        $("textarea[name=message]").val($message_daily);
        $("textarea[name=message]").css('height','200px');

    });
    jQuery('.btn_monthly').click(function(){

        $("input[name=subject]").val(subject_monthly);
        $("textarea[name=message]").val($message_monthly);
        $("textarea[name=message]").css('height','380px');

    });
});