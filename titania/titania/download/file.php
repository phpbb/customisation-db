<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
* @ignore
*/
define('IN_TITANIA', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './../');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;

phpbb::$user->add_lang('viewtopic');

// Thank you sun.
if (isset($_SERVER['CONTENT_TYPE']))
{
	if ($_SERVER['CONTENT_TYPE'] === 'application/x-java-archive')
	{
		exit;
	}
}
else if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Java') !== false)
{
	exit;
}

$download_id = request_var('id', 0);
$mode = request_var('mode', '');
$thumbnail = request_var('thumb', false);

if (!$download_id)
{
	// Mostly to make moving from Ariel easier
	$revision_id = request_var('revision', 0);
	$contrib_id = request_var('contrib', 0);
	if ($revision_id)
	{
		$sql = 'SELECT attachment_id FROM ' . TITANIA_REVISIONS_TABLE . '
			WHERE revision_id = ' . $revision_id;
		phpbb::$db->sql_query($sql);
		$download_id = phpbb::$db->sql_fetchfield('attachment_id');
		phpbb::$db->sql_freeresult();
	}
	else if ($contrib_id)
	{
		$sql = 'SELECT attachment_id FROM ' . TITANIA_REVISIONS_TABLE . '
			WHERE contrib_id = ' . $contrib_id . '
				AND revision_status = ' . TITANIA_REVISION_APPROVED . '
			ORDER BY revision_id DESC';
		phpbb::$db->sql_query_limit($sql, 1);
		$download_id = phpbb::$db->sql_fetchfield('attachment_id');
		phpbb::$db->sql_freeresult();
	}

	if (!$download_id)
	{
		trigger_error('NO_ATTACHMENT_SELECTED');
	}
}

$sql = 'SELECT *
	FROM ' . TITANIA_ATTACHMENTS_TABLE . "
	WHERE attachment_id = $download_id";
$result = phpbb::$db->sql_query_limit($sql, 1);
$attachment = phpbb::$db->sql_fetchrow($result);
phpbb::$db->sql_freeresult($result);

if (!$attachment)
{
	trigger_error('ERROR_NO_ATTACHMENT');
}

// Don't allow downloads of revisions for TITANIA_CONTRIB_DOWNLOAD_DISABLED items unless on the team or an author.
if ($attachment['object_type'] == TITANIA_CONTRIB)
{
		$sql = 'SELECT contrib_id, revision_status FROM ' . TITANIA_REVISIONS_TABLE . '
			WHERE  attachment_id = ' . $attachment['attachment_id'];
		$result = phpbb::$db->sql_query($sql);
		$revision = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		$contrib = new titania_contribution;
		if (!$contrib->load((int) $revision['contrib_id']))
		{
			trigger_error('NO_ATTACHMENT_SELECTED');
		}

		if ((($revision['revision_status'] != TITANIA_REVISION_APPROVED && titania::$config->require_validation && titania_types::$types[$contrib->contrib_type]->require_validation) || $contrib->contrib_status == TITANIA_CONTRIB_DOWNLOAD_DISABLED) && !$contrib->is_author && !$contrib->is_active_coauthor && !titania_types::$types[$contrib->contrib_type]->acl_get('view') && !titania_types::$types[$contrib->contrib_type]->acl_get('moderate'))
		{
			// Is it the MPV server requesting the file?  If so we allow non-approved file downloads
			$is_mpv_server = false;
			foreach (titania::$config->mpv_server_list as $data)
			{
				$dns_ipv4 = dns_get_record($data['host'], DNS_A);
				$dns_ipv6 = dns_get_record($data['host'], DNS_AAAA);

				if ((isset($dns_ipv4[0]) && isset($dns_ipv4[0]['ip']) && phpbb::$user->ip == $dns_ipv4[0]['ip']) || (isset($dns_ipv6[0]) && isset($dns_ipv6[0]['ip']) && phpbb::$user->ip == $dns_ipv6[0]['ip']))
				{
					$is_mpv_server = true;
					break;
				}
			}

			if (!$is_mpv_server)
			{
				trigger_error('NO_ATTACHMENT_SELECTED');
			}
		}

		// Access Level
		if ($contrib->is_author || $contrib->is_active_coauthor)
		{
			titania::$access_level = TITANIA_ACCESS_AUTHORS;
		}

		unset($contrib);
}

