/**
 * Scripts that run on front end
 */
(function($) {
	$(document).ready(function(){
        $('.sendwelcome').click(function(e) {
            e.preventDefault();
            var $this = jQuery(this);
            var currenttext = $this.text();
            $this.addClass('processing');
            var user_id =  $this.attr('data-user-id');

            var data = {
				'action': 'eos_onboard_user',
                'user_id' : user_id
			};		
			$.ajax({
				url: settings.ajaxurl,
				type: 'post',
				data: data
			}).done(function(response) {
                $this.removeClass('processing');
                if(response === 'error') {
                    alert('There was an error sending the onboarding emails, please try again later.');
                    $this.text('Error!');
                } else {
                    $this.text(response);
                }
                /*setTimeout(function() {
                    $this.text(currenttext);
                },2500); */ 
			});
        });

        $('.sendtest').click(function() {
            var parent = $(this).parent().parent();
            if($(this).text() == 'Send Test') {
                $(this).text('Cancel');
            } else {
                $(this).text('Send Test');
            }
            parent.find('.sendresult').text('');
            parent.find('.testemail').toggleClass('testing');
            parent.find('input.testemail').val('').removeClass('error');
        });

        $('.sendtestsubmit').click(function() {
            var parent = $(this).parent().parent();
            parent.find('.sendresult').text(''); //empty results/error
            var email = parent.find('input.testemail');
            email.removeClass('error');
            if(email.val() == '') {
                email.addClass('error');
                parent.find('.sendresult').text('Please enter an email');
            } else {
                var subject = parent.find('.eos-subject').val();
                var message = parent.find('textarea').val();
                var data = {
                    'action': 'eos_test_email',
                    'email' : email.val(),
                    'subject' : subject,
                    'message' : message
                };		
                $.ajax({
                    url: settings.ajaxurl,
                    type: 'post',
                    data: data
                }).done(function(response) {
                    if(response === 'error') {
                        parent.find('.sendresult').text('There was an error sending your test. Please try again later.');
                    } else {
                        parent.find('.sendresult').text('Success! Please check your '+ email.val() +' email');
                    }
                    setTimeout(function() {
                        parent.find('.sendresult').text('');
                    },3500); 
                });
            }
        });

        $('#createuser select').attr('disabled','disabled');
	});
})( jQuery );