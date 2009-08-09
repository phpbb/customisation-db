<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

if (!class_exists('fileupload'))
{
	include PHPBB_ROOT_PATH . 'includes/functions_upload.' . PHP_EXT;
}

/**
 * Handles uploading attachments for Titania.
 *
 * Still needs a lot of work done.
 *
 */
class titania_uploader extends fileupload
{
	/**
	 * Set some defualt class variables that we need.
	 *
	 * @var mix
	 */
	private $form_name = '';
	private $contrib_id = 0;
	private $file_type = '';

	/**
	 * Class constructor
	 *
	 * @param string $form_name Form name from where we can find the file.
	 * @param int $contrib_id The contribution id which the attachment belongs to, if 0, the attachment will be an orphan and the
	 * attachment record in the DB should be updated once the contrib_id is known.
	 */
	public function __construct($form_name = 'uploadify', $contrib_id = 0, $file_type = 'contrib')
	{
		// Set class variables.
		$this->form_name = $form_name;
		$this->contrib_id = $contrib_id;
		$this->file_type = $file_type;

		// Add posting language for the attachment language strings.
		phpbb::$user->add_lang('posting');
	}

	/**
	 * Uploads a file to server
	 *
	 * @return array filedata
	 */
	public function upload_file()
	{
		$filedata = array(
			'error'			=> array(),
			'post_attach'	=> ($this->is_valid($this->form_name)) ? true : false,
		);

		if (!$filedata['post_attach'])
		{
			$filedata['error'][] = phpbb::$user->lang['NO_UPLOAD_FORM_FOUND'];
			return $filedata;
		}

		$extensions = titania::$cache->obtain_attach_extensions();
		$this->set_allowed_extensions(array_keys($extensions['_allowed_' . $this->file_type]));

		$file = $this->form_upload($this->form_name);

		if ($file->init_error)
		{
			$filedata['post_attach'] = false;
			return $filedata;
		}

		// @todo Support attachment categories

		// Set max file size for anyone but team members.
		if (titania::$access_level != TITANIA_ACCESS_TEAMS)
		{
			$this->set_max_filesize(phpbb::$config['max_filesize']);
		}

		$file->clean_filename('unique', phpbb::$user->data['user_id'] . '_');

		// @todo config for Titania upload path.
		$file->move_file(titania::$config->titania_script_path . 'files/', false, true);

		if (sizeof($file->error))
		{
			$file->remove();
			$filedata['error'] = array_merge($filedata['error'], $file->error);
			$filedata['post_attach'] = false;

			return $filedata;
		}

		$filedata['filesize'] = $file->get('filesize');
		$filedata['mimetype'] = $file->get('mimetype');
		$filedata['extension'] = $file->get('extension');
		$filedata['physical_filename'] = $file->get('realname');
		$filedata['real_filename'] = $file->get('uploadname');
		$filedata['filetime'] = time();
		$filedata['md5_checksum'] = md5_file($file->get('destination_file'));

		// Check our complete quota
		//@todo Seperate config for titania attachments
		if (phpbb::$config['attachment_quota'])
		{
			if (phpbb::$config['upload_dir_size'] + $file->get('filesize') > phpbb::$config['attachment_quota'])
			{
				$filedata['error'][] = phpbb::$user->lang['ATTACH_QUOTA_REACHED'];
				$filedata['post_attach'] = false;

				$file->remove();

				return $filedata;
			}
		}

		// Check free disk space
		if ($free_space = @disk_free_space(PHPBB_ROOT_PATH . phpbb::$config['upload_path']))
		{
			if ($free_space <= $file->get('filesize'))
			{
				$filedata['error'][] = phpbb::$user->lang['ATTACH_QUOTA_REACHED'];
				$filedata['post_attach'] = false;

				$file->remove();

				return $filedata;
			}
		}

		return $filedata;
	}
}