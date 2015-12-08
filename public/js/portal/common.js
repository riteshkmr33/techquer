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
        url : url,
        type : 'POST',
        data : data,
        dataType : 'json',
        beforeSend : beforeSend,
        success : successCallback,
        error : function(xhr, errortype, errormsg) {
            console.log(errorMsg);
        },
        complete: completeCallback
    });
}