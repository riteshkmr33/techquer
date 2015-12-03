	/*$(document).ready(function() {
		
	});
*/

(function ( $ ) {

	"use strict";

	
$('form#contactForm').submit(function() {
			$('form#contactForm .error').remove();
			var hasError = false;
			$('.requiredField').each(function() {
				if($.trim($(this).val()) == '') {
					var val = $(this).attr('placeholder');
					$(this).attr("placeholder", "Your forgot to enter your ");
					$(this).addClass('inputError');
					hasError = true;
				} else if($(this).hasClass('email')) {
					var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
					if(!emailReg.test($.trim($(this).val()))) {
						var labelText = $(this).prev('label').text();
						$(this).parent().append('<span class="error">Sorry! You\'ve entered an invalid '+labelText+'.</span>');
						$(this).addClass('inputError');
						hasError = true;
					}
				}
			});
			if(!hasError) {
				var formInput = $(this).serialize();
				$.post($(this).attr('action'),formInput, function(data){
					//$('form#contactForm').slideUp("fast", function() {	
				$('#name').attr("value", "");
				$('#email').attr("value", "");	
					
				$('#message').attr("value", "");
				$('#name').attr("placeholder", "Name");
				$('#email').attr("placeholder", "Email");	
				
				$('#message').attr("placeholder", "Message");
				alert('Message Sent');		   
				//$(this).before('<p class="tick"><strong>Thanks!</strong> Your email has been delivered. Huzzah!</p>');
					//});
				});
			}
			
			return false;	
		});


}( jQuery ));