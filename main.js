// JavaScript Document

(function ($) {

	$(document).ready(function () {
		var ajaxUrl = cognix_object.ajax_url;
		// Hide all tab content except the first one

		// Initialize form validation on the registration form.
		// It has the name attribute "registration"
		$("form[name='cognix_register']").validate({
			// Specify validation rules
			rules: {
				// The key name on the left side is the name attribute
				// of an input field. Validation rules are defined
				// on the right side
				firstname: "required",
				lastname: "required",
				email: {
					required: true,
					// Specify that email should be validated
					// by the built-in "email" rule
					email: true
				},
				password: {
					required: true,
				},
				con_password: {
					minlength: 5,
					equalTo: "#password",
				}
			},
			// Specify validation error messages
			messages: {
				firstname: "Please enter your firstname",
				lastname: "Please enter your lastname",
				email: "Please enter a valid email address",
				password: {
					required: "Please provide a password",
				},
				confirmpassword: {
					required: "Please provide a password",
					equalTo: "Please enter the same value again."
				}
			},
			// Make sure the form is submitted to the destination defined
			// in the "action" attribute of the form when valid
			submitHandler: function (form) {

				var firstname = jQuery('#firstname').val();
				var lastname = jQuery('#lastname').val();
				var member_name = jQuery('#member_name').val();
				var password = jQuery('#password').val();
				var email = jQuery('#email').val();

				$('#email_allowed').html('');
				$('#member_name_allowed').html('');

				//form.submit();
				jQuery.ajax({
					url: ajaxUrl,
					method: 'POST',
					data: {
						action: 'cognix_register_user',
						firstname: firstname,
						lastname: lastname,
						member_name: member_name,
						password: password,
						email: email
					},
					dataType: 'JSON',
					success: function (response) {
						if (response.success === true) {
							$('#responsemsg').html('The user has been successfully registered.');
							console.log('--- if  , inside success----');
							location.reload();
						} else {
							console.log('--- else , inside success----');
							$('.responsemsg').html(response.data);
							$('#cognix_reg_status_msg').html('We are extremly sorry. There is some issue, please try again later. We are looking into it. In the meanwhile please reach out to support@cognix.ai');
							$('#cognix_reg_status_msg').removeClass("success_message")
							$('#cognix_reg_status_msg').addClass("error_message")
						}
						$('#cognix_register')[0].reset();
					},
					error: function (xhr, status, error) {
						// Handle AJAX error
						//console.log(error);
						
					}
				});
				return false;
			}
		});

		$("form[name='cognix_login']").validate({
			// Specify validation rules
			rules: {
				lemail: {
					required: true
				},
				lpassword: {
					required: true,
				}
			},
			// Specify validation error messages
			messages: {},
			// Make sure the form is submitted to the destination defined
			// in the "action" attribute of the form when valid
			submitHandler: function (form) {

				var lemail = jQuery('#lemail').val();
				var lpassword = jQuery('#lpassword').val();
				jQuery.ajax({
					url: ajaxUrl,
					method: 'POST',
					data: {
						action: 'cognix_login_user',
						lemail: lemail,
						lpassword: lpassword,
					},
					dataType: 'JSON',
					success: function (response) {
						if (response.success === true) {
							$('#cognix_login_status_msg').html('You have successfully logged in.');
							$('#cognix_login_status_msg').removeClass("error_message")
							$('#cognix_login_status_msg').addClass("success_message")

							$('input[name=crchatboat]').removeAttr('disabled');
						} else {
							$('#cognix_login_status_msg').html('The username or password is incorrect.');
							$('#cognix_login_status_msg').removeClass("success_message")
							$('#cognix_login_status_msg').addClass("error_message")
						}
						$('#cognix_login')[0].reset();
					},
					error: function (xhr, status, error) {
						// Handle AJAX error
						//console.log(error);
					}
				});

			}
		});

		$("form[name='createchat']").validate({
			// Specify validation rules
			rules: {},

			messages: {},
			// Make sure the form is submitted to the destination defined
			// in the "action" attribute of the form when valid
			submitHandler: function (form) {
				$('.loading').addClass('load');
				var bottype = $('#bottype').val();
				var myCheckboxes = new Array();
				$("input.upages:checked").each(function () {
					myCheckboxes.push($(this).val());
				});
				jQuery.ajax({
					url: ajaxUrl,
					method: 'POST',
					data: {
						action: 'cognix_create_chat_bots',
						upages: myCheckboxes,
						bottype: bottype
					},
					dataType: 'JSON',
					success: function (response) {
						if (response.success === true) {
							//$('.lresponsemsg_success').html(response.data.message);
							$('#cognix_bot_create_msg').html('Your chatbot has been successfully created.');
							$('#cognix_bot_create_msg').removeClass("error_message");
							$('#cognix_bot_create_msg').addClass("success_message");
							$('.chatbot-script').html(response.data.script);
							$('.sctipt-data').removeClass('hidden');
						} else {
							$('#cognix_bot_create_msg').html('We are extremly sorry. There is some issue, please try again later. We are looking into it. In the meanwhile please reach out to support@cognix.ai');
							$('#cognix_bot_create_msg').removeClass("success_message");
							$('#cognix_bot_create_msg').addClass("error_message");
						}
						$('.loading').removeClass('load');
					},
					error: function (xhr, status, error) {
						// Handle AJAX error
						//console.log(error);
					}
				});

			}
		});

		$(document).on('blur', '#member_name', function(){
			$('#member_name_allowed').html('');
			if($(this).val().length == 0){
				$('#member_name_allowed').html('<span class="dashicons dashicons-dismiss" style="color:red"></span>');
				return;
			} else {
				$('#member_name_allowed').addClass('loading');
			}
			jQuery.ajax({
				url: ajaxUrl,
				method: 'GET',
				data: {
					action: 'check_username_exists',
					value: $(this).val()
				},
				dataType: 'JSON',
				success: function (response) {
					$('#member_name_allowed').removeClass('loading');
					if (response.success === true) {
						$('#member_name_allowed').html('<span class="dashicons dashicons-yes-alt" style="color:green"></span>');
					} else {
						$('#member_name_allowed').html('<span class="dashicons dashicons-dismiss" style="color:red"></span>');
					}
				},
				error: function (xhr, status, error) {
					// Handle AJAX error
					//console.log(error);
				}
			});
		});

		$(document).on('blur', '#email', function(){
			$('#email_allowed').html('');
			if($(this).val().length == 0){
				return;
			} else {
				$('#email_allowed').addClass('loading');
			}
			jQuery.ajax({
				url: ajaxUrl,
				method: 'GET',
				data: {
					action: 'check_email_exists',
					value: $(this).val()
				},
				dataType: 'JSON',
				success: function (response) {
					$('#email_allowed').removeClass('loading');
					if (response.success === true) {
						if(response.data === 'empty'){
							$('#email_allowed').html('');
							return;
						}
						$('#email_allowed').html('<span class="dashicons dashicons-yes-alt" style="color:green"></span>');
					} else {
						$('#email_allowed').html('<span class="dashicons dashicons-dismiss" style="color:red"></span>');
					}
				},
				error: function (xhr, status, error) {
					// Handle AJAX error
					//console.log(error);
				}
			});
		});

		// $(".showreguser").click(function () {
		// 	$('.regclu').removeClass('regclu');
		// 	$('.showreguser').addClass('hideuser');
		// });

		// $("#cognixRegCancelButton").click(function(){

		// 	$('.regclu').addClass('regclu');
		// 	$('#cognix_register')[0].reset();
		// 	$('.showreguser').removeClass('hideuser');

		// });

		$("#cognix_reg_div").click(function () {
			$('#cognix_register_frm').show();
			$('#cognix_reg_div').hide();
		});

		$("#cognixRegCancelButton").click(function(){

			//$('#cognix_register')[0].reset();
			$('#cognix_register_frm').hide();
			$('#cognix_reg_div').show();
			

		});

	});

})(jQuery);
