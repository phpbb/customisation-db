<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\titania\controller;

use phpbb\titania\access;
use phpbb\titania\entity\package;
use Symfony\Component\Filesystem\Filesystem;

class download
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\controller\helper */
	protected $helper;

	/** @var \phpbb\titania\config\config */
	protected $ext_config;

	/** @var \phpbb\titania\access */
	protected $access;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var array */
	protected $file;

	/** @var int */
	protected $id;

	/** @var string */
	protected $type;

	const OK = 200;
	const FORBIDDEN = 403;
	const NOT_FOUND = 404;
	const INTERNAL_SERVER_ERROR = 500;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface $db
	* @param \phpbb\auth\auth $auth
	* @param \phpbb\user $user
	* @param \phpbb\request\request_interface $request
	* @param \phpbb\titania\controller\helper $helper
	* @param \phpbb\titania\config\config $ext_config
	* @param \phpbb\titania\access $access
	* @param string $phpbb_root_path
	* @param string $php_ext
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\auth\auth $auth, \phpbb\user $user, \phpbb\request\request $request, \phpbb\titania\controller\helper $helper, \phpbb\titania\config\config $ext_config, access $access, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->auth = $auth;
		$this->user = $user;
		$this->request = $request;
		$this->helper = $helper;
		$this->ext_config = $ext_config;
		$this->access = $access;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->user->add_lang('viewtopic');
		require($this->phpbb_root_path . 'includes/functions_download.' . $this->php_ext);
	}

	/**
	* Output attachment browser.
	*
	* @param int	$id		Attachment id.
	* @param string	$type	Type of download (manual or composer)
	* @return \Symfony\Component\HttpFoundation\Response if error found. Otherwise method exits.
	*/
	public function file($id , $type)
	{
		$this->check_invalid_request();
		$this->id = (int) $id;
		$this->type = $type;

		// If no download id is provided, check for legacy download.
		if (!$this->id)
		{
			$this->id = (int) $this->get_legacy_download_id();
		}

		$mode = $this->request->variable('mode', '');
		$thumbnail = $this->request->variable('thumb', false);
		$status = $this->load_file_data();

		if ($status !== self::OK)
		{
			$error = array(
				self::NOT_FOUND		=> 'ERROR_NO_ATTACHMENT',
				self::FORBIDDEN		=> 'SORRY_AUTH_VIEW_ATTACH',
			);

			return $this->helper->error($error[$status], $status);
		}

		$directory = utf8_basename($this->file['attachment_directory']) . '/';
		$base_filename = utf8_basename($this->file['physical_filename']);
		$is_image = strpos($this->file['mimetype'], 'image') === 0;
		$is_ie = strpos(strtolower($this->user->browser), 'msie') !== false;
		$display_cat = ($is_image) ? ATTACHMENT_CATEGORY_IMAGE : ATTACHMENT_CATEGORY_NONE;

		if ($thumbnail && $is_image)
		{
			$this->file['physical_filename'] = $directory . 'thumb_' . $base_filename;
			$display_cat = ATTACHMENT_CATEGORY_THUMB;
		}
		else
		{
			$this->file['physical_filename'] = $directory . $base_filename;
			$this->increase_download_count();
		}

		if ($type === 'composer')
		{
			$composer_package = $this->file['physical_filename'] . '.composer';

			if (!file_exists($this->ext_config->upload_path . $composer_package))
			{
				$package = new package();
				$package->set_source($this->ext_config->upload_path . $this->file['physical_filename']);
				$package->set_temp_path($this->ext_config->contrib_temp_path, true);

				$ext_base_path = $package->find_directory(
					array(
						'files' => array(
							'required' => 'composer.json',
							'optional' => 'ext.php',
						),
					),
					'vendor'
				);

				$package->restore_root($ext_base_path, $this->id);

				$filesystem = new Filesystem();
				$filesystem->copy($package->get_source(), $this->ext_config->upload_path . $composer_package);

				$package->set_source($package->get_source() . '.composer');
				$package->repack(true);
			}

			$this->file['physical_filename'] = $composer_package;
		}

		if (!$thumbnail && $mode === 'view' && $is_image && $is_ie && !phpbb_is_greater_ie_version($this->user->browser, 7))
		{
			$file_url = $this->helper->route('phpbb.titania.download', array('id' => $this->id));

			wrap_img_in_html($file_url, $this->file['real_filename']);
			file_gc();
		}
		else
		{
			$filename = $this->ext_config->upload_path . $this->file['physical_filename'];

			return $this->send_file_to_browser($this->file, $filename, $display_cat);
		}
	}

	/**
	* Load attachment file data.
	*
	* @return int Returns HTTP status code.
	*/
	protected function load_file_data()
	{
		if (!$this->id)
		{
			return self::NOT_FOUND;
		}

		$sql = 'SELECT *
			FROM ' . TITANIA_ATTACHMENTS_TABLE . '
			WHERE attachment_id = ' . (int) $this->id;
		$result = $this->db->sql_query_limit($sql, 1);
		$this->file = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return (!$this->file) ? self::NOT_FOUND : $this->check_accessibility();
	}

	/**
	* Check for invalid request.
	*
	* @return null. Method exits if invalid request found.
	*/
	protected function check_invalid_request()
	{
		// Thank you sun.
		if ($this->request->server('CONTENT_TYPE') === 'application/x-java-archive')
		{
			exit;
		}
		else if (strpos($this->request->server('HTTP_USER_AGENT'), 'Java') !== false)
		{
			exit;
		}
	}

	/**
	* Get attachment id from legacy Ariel URL.
	*
	* @return int
	*/
	protected function get_legacy_download_id()
	{
		// Mostly to make moving from Ariel easier
		$revision_id = $this->request->variable('revision', 0);
		$contrib_id = $this->request->variable('contrib', 0);
		$download_id = 0;

		if ($revision_id)
		{
			$sql = 'SELECT attachment_id
				FROM ' . TITANIA_REVISIONS_TABLE . "
				WHERE revision_id = $revision_id";
			$this->db->sql_query($sql);
			$download_id = (int) $this->db->sql_fetchfield('attachment_id');
			$this->db->sql_freeresult();
		}
		else if ($contrib_id)
		{
			$sql = 'SELECT attachment_id
				FROM ' . TITANIA_REVISIONS_TABLE . '
				WHERE contrib_id = ' . $contrib_id . '
					AND revision_status = ' . TITANIA_REVISION_APPROVED . '
				ORDER BY revision_id DESC';
			$this->db->sql_query_limit($sql, 1);
			$download_id = (int) $this->db->sql_fetchfield('attachment_id');
			$this->db->sql_freeresult();
		}

		return $download_id;
	}

	/**
	* Check file accesibility.
	*
	* @return int Returns HTTP status code.
	*/
	protected function check_accessibility()
	{
		$status = self::OK;

		// Don't allow downloads of revisions for TITANIA_CONTRIB_DOWNLOAD_DISABLED items unless on the team or an author.
		if ($this->file['object_type'] == TITANIA_CONTRIB)
		{
			$status = $this->check_revision_auth();
		}

		if ($status === self::OK)
		{
			// Only revisions can be downloaded as Composer packages
			if ($this->type == 'composer' && $this->file['object_type'] != TITANIA_CONTRIB)
			{
				return self::NOT_FOUND;
			}

			if ($this->file['is_orphan'] && $this->user->data['user_id'] != $this->file['attachment_user_id'] && !$this->auth->acl_get('a_attach'))
			{
				$status = self::NOT_FOUND;
			}
			else if (!download_allowed())
			{
				$status = self::FORBIDDEN;
			}
			else if ($this->file['attachment_access'] < $this->access->get_level() && $this->access->is_team($this->file['attachment_access']))
			{
				$status = self::FORBIDDEN;
			}
			else if ($this->file['attachment_access'] < $this->access->get_level() && $this->access->is_author($this->file['attachment_access']))
			{
				$status = $this->check_author_level_access();
			}
		}

		return $status;
	}

	/**
	* Check whether the file is being requested by an automatic validator.
	*
	* @return bool
	*/
	protected function is_auto_validator()
	{
		foreach ($this->ext_config->mpv_server_list as $data)
		{
			$dns_ipv4 = dns_get_record($data['host'], DNS_A);
			$dns_ipv6 = dns_get_record($data['host'], DNS_AAAA);

			if ($this->dns_matches_user_ip($dns_ipv4) || $this->dns_matches_user_ip($dns_ipv6) || $this->user->ip == $data['ip'])
			{
				return true;
			}
		}
		return false;
	}

	/**
	* Check whether DNS record matches the current user's IP.
	*
	* @param array $record	DNS record.
	* @return bool
	*/
	protected function dns_matches_user_ip($record)
	{
		return isset($record[0]) && isset($record[0]['ip']) && $this->user->ip == $record[0]['ip'];
	}

	/**
	* Check if user can download the requested revision.
	*
	* @return int Returns HTTP status code.
	*/
	protected function check_revision_auth()
	{
		$sql = 'SELECT contrib_id, revision_status
			FROM ' . TITANIA_REVISIONS_TABLE . '
			WHERE attachment_id = ' . (int) $this->id;
		$result = $this->db->sql_query($sql);
		$revision = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$contrib = new \titania_contribution;

		if (!$revision || !$contrib->load((int) $revision['contrib_id']) || !$contrib->is_visible(true))
		{
			return self::NOT_FOUND;
		}

		if ($this->type == 'composer' && !$contrib->type->create_composer_packages)
		{
			return self::NOT_FOUND;
		}

		$is_author = $contrib->is_author || $contrib->is_active_coauthor;
		$can_download_hidden = $is_author || $contrib->type->acl_get('view') || $contrib->type->acl_get('moderate');
		$use_queue = $this->ext_config->require_validation && $contrib->type->require_validation;
		$is_unvalidated = $revision['revision_status'] != TITANIA_REVISION_APPROVED && $use_queue;
		$is_disabled = $contrib->contrib_status == TITANIA_CONTRIB_DOWNLOAD_DISABLED;

		if (!$can_download_hidden && !$this->is_auto_validator() && ($is_unvalidated || $is_disabled))
		{
			return self::NOT_FOUND;
		}

		// Set access Level
		if ($is_author)
		{
			$this->access->set_level(access::AUTHOR_LEVEL);
		}

		return self::OK;
	}

	/**
	* Check user's access against attachment access level.
	*
	* @return int Returns HTTP status code.
	*/
	protected function check_author_level_access()
	{
		// Author level check
		$contrib = false;

		switch ((int) $this->file['object_type'])
		{
			case TITANIA_FAQ :
				$sql = 'SELECT c.contrib_id, c.contrib_user_id
					FROM ' . TITANIA_CONTRIB_FAQ_TABLE . ' f, ' .
						TITANIA_CONTRIBS_TABLE . ' c
					WHERE f.faq_id = ' . (int) $this->file['object_id'] . '
						AND c.contrib_id = f.contrib_id';
				$result = $this->db->sql_query($sql);
				$contrib = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);
			break;

			case TITANIA_SUPPORT :
			case TITANIA_QUEUE_DISCUSSION :
				$sql = 'SELECT c.contrib_id, c.contrib_user_id
					FROM ' . TITANIA_POSTS_TABLE . ' p, ' .
						TITANIA_TOPICS_TABLE . ' t, ' .
						TITANIA_CONTRIBS_TABLE . ' c
					WHERE p.post_id = ' . (int) $this->file['object_id'] . '
						AND t.topic_id = p.topic_id
						AND c.contrib_id = t.parent_id';
				$result = $this->db->sql_query($sql);
				$contrib = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);
			break;
		}

		if ($contrib !== false)
		{
			if ($contrib['contrib_user_id'] == $this->user->data['user_id'])
			{
				// Main author
				$this->access->set_level(access::AUTHOR_LEVEL);
			}
			else
			{
				// Coauthor
				$sql = 'SELECT user_id
					FROM ' . TITANIA_CONTRIB_COAUTHORS_TABLE . '
					WHERE contrib_id = ' . (int) $contrib['contrib_id'] . '
						AND user_id = ' . (int) $this->user->data['user_id'] . '
						AND active = 1';
				$result = $this->db->sql_query($sql);

				if ($this->db->sql_fetchrow($result))
				{
					$this->access->set_level(access::AUTHOR_LEVEL);
				}
				$this->db->sql_freeresult($result);
			}
		}

		// Still not authorised?
		return ($this->file['attachment_access'] < $this->access->get_level()) ? self::FORBIDDEN : self::OK;
	}

	/**
	* Increase attachment download count by one.
	*
	* @return null
	*/
	protected function increase_download_count()
	{
		// Update download count
		$sql = 'UPDATE ' . TITANIA_ATTACHMENTS_TABLE . '
			SET download_count = download_count + 1
			WHERE attachment_id = ' . (int) $this->id;
		$this->db->sql_query($sql);

		// Update download count for the contrib object as well
		if ($this->file['object_type'] == TITANIA_CONTRIB)
		{
			$this->increase_contrib_download_count($this->file['object_id']);
		}
	}

	/**
	* Increase contribution download count by one.
	*
	* @param int $contrib_id	Contribution id.
	* @return null
	*/
	protected function increase_contrib_download_count($contrib_id)
	{
		$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . '
			SET contrib_downloads = contrib_downloads + 1
			WHERE contrib_id = ' . (int) $contrib_id;
		$this->db->sql_query($sql);
	}

	/**
	* Send file to browser
	*
	* Copy of send_file_to_browser() from functions_download.php
	* with some minor modifications to work correctly in Titania.
	*
	* @param array $attachment	Attachment data.
	* @param string $filename	Full path to the attachment file.
	* @param int $category		Attachment category.
	*
	* @return \Symfony\Component\HttpFoundation\Response if error found. Otherwise method exits.
	*/
	protected function send_file_to_browser($attachment, $filename, $category)
	{
		if (!@file_exists($filename))
		{
			return $this->helper->error('ERROR_NO_ATTACHMENT', 404);
		}

		// Correct the mime type - we force application/octetstream for all files, except images
		// Please do not change this, it is a security precaution
		if ($category != ATTACHMENT_CATEGORY_IMAGE || strpos($attachment['mimetype'], 'image') !== 0)
		{
			$attachment['mimetype'] = (strpos(strtolower($this->user->browser), 'msie') !== false || strpos(strtolower($this->user->browser), 'opera') !== false) ? 'application/octetstream' : 'application/octet-stream';
		}

		if (@ob_get_length())
		{
			@ob_end_clean();
		}

		// Now send the File Contents to the Browser
		$size = @filesize($filename);

		// To correctly display further errors we need to make sure we are using the correct headers for both (unsetting content-length may not work)

		// Check if headers already sent or not able to get the file contents.
		if (headers_sent() || !@file_exists($filename) || !@is_readable($filename))
		{
			// PHP track_errors setting On?
			if (!empty($php_errormsg))
			{
				return $this->helper->error(
					$this->user->lang['UNABLE_TO_DELIVER_FILE'] . '<br />' . $this->user->lang('TRACKED_PHP_ERROR', $php_errormsg),
					self::INTERNAL_SERVER_ERROR
				);
			}

			return $this->helper->error('UNABLE_TO_DELIVER_FILE', self::INTERNAL_SERVER_ERROR);
		}

		// Make sure the database record for the filesize is correct
		if ($size > 0 && $size != $attachment['filesize'])
		{
			// Update database record
			$sql = 'UPDATE ' . TITANIA_ATTACHMENTS_TABLE . '
				SET filesize = ' . (int) $size . '
				WHERE attachment_id = ' . (int) $attachment['attachment_id'];
			$this->db->sql_query($sql);
		}

		// Now the tricky part... let's dance
		header('Pragma: public');

		// Send out the Headers. Do not set Content-Disposition to inline please, it is a security measure for users using the Internet Explorer.
		header('Content-Type: ' . $attachment['mimetype']);

		if (phpbb_is_greater_ie_version($this->user->browser, 7))
		{
			header('X-Content-Type-Options: nosniff');
		}

		if ($category == ATTACHMENT_CATEGORY_FLASH && request_var('view', 0) === 1)
		{
			// We use content-disposition: inline for flash files and view=1 to let it correctly play with flash player 10 - any other disposition will fail to play inline
			header('Content-Disposition: inline');
		}
		else
		{
			if (empty($this->user->browser) || ((strpos(strtolower($this->user->browser), 'msie') !== false) && !phpbb_is_greater_ie_version($this->user->browser, 7)))
			{
				header('Content-Disposition: attachment; ' . header_filename(htmlspecialchars_decode($attachment['real_filename'])));
				if (empty($this->user->browser) || (strpos(strtolower($this->user->browser), 'msie 6.0') !== false))
				{
					header('expires: -1');
				}
			}
			else
			{
				header('Content-Disposition: ' . ((strpos($attachment['mimetype'], 'image') === 0) ? 'inline' : 'attachment') . '; ' . header_filename(htmlspecialchars_decode($attachment['real_filename'])));
				if (phpbb_is_greater_ie_version($this->user->browser, 7) && (strpos($attachment['mimetype'], 'image') !== 0))
				{
					header('X-Download-Options: noopen');
				}
			}
		}

		if ($size)
		{
			header("Content-Length: $size");
		}

		// Close the db connection before sending the file etc.
		file_gc(false);

		if (!set_modified_headers($attachment['filetime'], $this->user->browser))
		{
			// Try to deliver in chunks
			@set_time_limit(0);

			$fp = @fopen($filename, 'rb');

			if ($fp !== false)
			{
				// Deliver file partially if requested
				if ($range = phpbb_http_byte_range($size))
				{
					fseek($fp, $range['byte_pos_start']);

					send_status_line(206, 'Partial Content');
					header('Content-Range: bytes ' . $range['byte_pos_start'] . '-' . $range['byte_pos_end'] . '/' . $range['bytes_total']);
					header('Content-Length: ' . $range['bytes_requested']);
				}

				while (!feof($fp))
				{
					echo fread($fp, 8192);
				}
				fclose($fp);
			}
			else
			{
				@readfile($filename);
			}

			flush();
		}

		exit;
	}
}

