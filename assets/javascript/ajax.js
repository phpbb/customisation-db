(function($) {  // Avoid conflicts with other libraries

	'use strict';

	/**
	 * Filter quick edit AJAX requests.
	 * Prevents requests when form is already open.
	 *
	 * @param {object} data
	 * @param {object} event
	 * @returns {boolean}
	 */
	titania.quickEditFilter = function(data, event) {
		var $postbody = $(this).parents('.postbody');

		if ($('form', $postbody).length) {
			event.preventDefault();
			return false;
		}
		return true;
	};

	/**
	 * Filter quick edit form AJAX submissions.
	 * Ensures that the form is submitted through AJAX only when the
	 * Submit button is clicked.
	 *
	 * @param {object} data
	 * @returns {boolean}
	 */
	titania.quickEditSubmitFilter = function(data) {
		return $(this).find('input:submit[data-clicked]').attr('name') === 'submit';
	};

	phpbb.addAjaxCallback('titania.demo.install', function(res) {
		if (typeof res.url !== 'undefined' && res.url !== '') {
			$('#demo_url_' + $(this).data('branch')).val(res.url);
		}
	});

	phpbb.addAjaxCallback('titania.quick_edit', function(response) {
		var $postbody = $(this).parents('.postbody'),
			$post = $postbody.find('.content');

		// Store the original post in case the user cancels the edit
		$post.after($post.clone());
		$post.next().addClass('hidden original_post');
		$post.replaceWith(response.form);
		titania.ajaxify($postbody.find('form'));
	});

	phpbb.addAjaxCallback('titania.quick_edit.submit', function(response) {
		var $form = $(this),
			$postbody = $form.parents('.postbody');

		$form.replaceWith('<div class="content text-content">' + response.message + '</div>');
		$('h3 a', $postbody).html(response.subject);
		$('.original_post', $postbody).remove();
	});
})(jQuery); // Avoid conflicts with other libraries
