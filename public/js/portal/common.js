function beforeSend(xhr) {
    $('.alert').fadeOut('slow');
    $('.animationload').fadeIn('slow');
}

function successCallback(data) {
    $('.animationload').fadeOut('slow');
}

function completeCallback(xhr, status) {
    $('.animationload').fadeOut('slow');
}

function send(url, data, beforeSend, successCallback, completeCallback) {
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        dataType: 'json',
        beforeSend: beforeSend,
        success: successCallback,
        error: function (xhr, errortype, errormsg) {
            console.log(errorMsg);
        },
        complete: completeCallback
    });
}

$(function () {
    $(document.body).on('click', 'input.check-all', function () {
        if ($(this).is(':checked')) {
            $('input.check-it').prop('checked', true);
        } else {
            $('input.check-it').prop('checked', false);
        }
        
        if ($('input.check-it:checked').length > 0) {
            $('button.delete, button.status').prop('disabled', false);
        } else {
            $('button.delete, button.status').prop('disabled', true);
        }
    });
    
    $(document.body).on('click', 'input.check-it', function () {
        if ($('input.check-it:checked').length > 0) {
            $('button.delete, button.status').prop('disabled', false);
        } else {
            $('button.delete, button.status').prop('disabled', true);
        }
    });
    
    $(document.body).on('click', 'button.status', function(){
        var that = $(this);
        var ids = Array();
        
        $('input.check-it:checked').each(function(){
            ids.push($(this).val());
        });
        
        document.location.href = that.data('href')+'?ids='+ids.join(',')+'&status='+$(this).data('val');
    });
    
    $(document.body).on('click', 'button.delete', function(){
        var that = $(this);
        var ids = Array();
        
        $('input.check-it:checked').each(function(){
            ids.push($(this).val());
        });
        
        swal({   
            title: "Are you sure?",   
            text: "You will not be able to recover this record(s)!",   
            type: "warning",   
            showCancelButton: true,   
            confirmButtonColor: "#DD6B55",   
            confirmButtonText: "Yes, delete!",   
            closeOnConfirm: false 
        }, function(){   
            document.location.href = that.data('href')+'?ids='+ids.join(',');
        });
    });
});