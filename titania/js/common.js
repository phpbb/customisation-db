$(document).ready(function(){
/* Not working...
	// AJAX Rate
	$("ul.rating li a, ul.rated li a").click(function(event){
		event.preventDefault();

		// Get the child img id which holds some info we need.
		var child = $(this).children().attr('id');

		// Child contains the object_id and the star number.
		var object = child.split('_');

		// Set the red stars that we neeed to.
		for (i = 1; i <= max_rating; i++)
		{
			if (i <= object[1])
			{
				$('#rating_' + object[1] + '_' + i).attr({src: green_star.src});
			}
			else
			{
				$('#rating_' + object[1] + '_' + i).attr({src: grey_star.src});
			}
		}

		if ($("#rating_" + object[1]).hasClass("rating"))
		{
			$("#rating_" + object[1]).removeClass("rating");
			$("#rating_" + object[1]).addClass("rated");
			$(this).unbind("hover");
			$("#rating_" + object[1] + "_remove").parent().parent().removeClass("hidden");
		}
		else
		{
			$("#rating_" + object[1]).removeClass("rated");
			$("#rating_" + object[1]).addClass("rating");
			$("#rating_" + object[1] + "_remove").parent().parent().addClass("hidden");
		}

		return;

		$.ajax({
			type: "POST",
			url: $(this).attr('href'),
			success: function() {
			}
		});
	});
*/
	// Rating hover functions
	$("ul.rating li a").hover(
		function(){
			// Over function. This will change the stars up to the point that was hovered on to red

			// Get the child img id which holds some info we need.
			var child = $(this).children().attr('id');

			// Child contains the object_id and the star number.
			var object = child.split('_');

			// Set all the stars to grey first
			$('#rating_' + object[1] + " li a img").each(function() {
				$(this).attr({src: grey_star.src});
			});

			// Set the red stars that we neeed to.
			for (i = 1; i <= object[2]; i++)
			{
				$('#rating_' + object[1] + '_' + i).attr({src: red_star.src});
			}
		},
		function(){
			// Out function. Reset to default stars
			$("ul.rating li a img.green").each(function() {
				$(this).attr({src: green_star.src});
			});

			$("ul.rating li a img.orange").each(function() {
				$(this).attr({src: orange_star.src});
			});

			$("ul.rating li a img.grey").each(function() {
				$(this).attr({src: grey_star.src});
			});
		}
	);

	// Ajax Quick Edit
	$('.postbody > .profile-icons > .edit-icon').click(function(e) {
		var postbody = $(this).parent().parent();

		// Return false if the form is already open
		if ($('form', postbody).length)
		{
			e.preventDefault();
			return;
		}

		var post = $(postbody).children('.content');

		// Store the original post in case the user cancels the edit
		$(post).after($(post).clone());
		$(post).next().addClass('hidden original_post');

		// Ajax time
		$.ajax({
			type: "POST",
			url: $(post).parent().children('.quick_edit').val(),
			success: function(html){
				$(post).replaceWith(html);

				var quickeditor = $(postbody).children('form').children('textarea');

				// Make elastic and tabby
				$(quickeditor).elastic();
				$(quickeditor).tabby();

				$(quickeditor).parent().children('.submit-buttons').children('[name=submit]').click(function(e) {
					$(this).parent().hide();

					// Ajax time
					$.ajax({
						type: "POST",
						url: $(quickeditor).parent().attr('action'),
						data: $(quickeditor).parent().serialize() + '&submit=1',
						success: function(html){
							$(quickeditor).parent().replaceWith('<div class="content text-content">' + html + '</div>');
							var subject = $('.content:not(.original_post)', postbody).children('span:first-child');
							$('h3 a', postbody).html($(subject).html());
							$(subject).remove();
							$('.original_post', postbody).remove();
						}
					});

					// Do not redirect
					e.preventDefault();
				});
			}
		});

		// Do not follow the link
		e.preventDefault();
	});

	// Canceled quick edit, so display original post again
	$('.postbody #cancel').live('click', function(event) {
		event.preventDefault();

		var postbody = $(this).parents('.postbody');

		$('form', postbody).remove();
		$('.original_post', postbody).removeClass('hidden');
	});

	// Show only the first five revisions
	$('.revisions > li').each(function(cnt) {
		if (cnt > 5)
		{
			// Hide the revision from the list
			$(this).hide();

			$(this).parent().parent().children('.show-all').show();
		}
	});

	// Hide all of the revision details
	$('.revision-details:not(.first)').hide();

	// Show revision details on click
	$('.revisions > li').click(function() {
		$(this).children('.revision-details').toggle('fast');
	});

	// Queue Subactions
	$('.queue-actions > li > .subactions').hide();
	$('.queue-actions > li').hover(function() {
		$(this).children('.subactions').toggle();
	}, function() {
		$(this).children('.subactions').toggle();
	});
	
	$('.download-main').click(function() {
		var cease = readCookie('cdb_ignore_subscription');
		
		if (!cease && $('.dialog#subscription').length) {
			$.colorbox({html: $('.dialog#subscription').html(), width: '400px'});
		}
	});
	
	$('#cboxLoadedContent #cancel').live('click', function(event) {
		event.preventDefault();
		$.colorbox.close();
	});
	
	$('#cboxLoadedContent #cease').live('click', function(event) {
		event.preventDefault();
		createCookie('cdb_ignore_subscription', 'true', 365);
		$.colorbox.close();
	});
  
	// Remove -mode_view from screenshot links as we'll be displaying the image inline, so file.php should not
	// wrap the image in html in IE
	$.each($('a.screenshot'), function() {this.href = this.href.replace('-mode_view', '');});

	// Prevent the user from submitting a form more than once.
	$('input[type="submit"]').click(function(event) {
		// Since the submit value is no longer passed once the button is disabled, we must hide the original button and create a clone.
		$(this).after($(this).clone()).addClass('hidden');
		$(this).parent().children('input[type="submit"]:not(.hidden)').attr('disabled', 'disabled').addClass('disabled');
	});
});

function hide_quotebox(box)
{
	$(box).parent().children('div').hide();
	$(box).parent().children('.hide_quote').hide();

	$(box).parent().children('.show_quote').show();
}

function show_quotebox(box)
{
	$(box).parent().children('div').show();
	$(box).parent().children('.show_quote').hide();

	$(box).parent().children('.hide_quote').show();
}

function show_all_revisions(box)
{
	$(box).parent().children('.revisions').children('li').each(function(cnt) {
		$(this).show();

		$(this).parent().parent().children('.show-all').hide();
	});
}
