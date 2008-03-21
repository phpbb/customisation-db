<?php
/**
*
* @package ariel
* @version $Id: functions_display.php,v 1.9 2008/01/13 11:56:58 paul999 Exp $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* downloads a contrib item
*
* @param integer $contrib_id ID number for the contrib item that is being downloaded
* @param string $filename_internal Internal filename of the contrib file. Usually an md5 hash. Users will not see this filename.
* @param string $filename_external Filename that the user is given.
*/
function download_contrib($contrib_id, $filename_internal, $filename_external, $increment = true)
{
	global $db, $config, $root_path;
	global $user;
	
	$filename = $root_path . $config['site_upload_dir'] . '/' . $filename_internal;

	if (!file_exists($filename))
	{
		trigger_error('The requested file cannot be found.');
	}	
	
	if ($user->data['is_bot'])
	{
		$increment = false;
	}
	
	$ext = get_extension($filename_external);
	
	if (empty($ext) || !in_array($ext, array('zip', 'mod')))
	{
		// Ouch, we dont have a extension
		// We need to check if its a zip file or mod file, after that we need to update the db.
		
		$fp = fopen($filename, 'rb');
		
		if (!$fp)
		{
			trigger_error('Could not open internal file.');
		}
		
		$dd_try = false;
		rewind($fp);

		while (!feof($fp))
		{
			// Check if the signature is valid...
			$signature = fread($fp, 4);

			switch ($signature)
			{
				// 'Local File Header'
				case "\x50\x4b\x03\x04":
				case "\x50\x4b\x01\x02":
				case "\x50\x4b\x05\x06":
				case 'PK00':
					$extension = 'zip';
				break 2;
				
				// We have encountered a header that is weird. Lets look for better data...
				default:
					if (!$dd_try)
					{
						// Unexpected header. Trying to detect wrong placed 'Data Descriptor';
						$dd_try = true;
						fseek($fp, 8, SEEK_CUR); // Jump over 'crc-32'(4) 'compressed-size'(4), 'uncompressed-size'(4)
						continue 2;
					}
					$extension = 'mod';
				break 2;
			}
			$dd_try = false;
		}
		
		fclose($fp);
		
		unset($fp);
		
		$filename_external .= '.' . $extension;
		
		$sql = "UPDATE  " . SITE_CONTRIBS_TABLE .  " SET contrib_filename = '" . $db->sql_escape($filename_external) . "' WHERE contrib_id = $contrib_id";
		$db->sql_query($sql);		
	}

	// increase download count
	if ($increment)
	{
		$sql = 'UPDATE ' . SITE_CONTRIBS_TABLE . '
			SET contrib_downloads = contrib_downloads + 1
			WHERE contrib_id = ' . (int) $contrib_id;
		$db->sql_query($sql);
	}

	$size = @filesize($filename);

	// Try to deliver in chunks
	@set_time_limit(0);

	$fp = @fopen($filename, 'rb');

	if ($fp !== false)
	{
		// Now the tricky part... let's dance
		header('Pragma: public');
		header('Content-Type: ' . get_mimetype($filename));
		header('Content-Disposition: attachment; filename=' . rawurlencode($filename_external));

		if ($size)
		{
			header("Content-Length: $size");
		}

		while (!feof($fp))
		{
			echo fread($fp, 8192);
		}
		fclose($fp);
	}
	else
	{
		trigger_error('Not able to open file for downloading.');
	}

	flush();
	exit;
}
/**
 * second parameter isnt used anymore!
 */
