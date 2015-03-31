(function($) {  // Avoid conflicts with other libraries

	'use strict';

	phpbb.addAjaxCallback('titania.demo.install', function(res) {
		if (typeof res.url !== 'undefined' && res.url !== '') {
			$('#demo_url_' + $(this).data('branch')).val(res.url);
		}
	});

})(jQuery); // Avoid conflicts with other libraries