if ($thumbnail)
{
	$attachment['physical_filename'] = utf8_basename($attachment['attachment_directory']) . '/thumb_' . utf8_basename($attachment['physical_filename']);
}
else
{
	$attachment['physical_filename'] = utf8_basename($attachment['attachment_directory']) . '/' . utf8_basename($attachment['physical_filename']);
}

if ($attachment['is_orphan'] && phpbb::$user->data['user_id'] != $attachment['attachment_user_id'] && !phpbb::$auth->acl_get('a_attach'))
{
	trigger_error('ERROR_NO_ATTACHMENT');
}
else if (!download_allowed())
{
	header('HTTP/1.0 403 Forbidden');
	trigger_error('SORRY_AUTH_VIEW_ATTACH');
}
else if ($attachment['attachment_access'] < titania::$access_level && $attachment['attachment_access'] == TITANIA_ACCESS_TEAMS)
{
	header('HTTP/1.0 403 Forbidden');
	trigger_error('SORRY_AUTH_VIEW_ATTACH');
}
else if ($attachment['attachment_access'] < titania::$access_level && $attachment['attachment_access'] == TITANIA_ACCESS_AUTHORS)
{
	// Author level check
	$contrib = false;
	switch ((int) $attachment['object_type'])
	{
		case TITANIA_FAQ :
			$sql = 'SELECT c.contrib_id, c.contrib_user_id
				FROM ' . TITANIA_CONTRIB_FAQ_TABLE . ' f, ' . TITANIA_CONTRIBS_TABLE . ' c
				WHERE f.faq_id = ' . $attachment['object_id'] . '
					AND c.contrib_id = f.contrib_id';
			$result = phpbb::$db->sql_query($sql);
			$contrib = phpbb::$db->sql_fetchrow($result);
			phpbb::$db->sql_freeresult($result);
		break;

		case TITANIA_SUPPORT :
		case TITANIA_QUEUE_DISCUSSION :
			$sql = 'SELECT c.contrib_id, c.contrib_user_id
				FROM ' . TITANIA_POSTS_TABLE . ' p, ' . TITANIA_TOPICS_TABLE . ' t, ' . TITANIA_CONTRIBS_TABLE . ' c
				WHERE p.post_id = ' . $attachment['object_id'] . '
					AND t.topic_id = p.topic_id
					AND c.contrib_id = t.parent_id';
			$result = phpbb::$db->sql_query($sql);
			$contrib = phpbb::$db->sql_fetchrow($result);
			phpbb::$db->sql_freeresult($result);
		break;
	}

	if ($contrib !== false)
	{
		if ($contrib['contrib_user_id'] == phpbb::$user->data['user_id'])
		{
			// Main author
			titania::$access_level = TITANIA_ACCESS_AUTHORS;
		}
		else
		{
			// Coauthor
			$sql = 'SELECT user_id FROM ' . TITANIA_CONTRIB_COAUTHORS_TABLE . '
				WHERE contrib_id = ' . $contrib['contrib_id'] . '
					AND user_id = ' . phpbb::$user->data['user_id'] . '
					AND active = 1';
			$result = phpbb::$db->sql_query($sql);
			if (phpbb::$db->sql_fetchrow($result))
			{
				titania::$access_level = TITANIA_ACCESS_AUTHORS;
			}
			phpbb::$db->sql_freeresult($result);
		}
	}

	// Still not authorised?
	if ($attachment['attachment_access'] < titania::$access_level)
	{
		header('HTTP/1.0 403 Forbidden');
		trigger_error('SORRY_AUTH_VIEW_ATTACH');
	}
}

/*
* Can not currently be done with the way extensions are setup... @todo?

$extensions = titania::$cache->obtain_attach_extensions();

$download_mode = (int) $extensions[$attachment['extension']]['download_mode'];

$attachment['physical_filename'] = utf8_basename($attachment['physical_filename']);
$display_cat = $extensions[$attachment['extension']]['display_cat'];

if (($display_cat == ATTACHMENT_CATEGORY_IMAGE || $display_cat == ATTACHMENT_CATEGORY_THUMB) && !$user->optionget('viewimg'))
{
	$display_cat = ATTACHMENT_CATEGORY_NONE;
}

if ($display_cat == ATTACHMENT_CATEGORY_FLASH && !$user->optionget('viewflash'))
{
	$display_cat = ATTACHMENT_CATEGORY_NONE;
}
*/

