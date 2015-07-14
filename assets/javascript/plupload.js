(function($) {  // Avoid conflicts with other libraries

	'use strict';

	if (phpbb.plupload.isRevision) {
		phpbb.plupload.uploader.bind('FileUploaded', function(up, file, response) {
			if (file.status === plupload.DONE) {
				try {
					var json = $.parseJSON(response.response);
				} catch (e) {
					return;
				}
				var attachID = json['data'][0]['attach_id'],
					$obsoleteRows = $('.attach-row', '#file-list').not('[data-attach-id="' + attachID + '"]');
				$obsoleteRows.fadeOut('slow', function() {
					$obsoleteRows.remove();
				});
				$('#new_revision').append(
					$('<input />').attr({
						type:'hidden',
						name: 'attachment_id',
						value: attachID
					})
				);
			}
		});
	}

})(jQuery); // Avoid conflicts with other libraries
