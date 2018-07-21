function enable_assignmanager(formid){
    var selectReg = function () {
        var checked = $("form#movemodules"+formid+"  input:checked").length;
        if (checked == '0') {
            $('form#movemodules'+formid+'  input[type="submit"]').attr('disabled', 'disabled');
        }
        else
            $('form#movemodules'+formid+'  input[type="submit"]').removeAttr('disabled');
    };
    selectReg();
    $("form#movemodules"+formid+"  input[type=checkbox]").on("click", selectReg);

}