if (!$thumbnail)
{
	// Update download count
	$sql = 'UPDATE ' . TITANIA_ATTACHMENTS_TABLE . '
		SET download_count = download_count + 1
		WHERE attachment_id = ' . $attachment['attachment_id'];
	phpbb::$db->sql_query($sql);

	// Update download count for the contrib object as well
	if ($attachment['object_type'] == TITANIA_CONTRIB)
	{
		$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . '
			SET contrib_downloads = contrib_downloads + 1
			WHERE contrib_id = ' . $attachment['object_id'];
		phpbb::$db->sql_query($sql);
	}
}

if (!$thumbnail && $mode === 'view' && (strpos($attachment['mimetype'], 'image') === 0) && ((strpos(strtolower(phpbb::$user->browser), 'msie') !== false) && (strpos(strtolower(phpbb::$user->browser), 'msie 8.0') === false)))
{
	wrap_img_in_html(titania_url::build_url('download', array('id' => $attachment['attachment_id'])), $attachment['real_filename']);
	file_gc();
}
else
{
	send_file_to_browser($attachment, titania::$config->upload_path);
	file_gc();
}


/**
* Wraps an url into a simple html page. Used to display attachments in IE.
* this is a workaround for now; might be moved to template system later
* direct any complaints to 1 Microsoft Way, Redmond
*/
function wrap_img_in_html($src, $title)
{
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-Strict.dtd">';
	echo '<html>';
	echo '<head>';
	echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />';
	echo '<title>' . $title . '</title>';
	echo '</head>';
	echo '<body>';
	echo '<div>';
	echo '<img src="' . $src . '" alt="' . $title . '" />';
	echo '</div>';
	echo '</body>';
	echo '</html>';
}

