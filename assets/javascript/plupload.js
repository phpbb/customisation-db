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

	if (phpbb.plupload.isScreenshots) {
		phpbb.plupload.uploader.bind('FileUploaded', function(up, file, response) {
			if (file.status !== plupload.DONE) {
				return;
			}

			try {
				var json = $.parseJSON(response.response);
			} catch (e) {
				return;
			}
			var data = json['data'][0],
				attachID = data['attach_id'],
				$row = $('[data-attach-id="' + attachID + '"]'),
				$previewControl = $row.find('.file-contrib-preview-control'),
				$preview = $('<img />').attr({
					src: (data['thumb']) ? data['thumb'] : data['url'],
					class: 'screenshot'
				});


			$row.append($('<input />').attr({
				name: 'attach_order[]',
				value: attachID,
				class: 'attach_order',
				type: 'hidden'
			}));
			$previewControl.val(attachID);

			if (json['data'].length === 1) {
				setPreview($row.find('.file-contrib-preview'));
			}

			$preview.load(function() {
				if (data['thumb']) {
					$preview.removeClass('screenshot');
					$preview = $('<a />').attr({
						href: data['url'],
						class: 'screenshot'
					}).html($preview);
				}
				$row.find('.file-preview').html($preview).show('slow');
				$row.find('.file-contrib-preview').show('fast');
			});
		});

		var setPreview = function($this) {
			$('.file-contrib-preview').removeClass('file-contrib-preview-active');
			$('.file-contrib-preview-control').removeProp('checked');
			$this.addClass('file-contrib-preview-active')
				.siblings('.file-contrib-preview-control').prop('checked', true);
		};

		$('#file-list')
			.sortable({
				revert: true
			})
			.on('click', '.file-contrib-preview', function() {
				setPreview($(this));
			})
			.on('click', '.file-delete', function() {
				var $this = $(this);

				if ($this.siblings('.file-contrib-preview-control').is(':checked')) {
					var $newPreview = $('.file-contrib-preview')
						.not($this.siblings('.file-contrib-preview')).first();
					setPreview($newPreview);
				}
			});
	}
})(jQuery); // Avoid conflicts with other libraries
