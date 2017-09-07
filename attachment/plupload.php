<?php


namespace phpbb\titania\attachment;

class plupload extends \phpbb\plupload\plupload
{
	/**
	 * Configure Plupload for use under Titania.
	 *
	 * @param \phpbb\titania\config\config $ext_config
	 * @param \phpbb\template\template $template
	 * @param $s_action
	 * @param $object_type
	 * @param $max_files
	 * @param $max_filesize
	 */
	public function configure_ext(\phpbb\titania\config\config $ext_config, \phpbb\template\template $template, $s_action, $object_type, $max_files, $max_filesize)
	{
		$filters = $this->generate_filter_string_ext($ext_config, $object_type);
		$chunk_size = $this->get_chunk_size();
		$resize = $this->generate_resize_string();

		$template->assign_vars(array(
			'S_RESIZE'			=> $resize,
			'S_PLUPLOAD_EXT'	=> true,
			'FILTERS'			=> $filters,
			'CHUNK_SIZE'		=> $chunk_size,
			'S_PLUPLOAD_URL'	=> htmlspecialchars_decode($s_action),
			'MAX_ATTACHMENTS'	=> $max_files,
			'ATTACH_ORDER'		=> ($this->config['display_order']) ? 'asc' : 'desc',
			'L_TOO_MANY_ATTACHMENTS'	=> $this->user->lang('TOO_MANY_ATTACHMENTS', $max_files),
			'FILESIZE'			=> $max_filesize,
			'S_PLUPLOAD_KEY'	=> generate_link_hash('plupload_key'),
		));

		$this->user->add_lang('plupload');
	}

	/**
	 * Looks at the list of allowed extensions and generates a string
	 * appropriate for use in configuring plupload with
	 *
	 * @param \phpbb\titania\config\config $ext_config
	 * @param int $object_type
	 *
	 * @return string
	 */
	public function generate_filter_string_ext($ext_config, $object_type)
	{
		if (!array_key_exists($object_type, $ext_config->upload_allowed_extensions))
		{
			return '';
		}
		$extensions = $ext_config->upload_allowed_extensions[$object_type];

		$filters = array(sprintf(
			"{title: '%s', extensions: '%s'}",
			addslashes(ucfirst(strtolower($this->user->lang('ALLOWED')))),
			addslashes(implode(',', $extensions))
		));

		return implode(',', $filters);
	}
}