/**
* Send file to browser
*/
function send_file_to_browser($attachment, $upload_dir)
{
	$filename = $upload_dir . $attachment['physical_filename'];

	if (!@file_exists($filename))
	{
		trigger_error(phpbb::$user->lang['ERROR_NO_ATTACHMENT'] . '<br /><br />' . sprintf(phpbb::$user->lang['FILE_NOT_FOUND_404'], $filename));
	}

	// Correct the mime type - we force application/octetstream for all files, except images
	// Please do not change this, it is a security precaution
	if (strpos($attachment['mimetype'], 'image') !== 0)
	{
		$attachment['mimetype'] = (strpos(strtolower(phpbb::$user->browser), 'msie') !== false || strpos(strtolower(phpbb::$user->browser), 'opera') !== false) ? 'application/octetstream' : 'application/octet-stream';
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
			trigger_error(phpbb::$user->lang['UNABLE_TO_DELIVER_FILE'] . '<br />' . sprintf(phpbb::$user->lang['TRACKED_PHP_ERROR'], $php_errormsg));
		}

		trigger_error('UNABLE_TO_DELIVER_FILE');
	}

	// Now the tricky part... let's dance
	header('Pragma: public');

	// Send out the Headers. Do not set Content-Disposition to inline please, it is a security measure for users using the Internet Explorer.
	$is_ie8 = (strpos(strtolower(phpbb::$user->browser), 'msie 8.0') !== false);
	header('Content-Type: ' . $attachment['mimetype']);

	if ($is_ie8)
	{
		header('X-Content-Type-Options: nosniff');
	}

	if (empty(phpbb::$user->browser) || (!$is_ie8 && (strpos(strtolower(phpbb::$user->browser), 'msie') !== false)))
	{
		header('Content-Disposition: attachment; ' . header_filename(htmlspecialchars_decode($attachment['real_filename'])));
		if (empty(phpbb::$user->browser) || (strpos(strtolower(phpbb::$user->browser), 'msie 6.0') !== false))
		{
			header('expires: -1');
		}
	}
	else
	{
		header('Content-Disposition: ' . ((strpos($attachment['mimetype'], 'image') === 0) ? 'inline' : 'attachment') . '; ' . header_filename(htmlspecialchars_decode($attachment['real_filename'])));
		if ($is_ie8 && (strpos($attachment['mimetype'], 'image') !== 0))
		{
			header('X-Download-Options: noopen');
		}
	}

	if ($size)
	{
		header("Content-Length: $size");
	}

	// Close the db connection before sending the file
	phpbb::$db->sql_close();

	if (!set_modified_headers($attachment['filetime'], phpbb::$user->browser))
	{
		// Try to deliver in chunks
		@set_time_limit(0);

		$fp = @fopen($filename, 'rb');

		if ($fp !== false)
		{
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
	file_gc();
}

/**
* Get a browser friendly UTF-8 encoded filename
*/
function header_filename($file)
{
	$user_agent = (!empty($_SERVER['HTTP_USER_AGENT'])) ? htmlspecialchars((string) $_SERVER['HTTP_USER_AGENT']) : '';

	// There be dragons here.
	// Not many follows the RFC...
	if (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Safari') !== false || strpos($user_agent, 'Konqueror') !== false)
	{
		return "filename=" . rawurlencode($file);
	}

	// follow the RFC for extended filename for the rest
	return "filename*=UTF-8''" . rawurlencode($file);
}

/**
* Check if downloading item is allowed
*/
function download_allowed()
{
	if (!phpbb::$config['secure_downloads'])
	{
		return true;
	}

	$url = (!empty($_SERVER['HTTP_REFERER'])) ? trim($_SERVER['HTTP_REFERER']) : trim(getenv('HTTP_REFERER'));

	if (!$url)
	{
		return (phpbb::$config['secure_allow_empty_referer']) ? true : false;
	}

	// Split URL into domain and script part
	$url = @parse_url($url);

	if ($url === false)
	{
		return (phpbb::$config['secure_allow_empty_referer']) ? true : false;
	}

	$hostname = $url['host'];
	unset($url);

	$allowed = (phpbb::$config['secure_allow_deny']) ? false : true;
	$iplist = array();

	if (($ip_ary = @gethostbynamel($hostname)) !== false)
	{
		foreach ($ip_ary as $ip)
		{
			if ($ip)
			{
				$iplist[] = $ip;
			}
		}
	}

	// Check for own server...
	$server_name = phpbb::$user->host;

	// Forcing server vars is the only way to specify/override the protocol
	if (phpbb::$config['force_server_vars'] || !$server_name)
	{
		$server_name = phpbb::$config['server_name'];
	}

	if (preg_match('#^.*?' . preg_quote($server_name, '#') . '.*?$#i', $hostname))
	{
		$allowed = true;
	}

	// Get IP's and Hostnames
	if (!$allowed)
	{
		$sql = 'SELECT site_ip, site_hostname, ip_exclude
			FROM ' . SITELIST_TABLE;
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$site_ip = trim($row['site_ip']);
			$site_hostname = trim($row['site_hostname']);

			if ($site_ip)
			{
				foreach ($iplist as $ip)
				{
					if (preg_match('#^' . str_replace('\*', '.*?', preg_quote($site_ip, '#')) . '$#i', $ip))
					{
						if ($row['ip_exclude'])
						{
							$allowed = (phpbb::$config['secure_allow_deny']) ? false : true;
							break 2;
						}
						else
						{
							$allowed = (phpbb::$config['secure_allow_deny']) ? true : false;
						}
					}
				}
			}

			if ($site_hostname)
			{
				if (preg_match('#^' . str_replace('\*', '.*?', preg_quote($site_hostname, '#')) . '$#i', $hostname))
				{
					if ($row['ip_exclude'])
					{
						$allowed = (phpbb::$config['secure_allow_deny']) ? false : true;
						break;
					}
					else
					{
						$allowed = (phpbb::$config['secure_allow_deny']) ? true : false;
					}
				}
			}
		}
		phpbb::$db->sql_freeresult($result);
	}

	return $allowed;
}

/**
* Check if the browser has the file already and set the appropriate headers-
* @returns false if a resend is in order.
*/
function set_modified_headers($stamp, $browser)
{
	// let's see if we have to send the file at all
	$last_load 	=  isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? strtotime(trim($_SERVER['HTTP_IF_MODIFIED_SINCE'])) : false;
	if ((strpos(strtolower($browser), 'msie 6.0') === false) && (strpos(strtolower($browser), 'msie 8.0') === false))
	{
		if ($last_load !== false && $last_load <= $stamp)
		{
			if (substr(strtolower(@php_sapi_name()),0,3) === 'cgi')
			{
				// in theory, we shouldn't need that due to php doing it. Reality offers a differing opinion, though
				header('Status: 304 Not Modified', true, 304);
			}
			else
			{
				header('HTTP/1.0 304 Not Modified', true, 304);
			}
			// seems that we need those too ... browsers
			header('Pragma: public');
			header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
			return true;
		}
		else
		{
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $stamp) . ' GMT');
		}
	}
	return false;
}

function file_gc()
{
	if (!empty(phpbb::$cache))
	{
		phpbb::$cache->unload();
	}
	phpbb::$db->sql_close();
	exit;
}

?>