function display_contrib(&$class, $possible_team = false, $contrib_data = array())
{
	global $template, $db, $user, $root_path, $config, $base_path;
	global $phpbb_root_path, $phpEx, $auth;
	
	if (!isset($class) || !is_object($class))
	{
		trigger_error('Something wrong, missing class parameter.');
	}

	$class->tpl_name = 'contrib/contrib_browse';
	$template->assign_var('S_SINGLE', true);
	
	if (!sizeof($contrib_data))
	{
		$where = '';
		if (!($class->team))
		{
		    $where = $db->sql_in_set('contrib_status', array(CONTRIB_RELEASED, CONTRIB_CLEANED)) . ' AND ';
		}
		
		$sql = 'SELECT c.*, u.username, u.user_colour
			FROM ' . SITE_CONTRIBS_TABLE . ' c
			INNER JOIN ' . USERS_TABLE . ' u ON c.user_id = u.user_id
			WHERE ' . $where . ' contrib_type = ' . (int) $class->contrib_type . '
				AND contrib_id = ' . (int) $class->contrib_id;
		
		$result = $db->sql_query($sql);
		$contrib_data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		$message = sprintf('This %s doesn\'t exist, or you dont have permission to view or download it.', $class->contrib_label);
		
		if (!$contrib_data)
		{
			trigger_error($message);
		}
		$class->contrib_data = $contrib_data;
	}
	
	$contrib_data['nice_url'] = false;

	if (!($class->team))
	{
		$contrib_data['nice_url'] = true;
	}

	// download the contrib
	if (request_var('download', false) && !$user->data['is_bot'])
	{
		if (($class->team || $class->author) && request_var('revision_id', false))
		{
			$sql = "SELECT * FROM " . SITE_REVISIONS_TABLE . " 
				WHERE contrib_id = " . $contrib_data['contrib_id'] . "
				AND revision_id = " . request_var('revision_id', 0) . "
				ORDER BY revision_date";		
			$result = $db->sql_query($sql);
			
			$row = $db->sql_fetchrow($result);
			
			$db->sql_freeresult($result);
			download_contrib($contrib_data['contrib_id'], $row['revision_filename_internal'], $row['revision_filename']);
		}
		else
		{
			download_contrib($contrib_data['contrib_id'], $contrib_data['contrib_filename_internal'], $contrib_data['contrib_filename']);
		}
	}
	
	if ($contrib_data['contrib_status'] == CONTRIB_CLEANED && !($class->team))
	{
		trigger_error('This MOD is marked for being cleaned up soon. You are currently only able to download this MOD. If you want to take over this MOD, send one of the MOD team members a PM.');
	}
	
	// rate the contrib
	if (request_var('rate', false) && !$user->data['is_bot'] && $user->data['user_id'] != $contrib_data['user_id'])
	{
	    if (request_var('rate', 0) > 5)
	    {
	        trigger_error('You nasty boy, you cant rate higher as 5 ;)');
		}
		$rate_data = array(
			'contrib_rating' => $contrib_data['contrib_rating'] + request_var('rate', 0),
			'contrib_rate_count'	=> (int) $contrib_data['contrib_rate_count'] + 1,
		);
		
		$sql = 'UPDATE '. SITE_CONTRIBS_TABLE . ' SET 
			' . $db->sql_build_array('UPDATE', $rate_data). '
			WHERE contrib_id = ' . $contrib_data['contrib_id'];
			
		$db->sql_query($sql);
		
		$contrib_data['contrib_rating'] = $rate_data['contrib_rating'];
		$contrib_data['contrib_rate_count'] = $rate_data['contrib_rate_count'];
		
		$template->assign_var('S_RATE', true);
	}
	
	// authors can't rate their own contribs
	if ($user->data['user_id'] == $contrib_data['user_id'])
	{
		$template->assign_var('S_RATE', true);
	}
	
	$sql = "SELECT s.topic_id, s.topic_type, t.forum_id
		FROM " . SITE_TOPICS_TABLE . " s 
			INNER JOIN " . TOPICS_TABLE . " t ON s.topic_id = t.topic_id
			WHERE	s.contrib_id = " . (int)$contrib_data['contrib_id'];
	$result = $db->sql_query($sql);

	$row = $db->sql_fetchrow($result);
	
	$queue_topic = $queue_forum = 0;
	
	if (!$row)
	{
		trigger_error('Could not find topic id for release');
	}
	
	do
	{
		if (!$auth->acl_get('f_read', $row['forum_id']))
		{
			continue;	
		}
		
		$text = '';
		
		$announcment = false;
		
		switch ($row['topic_type'])
		{
			case CONTRIB_TOPIC_DISCUSS:
				// Dont display discuss topic here.
				continue 2;
			break;
			
			case CONTRIB_TOPIC_ANNOUNCEMENTS:
				$text = 'Announcement';
				$announcment = true;
			break;
			
			case CONTRIB_TOPIC_DEVELOPMENT:
			    continue 2;
				$text = 'Development';
			break;
			
			case CONTRIB_TOPIC_QUEUE:
				$text = 'Queue';
				$queue_topic = $row['topic_id'];
				$queue_forum = $row['forum_id'];
			break;
			
			default:
				continue 2;
		}
		if (empty($text))
		{
			// This SHOULD not happen!
			continue;
		}
		$template->assign_block_vars('topics', array(
			'U_TOPIC'		=> append_sid($phpbb_root_path . 'viewtopic.' . $phpEx, 't=' . (int)$row['topic_id']),
			'TOPIC'			=> $text,
			'S_ANNOUNC'	=> $announcment,
		));
	}
	while ($row = $db->sql_fetchrow($result));
	$db->sql_freeresult($result);
	
    if ($class->team || $class->author)
	{
		$sql = "SELECT * FROM " . SITE_REVISIONS_TABLE . "
			WHERE contrib_id = " . $contrib_data['contrib_id'] . "
			ORDER BY revision_date DESC";
		$result = $db->sql_query($sql);

		while ($data = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('rev', array(
				'EAL'				=> $data['revision_filename_internal'],
				'REVISION_VERSION'	=> $data['revision_version'],
				'REVISION_DATE'		=> $user->format_date($data['revision_date']),
				'REVISION_ID'		=> $data['revision_id'],
				'U_DOWNLOAD'		=> append_sid($class->u_action, 'contrib_id=' . $contrib_data['contrib_id'] . '&amp;download=1&amp;revision_id=' . $data['revision_id']),
			));
		}

		$db->sql_freeresult($result);
    }

	if (($class->team))
	{
	    if (request_var('change_owner', false))
	    {
	        $user_id = request_var('change_owner_uid', 0);
	        $contrib_id = $contrib_data['contrib_id'];
	        if ($user_id > 1)
	        {
		        $sql = array(
					"UPDATE " . SITE_CONTRIBS_TABLE . " SET user_id = $user_id WHERE contrib_id = $contrib_id",
					"UPDATE " . SITE_CONTRIB_USER_TABLE . " SET user_id = $user_id WHERE contrib_id = $contrib_id",
					"UPDATE " . SITE_REVISIONS_TABLE . " SET user_id = $user_id WHERE contrib_id = $contrib_id",
					"UPDATE " . SITE_QUEUE_TABLE . " SET user_id = $user_id WHERE contrib_id = $contrib_id",
				);
				for ($i = 0, $c = sizeof($sql); $i < $c; $i++)
				{
				    $db->sql_query($sql[$i]);
				}
				// We get again the contrib data.

				$sql = 'SELECT c.*, u.username, u.user_colour
					FROM ' . SITE_CONTRIBS_TABLE . ' c
					INNER JOIN ' . USERS_TABLE . ' u ON c.user_id = u.user_id
					WHERE contrib_type = ' . (int) $class->contrib_type . '
						AND contrib_id = ' . (int) $contrib_id;

				$result = $db->sql_query($sql);
				$contrib_data = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
			}
		}
		$template->assign_var('S_TEAM', true);
		
		// Assign queue post box.
		queue_post($class, $queue_topic, $queue_forum, $contrib_data['contrib_id']);
		
		
		// Display validating tags.
		$sql = "SELECT t.tag_label FROM " . SITE_CONTRIB_TAGS_TABLE . ' ct, ' . SITE_TAGS_TABLE  . ' t 
			WHERE contrib_id = ' . (int)$class->contrib_data['contrib_id'] . '
				AND t.tag_id = ct.tag_id
				AND ' . $db->sql_in_set('t.tag_id', $class->status);
		$result = $db->sql_query($sql);
		
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('st',array(
				'VALUE'		=> $row['tag_label'],
			));
		}	
		
		if ($class->contrib_data['contrib_status'] == CONTRIB_RELEASED)
		{
			$field = 'contrib_status_update';
			$status = $class->contrib_data['contrib_status_update'];
		}
		else
		{
			$field = 'contrib_status';
			$status = $class->contrib_data['contrib_status'];
		}		
		$template->assign_var('S_STATUS', $status);
	}

	display_contrib_data($contrib_data, $class);

	$tags = select_tags_data($contrib_data['contrib_id'], $class->contrib_class, 'contrib_id', true);

	// output the tags
	foreach ($tags as $groupname => $group)
	{
		if ($groupname == 'phpbb')
		{
			$groupname = 'phpBB';
		}
		else
		{
			$groupname = ucfirst($groupname);
		}
		$template->assign_block_vars('tag_group', array(
			'TAG_GROUP'	=> $groupname)
		);

		foreach ($group as $tag_entry)
		{
			$template->assign_block_vars('tag_group.tag_entry', array(
				'U_TAG'			=> append_sid($base_path . $class->contrib_class . '/db/index.php', 'i=browse&amp;mode=group:' . urlencode(strtolower($groupname)) . '&amp;sub='  . urlencode($tag_entry['tag_name'])),
				'TAG_NAME'	=> ucfirst($tag_entry['tag_label']),
				)
			);
		}
	}
}
function display_contrib_data(&$contrib_data, &$class)
{
	global $template, $phpbb_root_path, $phpEx, $root_path;
	
	$contrib_data['contrib_description'] = generate_text_for_display($contrib_data['contrib_description'], $contrib_data['contrib_bbcode_uid'], $contrib_data['contrib_bbcode_bitfield'], $contrib_data['contrib_bbcode_flags']);
	
	if (isset($contrib_data['nice_url']) && $contrib_data['nice_url'])
	{
		$url = append_sid("{$root_path}{$class->contrib_class}/db/download/{$contrib_data['contrib_id']}/");
	}
	else
	{
		$url = append_sid($class->u_action . '&amp;download=1&amp;contrib_id=' . $contrib_data['contrib_id']);
	}
	
	$template->assign_vars(array(
		'S_CONTRIB'				=> true,
		'AUTHOR_FULL'			=> get_username_string('full', $contrib_data['user_id'], $contrib_data['username'], $contrib_data['user_colour']),
		'CONTRIB_NAME'			=> $contrib_data['contrib_name'],
		'CONTRIB_DESCRIPTION'	=> $contrib_data['contrib_description'],
		'CONTRIB_STATUS'		=> $contrib_data['contrib_status'],
		'CONTRIB_VERSION'		=> $contrib_data['contrib_version'],
		'CONTRIB_REVISION_NAME'	=> $contrib_data['contrib_revision_name'],
		'CONTRIB_FILENAME'		=> $contrib_data['contrib_filename'],
		'CONTRIB_MD5'			=> $contrib_data['contrib_md5'],
		'CONTRIB_PHPBB_VERSION'	=> $contrib_data['contrib_phpbb_version'],
		'CONTRIB_DOWNLOADS'		=> $contrib_data['contrib_downloads'],
		'CONTRIB_RATING'		=> sprintf('%.2f', $contrib_data['contrib_rating'] / max(1, $contrib_data['contrib_rate_count'])),
		'CONTRIB_RATE_COUNT'	=> $contrib_data['contrib_rate_count'],
		'CONTRIB_FILESIZE'		=> ($contrib_data['contrib_filesize'] > 1048576) ? sprintf("%.2f MiB", $contrib_data['contrib_filesize'] / 1048576) : sprintf("%.2f KiB", $contrib_data['contrib_filesize'] / 1024),

		'U_DOWNLOAD'			=> $url,
		'U_RATE'				=> $class->u_action . '&amp;contrib_id=' . $contrib_data['contrib_id'],
		'U_FORM'				=> $class->u_action . '&amp;contrib_id=' . $contrib_data['contrib_id'],
		
	));
}
function queue_post(&$class, $topic_id, $forum_id, $contrib_id)
{
	global $config, $user, $auth, $template, $phpEx;
	global $phpbb_root_path, $phpbb_api, $db;
	
	if (request_var('post', false))
	{
		$message = utf8_normalize_nfc(request_var('message', '', true));
		
		$options = array(
			'poster_id'				=> $user->data['user_id'], 
			'forum_id' 				=> $forum_id, 
			'topic_title'			=> '', 
			'post_text'				=> $message, 
			'topic_id'				=> $topic_id,
			'enable_bbcode'		=> 1,
			'enable_urls'			=> 1,
			'enable_smilies'	=> 1,
			'enable_sig'			=> 1,
			'topic_time_limit'	=> 0,
			'icon_id'					=> 0,
			'post_time'				=> time(),
			'poster_ip'				=> $user->ip,
			'post_edit_locked'	=> 0,
			'topic_status'		=> POST_NORMAL,
			'topic_type'			=> POST_NORMAL,
		);
		
		$topic_data = $phpbb_api->post_add($options);
		redirect(append_sid($user->page['script_path'] . $user->page['page_name'], 'i=queue&mode=overview&contrib_id=' . $class->contrib_data['contrib_id']));
	}
	
	// 
	
	$sql = $db->sql_build_query('SELECT', array(
		'SELECT'	=> 'u.*, p.*',

		'FROM'		=> array(
			USERS_TABLE			=> 'u',
			POSTS_TABLE			=> 'p',
		),

		'WHERE'	=> 'p.topic_id = ' . (int)$topic_id . ' 
			AND u.user_id = p.poster_id')
		);
	
	$result = $db->sql_query($sql);

	$post_list = $rowset = array();
	$bbcode_bitfield = '';
	include($phpbb_root_path . 'includes/bbcode.' . $phpEx);

	// Posts are stored in the $rowset array while $attach_list, $user_cache
	// and the global bbcode_bitfield are built
	while ($row = $db->sql_fetchrow($result))
	{
		$post_list[] = $row['post_id'];

		$rowset[$row['post_id']] = array(
			'post_id'					=> $row['post_id'],
			'post_time'				=> $row['post_time'],
			'user_id'					=> $row['user_id'],
			'username'				=> $row['username'],
			'user_colour'			=> $row['user_colour'],
			'post_subject'		=> $row['post_subject'],

			'post_reported'		=> $row['post_reported'],
			'post_username'		=> $row['post_username'],
			'post_text'				=> $row['post_text'],
			'bbcode_uid'			=> $row['bbcode_uid'],
			'bbcode_bitfield'	=> $row['bbcode_bitfield'],
			'enable_smilies'	=> $row['enable_smilies'],
			'enable_sig'			=> $row['enable_sig'],
		);

		// Define the global bbcode bitfield, will be used to load bbcodes
		$bbcode_bitfield = $bbcode_bitfield | base64_decode($row['bbcode_bitfield']);
	}
	$db->sql_freeresult($result);

	// Instantiate BBCode if need be
	if ($bbcode_bitfield !== '')
	{
		$bbcode = new bbcode(base64_encode($bbcode_bitfield));
	}

	// Output the posts
	for ($i = 0, $end = sizeof($post_list); $i < $end; ++$i)
	{
		$row =& $rowset[$post_list[$i]];
		$poster_id = $row['user_id'];

		// Parse the message and subject
		$message = censor_text($row['post_text']);
		$message = str_replace("\n", '<br />', $message);

		// Second parse bbcode here
		if ($row['bbcode_bitfield'])
		{
			$bbcode->bbcode_second_pass($message, $row['bbcode_uid'], $row['bbcode_bitfield']);
		}

		// Always process smilies after parsing bbcodes
		$message = smiley_text($message);

		// Replace naughty words such as farty pants
		$row['post_subject'] = censor_text($row['post_subject']);

		$postrow = array(
			'S_ROW_COUNT'				=> $i,

			'POST_AUTHOR_FULL'	=> get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),

			'POST_DATE'					=> $user->format_date($row['post_time']),
			'POST_SUBJECT'			=> $row['post_subject'],
			'MESSAGE'						=> $message
		);

		// Dump vars into template
		$template->assign_block_vars('postrow', $postrow);

		unset($rowset[$post_list[$i]]);
	}
	
	$user->add_lang('posting');
	$bbcode_status	= true;
	$smilies_status	= true;
	$img_status			= true;
	$url_status			= true;
	$flash_status		= false;
	$quote_status		= true;
	
	
	// Do show topic type selection only in first post.
	$topic_type_toggle = false;
	
	
	$bbcode_checked		= (($config['allow_bbcode']) ? !$user->optionget('bbcode') : 1);
	$smilies_checked	= (($config['allow_smilies']) ? !$user->optionget('smilies') : 1);
	$urls_checked			= 0;
	$sig_checked			= true;
	
	
	// Page title & action URL, include session_id for security purpose
	$s_action = $class->u_action . '&amp;post=1&amp;contrib_id=' . $contrib_id;
	
	$s_hidden_fields = '';
	$s_hidden_fields .= '<input type="hidden" name="lastclick" value="' . time() . '" />';
	
	$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off' || @ini_get('file_uploads') == '0' || !$config['allow_attachments'] || !$auth->acl_get('u_attach') || !$auth->acl_get('f_attach', $forum_id)) ? '' : ' enctype="multipart/form-data"';
	
	// Start assigning vars for main posting page ...
	$template->assign_vars(array(
		'L_POST_A'								=> 'Queue reply',
		'L_MESSAGE_BODY_EXPLAIN'	=> (intval($config['max_post_chars'])) ? sprintf($user->lang['MESSAGE_BODY_EXPLAIN'], intval($config['max_post_chars'])) : '',
	
		'BBCODE_STATUS'			=> ($bbcode_status) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>'),
		'IMG_STATUS'				=> ($img_status) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF'],
		'FLASH_STATUS'			=> ($flash_status) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF'],
		'SMILIES_STATUS'		=> ($smilies_status) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
		'URL_STATUS'				=> ($bbcode_status && $url_status) ? $user->lang['URL_IS_ON'] : $user->lang['URL_IS_OFF'],
		'MINI_POST_IMG'			=> $user->img('icon_post_target', $user->lang['POST']),
		'POST_DATE'					=> '',
		'ERROR'						=> '',
	
		'S_PRIVMSGS'							=> false,
		'S_CLOSE_PROGRESS_WINDOW'	=> (isset($_POST['add_file'])) ? true : false,
		'S_BBCODE_ALLOWED'				=> $bbcode_status,
		'S_BBCODE_CHECKED'				=> ($bbcode_checked) ? ' checked="checked"' : '',
		'S_SMILIES_ALLOWED'				=> $smilies_status,
		'S_SMILIES_CHECKED'				=> ($smilies_checked) ? ' checked="checked"' : '',
		'S_SIG_ALLOWED'						=> ($auth->acl_get('f_sigs', $forum_id) && $config['allow_sig'] && $user->data['is_registered']) ? true : false,
		'S_SIGNATURE_CHECKED'			=> ($sig_checked) ? ' checked="checked"' : '',
		'S_LINKS_ALLOWED'					=> $url_status,
		'S_MAGIC_URL_CHECKED'			=> ($urls_checked) ? ' checked="checked"' : '',

		'S_FORM_ENCTYPE'			=> $form_enctype,
	
		'S_BBCODE_IMG'			=> $img_status,
		'S_BBCODE_URL'			=> $url_status,
		'S_BBCODE_FLASH'		=> $flash_status,
		'S_BBCODE_QUOTE'		=> $quote_status,
	
		'S_POST_ACTION'			=> $s_action,
		'S_HIDDEN_FIELDS'		=> $s_hidden_fields)
	);
	if (!function_exists('display_custom_bbcodes'))
	{
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
	}	
	// Build custom bbcodes array
	display_custom_bbcodes();
}

