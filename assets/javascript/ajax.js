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

	phpbb.addAjaxCallback('titania.category.load', function(res) {
		var $this = $(this),
			$crumbs = $('#nav-breadcrumbs .breadcrumbs'),
			$title = $('title'),
			title = $title.html(),
			$categories = $('.categories [data-category-id]'),
			$contribList = $('.contrib-list-container'),
			$search = $('#category-search');

		phpbb.history.replaceUrl($this.attr('href'));

		var getParents = function($self) {
			var $tree = $self.parents('[data-parent-id]'),
				parentID = $tree.data('parent-id');

			if (parentID) {
				$tree = $tree.add(getParents($('[data-category-id="' + parentID + '"]')));
			}
			return $tree;
		};
		if ($this.data('category-id') !== undefined) {
			var $children = $('.categories [data-parent-id="' + $this.data('category-id') + '"]');
			$children.slideDown('slow');

			$('.categories [data-parent-id]').not($children.add(getParents($this))).slideUp('slow');
			$('.categories .active').removeClass('active');
			$this.addClass('active');
		}

		$contribList.fadeOut('fast', function() {
			$contribList.find('.contrib-list').html(res.content);
			$contribList.find('.action-bar').html(res.pagination).show();
			$contribList.fadeIn('fast');
		});
		$('#queue-stats-link').remove();

		if (res.u_queue_stats) {
			var $queueStats = $('<a />')
				.attr('href', res.u_queue_stats)
				.html(res.l_queue_stats);
			$('.titania-navigation').append(
				$('<li />').attr('id', 'queue-stats-link').html($queueStats)
			);
		}

		$search.find('[name="c[]"]').remove();
		$search.find('#search_keywords').after(
			$('<input \>').attr({
				type: 'hidden',
				name: 'c[]',
				value: $this.data('category-id')
			})
		);

		$categories.each(function() {
			var $this = $(this);
			$this.attr('href', res.categories[$this.data('category-id')]);
		});

		titania.updateSortOptions($('.branch-sort'), $('.branch-sort-options'), res.branches);
		titania.updateSortOptions($('.key-sort'), $('.key-sort-options'), res.sort);

		$title.html(title.substr(0, title.indexOf('-') + 2) + res.title);
		$crumbs.children(':not(:first-child)').remove();
		$crumbs.append(res.breadcrumbs);
	});

	titania.updateSortOptions = function($container, $sort, options) {
		$.each(options, function(i) {
			var option = options[i],
				$option = $sort.find('[data-sort="' + option.ID + '"]');

			if (option.ACTIVE) {
				$('.sort-active', $container).html(option.NAME);
				$option.addClass('active');
			} else {
				$option.attr('href', option.URL).removeClass('active');
			}
		});
	};

})(jQuery); // Avoid conflicts with other libraries
