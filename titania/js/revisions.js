$(document).ready(function() {
	$('#revision_submit').validate({
		debug: true,
		errorPlacement : function (error, element) {
			error.appendTo(element.parent('dd').parent().children('dt'));
		},
		highlight: function(element, errorClass) {
			$(element).addClass('error_outline');
		},
		unhighlight: function(element, errorClass) {
			$(element).removeClass('error_outline');
		},
		submitHandler: function(form) {
			$.ajax(
			{
				type: 'POST',
				url: $(form).attr('action'),
				data: $(form).serialize() + '&submit=submit',
				dataType: 'json',
				success: function(data, textStatus)
				{
					$('#uploads').slideUp('slow');
					$('#upload_container').fadeIn('slow');
					$('#revision_holder').html(data.html);
				}
			});
			
		},
	});
});

function revision_upload_complete(event, queueId, fileObj, response, data)
{
	// Parse response as JSON.
	var response = window["eval"]("(" + response + ")");

	if (response.error && response.error != '')
	{
		$('#errorbox_msg').text(response.error);
		$('#errorbox').fadeIn('slow');

		setTimeout("$('#errorbox').fadeOut('slow');", 10000);
	}
	else
	{
		$('#upload_container').slideUp(1000);
		$('#uploads').html(response.html).slideDown('slow');
		$('#errorbox').css('display', 'none');
	}
}

var countdown = {
	init: function() {
		countdown.remaining = countdown.max - $(countdown.obj).val().length;
		
		if (countdown.remaining > countdown.max) {
			$(countdown.obj).val($(countdown.obj).val().substring(0,countdown.max));
		}
		
		$(countdown.obj).siblings(".remaining").html(countdown.remaining + " characters remaining.");
	},
	max: null,
	remaining: null,
	obj: null
};

$(".countdown").each(function() {
	$(this).focus(function() {
		var c = $(this).attr("class");
		countdown.max = parseInt(c.match(/limit_[0-9]{1,}_/)[0].match(/[0-9]{1,}/)[0]);
		countdown.obj = this;
		iCount = setInterval(countdown.init,1000);
	}).blur(function() {
		countdown.init();
		clearInterval(iCount);
	});
}); 