/**
* select_tags_data()
* Creating a list with tags data, what can be used in a message.
*
* @param array $add_tags Tags to add in the message
* @param string $type Type, can be mods or styles.
* @param string $field field to select
* @param bool $return return the data
* @return string bbcode list with that tags.
**/
function select_tags_data($add_tags, $type, $field = 'tag_id', $return = false, $html = false)
{
	global $db;

	if ($field == 'contrib_id')
	{
		$sql = 'SELECT tag_id
			FROM ' . SITE_CONTRIB_TAGS_TABLE . '
			WHERE contrib_id = ' . (int) $add_tags;
		$result = $db->sql_query($sql);

		$add_tags = array();
		while($row = $db->sql_fetchrow($result))
		{
			$add_tags[] = $row['tag_id'];
		}
		$db->sql_freeresult($result);
	}

	// Gather available tags out of the database
	$sql = 'SELECT tag_id, tag_group, tag_name, tag_label
		FROM ' . SITE_TAGS_TABLE . "
		WHERE tag_class IN ('" . $db->sql_escape($type) . "','*')
		AND tag_name != '_'
		AND " . $db->sql_in_set('tag_id', $add_tags, false, true);
	$result = $db->sql_query($sql);

	$tags = array();
	while ($row = $db->sql_fetchrow($result))
	{
		if (!isset($tags[$row['tag_group']]))
		{
			$tags[$row['tag_group']] = array();
		}

		$tags[$row['tag_group']][$row['tag_id']] = $row;
	}
	$db->sql_freeresult($result);

	if ($return == true)
	{
		return $tags;
	}
	if ($html)
	{
        $tags_data = '<ol style="list-style-type: arabic-numbers">';
	}
	else
	{
		$tags_data = '[list=1]';
	}

	unset($tags['author']);

	// Put the remaining tags into a generic dump
	foreach ($tags as $groupname => $group)
	{
        if ($groupname == 'phpbb')
		{
        	$groupname = 'phpBB';
        }
        else
        {
            $groupname = ucfirst($groupname);
		}
	    
	    if ($html)
	    {
            $tags_data .= "<li>{$groupname}<ol style=\"list-style-type: lower-alpha\">";
		}
		else
		{
            $tags_data .= "[*]{$groupname}[list=a]";
		}
		
		foreach ($group as $tag_entry)
		{
			// Dont use append_sid here.
			$url =  'http://www.phpbb.com/' . $type . '/db/index.php?i=browse&mode=group:' . strtolower($groupname) . '&sub=' . $tag_entry['tag_name'];
			
			$tag_entry['tag_label'] = ucfirst($tag_entry['tag_label']);
			
			if ($html)
			{
                $tags_data .= "<li><a href=\"{$url}\">{$tag_entry['tag_label']}</a></li>";
			}
			else
			{
			    $tags_data .= "[*][url={$url}]{$tag_entry['tag_label']}[/url]";
			}
		}
		if ($html)
		{
            $tags_data .= '</ol></li>';
		}
		else
		{
			$tags_data .= '[/list]';
		}
	}
	
	if ($html)
	{
        $tags_data .= '</ol>';
	}
	else
	{
		$tags_data .= '[/list]';
	}

	return $tags_data;
}
?>