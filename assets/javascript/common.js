var titania = {};

(function($) {  // Avoid conflicts with other libraries

'use strict';

/**
* Filter select options by selected type value.
*
* @param jQuery $typeSelect 		Types <select>
* @param jQuery $optionSelect		Options <select>
* @return undefined
*/
titania.filterOptionsBySelectedType = function($typeSelect, $optionSelect) {
	var $allOptions = $optionSelect.children().clone(),
		filter = function() {
			$optionSelect.html(
				$allOptions.filter('[data-option-type="' + $typeSelect.val() + '"]')
			);
			var $options = $optionSelect.children();
			// Select the option if there's only one.
			if ($options.length === 1) {
				$options.prop('selected', 'selected');
			}
		};

	if (!$typeSelect.length || !$optionSelect.length) {
		return;
	}
	if ($typeSelect.val() != 0) {
		filter();
	}
	$typeSelect.change(function() {
		filter();
	});
};

/**
* Activate Colorbox.
*
* @return undefined
*/
titania.activateColorbox = function() {
	if (typeof $.colorbox === 'function') {
		$('a.screenshot').colorbox({photo: true, rel: 'group1'})
		// Remove ?mode=view from screenshot links as we'll be displaying the image inline, so the image should not
		// be wrapped in HTML in IE
		.each(function() {
			this.href = this.href.replace('?mode=view', '');
		});
	}
};

/**
* Activate toggle buttons on rows.
*
* @param jQuery $rows		Rows to activate buttons on
* @param function callback	Function to run when button is clicked
* @return undefined
*/
titania.activateToggleButtons = function($rows, callback) {
	var $toggles = $rows.find('.toggle');

	$toggles.show().click(function() {
		$(this).toggleClass('icon-expand icon-contract');

		if (typeof callback === 'function') {
			callback.call(this);
		}
	});
	$rows.filter(':first-child').find('.toggle').toggleClass('icon-contract icon-expand');
};

/**
* Hide extra revisions.
*
* @param int maxDisplayed		Max revisions to display
* @return undefined
*/
titania.hideExtraRevisions = function(maxDisplayed) {
	var $revisions = $('.revisions .row');

	if (!$revisions.length) {
		return;
	}

	if ($revisions.length > maxDisplayed) {
		$('.revision-list').find('.show-all').show().click(function() {
			$revisions.show();
			$(this).hide();
		});
		// Show only the first five revisions
		$revisions.slice(maxDisplayed).hide();
	}

	// Hide all of the revision details
	$revisions.slice(1).find('.revision-details').hide();
	// Add toggle button to each revision
	titania.activateToggleButtons($revisions, function() {
		$(this).siblings('.revision-details').toggle('fast');
	});
};

/**
* Hide extra revision translations.
*
* @param int maxDisplayed		Max translations displayed per column
* @return undefined
*/
titania.hideExtraTranslations = function(maxDisplayed) {
	var $translations = $('.translations .row'),
		toggle = function($columns) {
			$columns.each(function() {
				$('li', this).slice(maxDisplayed).toggle();
			});
		};

	if (!$translations.length) {
		return;
	}

	$translations.slice(1).each(function() {
		toggle($('.column1, .column2', this));
	});

	titania.activateToggleButtons($translations, function() {
		toggle($(this).siblings().find('.column1, .column2'));
	});
};

/**
* Updating rating stars.
*
* @param jQuery $stars
* @param int rating
* @param bool rated
*
* @return undefined
*/
titania.updateRating = function($stars, rating, rated) {
	$stars.each(function() {
		var $this = $(this),
			isActive = parseInt(rating) >= parseInt($this.data('rate'));

		if (rated) {
			$this.toggleClass('rating-rated', isActive);
		} else {
			$this.toggleClass('rating-available', isActive);
		}
		$this.toggleClass('rating-inactive', !isActive);
	});
};

/**
* Highlight rating stars when hovering over them
*/
$('a [data-rate]').mouseenter(function() {
	var $active = $(this),
		$stars = $active.parents('.rating').find('[data-rate]');

	titania.updateRating($stars, $active.data('rate'), false);
});

/**
* Reset rating to default value upon moving mouse out of rating.
*/
$('.rating').mouseleave(function() {
	var $rating = $(this),
		$stars = $rating.find('[data-rate]');
	titania.updateRating($stars, $rating.data('rating'), $rating.hasClass('rated'));	
});

/**
* Display Colorize it frame.
*/
$('[data-colorizeit-url]').one('click', function(e) {
	$('#colorizeit-placeholder')
	.show()
	.replaceWith(
		$('<iframe>')
		.attr('id', 'colorizeit-frame')
		.attr('src', $(this).data('colorizeit-url')
	));

	e.preventDefault();
});

/**
* Display additional info when hovering over download button.
*/
$('.contrib-download').hover(function() {
	$('.download-info').hide();
	$('.download-info', this).fadeIn('slow');
}, function() {
	$('.download-info', this).fadeOut('slow');
});

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

	// Prevent the user from submitting a form more than once.
	$('input[type="submit"]').click(function(event) {
		// Since the submit value is no longer passed once the button is disabled, we must hide the original button and create a clone.
		$(this).after($(this).clone()).addClass('hidden');
		$(this).parent().children('input[type="submit"]:not(.hidden)').attr('disabled', 'disabled').addClass('disabled');
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

$(function() {
	// Filter categories by contrib type.
	titania.filterOptionsBySelectedType($('select#contrib_type'), $('select#contrib_category'));
	// Activate Colorbox
	titania.activateColorbox();
	// Only display first 5 revisions
	titania.hideExtraRevisions(5);
	// Hide extra translations
	titania.hideExtraTranslations(3);
});

})(jQuery);
