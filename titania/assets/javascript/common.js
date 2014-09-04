$(document).ready(function(){

	if (typeof $.colorbox === 'function') {
		$('a.screenshot').colorbox({photo: true, rel: 'group1'});
	}

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
	$('.postbody > .post-buttons .edit-icon').click(function(e) {
		var postbody = $(this).parents('.postbody');
		var full_edit = $(this).attr('href');

		// Return false if the form is already open
		if ($('form', postbody).length || $('.qe-error', postbody).length)
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
			success: function(response){

				// If a full page was served, then an error occurred. Redirect to full edit.
				if (response.indexOf('{') !== 0)
				{
					window.location.href = full_edit;
				}

				var response = eval('(' + response + ')');

				if (response.result == 'error')
				{
					$(post).before('<div class="error qe-error">' + response.content + '</div>');
					return;
				}

				$(post).replaceWith(response.content);

				var quickeditor = $(postbody).children('form').children('textarea');

				$(quickeditor).parent().children('.submit-buttons').children('[name=submit]').click(function(e) {

					// Ajax time
					$.ajax({
						type: "POST",
						url: $(quickeditor).parent().attr('action'),
						data: $(quickeditor).parent().serialize() + '&submit=1',
						success: function(response){
							var qe_form = $(quickeditor).parent();
	
							// If a full page was served, then an error occurred.
							if (response.indexOf('{') !== 0)
							{
								$(qe_form).before('<div class="error qe-error">' + form_error + '</div>');
							}
							else
							{
								var response = eval('(' + response + ')');

								// If the form token is invalid, redirect to full edit.
								if (response.result == 'invalid_token')
								{
									$('[name=full_editor]', qe_form).trigger('click');
									
								}
								else if (response.result == 'error')
								{
									$(qe_form).before('<div class="error qe-error">' + response.content + '</div>');
								}
								else
								{
									// Hide the form only if the AJAX call succeeded
									$(qe_form).hide();

									$(qe_form).replaceWith('<div class="content text-content">' + response.content + '</div>');
									var subject = $('.content:not(.original_post)', postbody).children('span:first-child');
									$('h3 a', postbody).html($(subject).html());
									$(subject).remove();
									$('.original_post', postbody).remove();
								}
							}
						}
					});

					// Do not redirect
					e.preventDefault();
				});
			},
			error: function() {
				window.location.href = full_edit;
			}
		});

		// Do not follow the link
		e.preventDefault();
	});

	// Canceled quick edit, so display original post again
	$(document).on('click', '.postbody #cancel', function(event) {
		event.preventDefault();

		var postbody = $(this).parents('.postbody');

		$('form', postbody).remove();
		// Remove warnings
		$('.qe-error', postbody).remove();
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
	$('.revisions li.row:not(":first") .revision-details').hide();
	// Add toggle button to each revision and translation
	$('.revisions li.row, .translations li.row').each(function() {
		var toggle_class = ($(this).is(':first-child')) ? 'contract' : 'expand';

		$(this).prepend('<a href="" class="toggle '+toggle_class+'"></a>');
	});

	// Only show the top 3 translations in each column
	$('.translations li.row:not(":first") dt div ul').each(function() {
		$('li:eq(1)', this).nextAll().hide();
	});

	// Show revision details on click
	$('.revisions > li a.toggle').click(function(e) {
		e.preventDefault();
		$(this).toggleClass('expand contract');
		$(this).parents('li.row').children('.revision-details').toggle('fast');
	});

	// Toggle extra translations
	$('.translations > li a.toggle').click(function(e) {
		e.preventDefault();

		$(this).siblings('dl').find('dt div ul').each(function() {
			$('li:eq(1)', this).nextAll().toggle();
		});
		$(this).toggleClass('expand contract');
	});

	$('.download-main').click(function() {
		var cease = readCookie('cdb_ignore_subscription');

		if (!cease && $('.dialog#subscription').length) {
			$.colorbox({html: $('.dialog#subscription').html(), width: '400px'});
		}
	});

	$(document).on('click', '#cboxLoadedContent #cancel', function(event) {
		event.preventDefault();
		$.colorbox.close();
	});
	
	$(document).on('click', '#cboxLoadedContent #cease', function(event) {
		event.preventDefault();
		createCookie('cdb_ignore_subscription', 'true', 365);
		$.colorbox.close();
	});

	// Remove ?mode=view from screenshot links as we'll be displaying the image inline, so the image should not
	// be wrapped in HTML in IE
	$('a.screenshot').each(function() {this.href = this.href.replace('?mode=view', '');});

	// Prevent the user from submitting a form more than once.
	$('input[type="submit"]').click(function(event) {
		// Since the submit value is no longer passed once the button is disabled, we must hide the original button and create a clone.
		$(this).after($(this).clone()).addClass('hidden');
		$(this).parent().children('input[type="submit"]:not(.hidden)').attr('disabled', 'disabled').addClass('disabled');
	});
});

$(document).on('click', '#screenshot-manage a.item-control-button:not(.delete)', function(e) {
	e.preventDefault();

	var container = $(this).closest('dl');

	if ($(this).hasClass('move-up'))
	{	
		$(container).insertBefore($(container).prev());

		// Enable move-down button
		$(this).prev('.move-down').removeClass('hidden');
		$(this).siblings('.move-down-disabled').addClass('hidden');

		// Disable move-up button if we're the first child now
		if ($(container).is(':first-child'))
		{
			$(this).addClass('hidden');
			$(this).next('.move-up-disabled').removeClass('hidden');
		}

		// Enable move-up button for sibling
		$(container).next().find('dd div .move-up').removeClass('hidden');
		$(container).next().find('dd div .move-up-disabled').addClass('hidden');

		// Disable move-down button for sibling if it's the last child
		if ($(container).next().is(':last-child'))
		{
			$(container).next().find('dd div .move-down').addClass('hidden');
			$(container).next().find('dd div .move-down-disabled').removeClass('hidden');
		}
	}
	else if ($(this).hasClass('move-down'))
	{
		$(container).insertAfter($(container).next());

		// Enable disabled move-up button
		$(this).next('.move-up').removeClass('hidden');
		$(this).siblings('.move-up-disabled').addClass('hidden');

		// Disable move-down button
		if ($(container).is(':last-child'))
		{
			$(this).addClass('hidden');
			$(this).prev('.move-down-disabled').removeClass('hidden');
		}

		// Enable move-down button for sibling
		$(container).prev().find('dd div .move-down').removeClass('hidden');
		$(container).prev().find('dd div .move-down-disabled').addClass('hidden');

		// Disable move-up button for sibling
		if ($(container).prev().is(':first-child'))
		{
			$(container).prev().find('dd div .move-up').addClass('hidden');
			$(container).prev().find('dd div .move-up-disabled').removeClass('hidden');
		}
	}
	
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
