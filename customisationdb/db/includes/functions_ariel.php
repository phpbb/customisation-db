<?php
/**
*
* @package ariel
* @version $Id: functions_ariel.php,v 1.72 2008/02/24 16:21:22 paul999 Exp $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

require($root_path . 'includes/functions_display.' . $phpEx);

/**
* @TODO since contrib_queue redundancies have been merged into contrib_author, some of this stuff should likewise be moved back
*/

/**
* Creates a Contrib entry in the database
*
* @param array $data assoc array containing necessary contrib data: name, description, version, revision_name
* @param string $file Name of a $_FILES entry, used in create_revision
* @return mixed if creation succeeds, contains an array of the contribs details, else false
*/
function create_contrib($data, $file)
{
	global $db, $config, $user, $cache;

	$uid = $bitfield = ''; // will be modified by generate_text_for_storage
	$options = 0;
	$allow_bbcode = $allow_urls = $allow_smilies = true;

	generate_text_for_storage($data['description'], $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);

	// It seems that there's no point in even bothering with transactions...
	$contrib_ary = array(
		'contrib_type'						=> $data['type'],
		'contrib_name'						=> $data['name'],
		'contrib_description'			=> $data['description'],
		'contrib_status'					=> CONTRIB_NEW,
		'contrib_version'					=> $data['version'],
		'contrib_revision_name'		=> $data['revision_name'],
		'contrib_filename'				=> $file['name'],
		'contrib_filename_internal'	=> '',
		'contrib_filesize'				=> 0,
		'contrib_md5'							=> '',
		'contrib_phpbb_version'		=> $data['phpbbversion'],
		'user_id'									=> $user->data['user_id'],
		'contrib_downloads'				=> 0,
		'contrib_rating'					=> 0,
		'contrib_rate_count'			=> 0,
	  'contrib_bbcode_uid'      => $uid,
	  'contrib_bbcode_bitfield' => $bitfield,
	  'contrib_bbcode_flags'    => $options,
	  'contrib_style_demo'		=> 0,
	  'contrib_status_update'	=> 0,
		);

	$sql = 'INSERT INTO ' . SITE_CONTRIBS_TABLE . $db->sql_build_array('INSERT', $contrib_ary);
	$db->sql_query($sql);

	$contrib_id = $db->sql_nextid();

	$owner_ary = array(
		'contrib_id'		=> $contrib_id,
		'user_id'				=> $user->data['user_id'],
		'contrib_role'	=> CONTRIB_OWNER,
	);

	$sql = 'INSERT INTO ' . SITE_CONTRIB_USER_TABLE . $db->sql_build_array('INSERT', $owner_ary);
	$db->sql_query($sql);

	// now that we've got a contrib, attempt to create an initial revision entry
	$revision_data = create_revision($contrib_id, $data, $file);

	// failure
	if (!$revision_data)
	{
		$sql = 'DELETE FROM ' . SITE_CONTRIBS_TABLE . ' WHERE contrib_id = ' . $contrib_id;
		$db->sql_query($sql);
		return false;
	}

	$update_ary = array(
		'contrib_filename_internal'		=> $revision_data['filename'],
		'contrib_md5'									=> $revision_data['md5'],
		'contrib_filesize'						=> $revision_data['filesize'],
	);

	$sql = 'UPDATE ' . SITE_CONTRIBS_TABLE . '
		SET ' . $db->sql_build_array('UPDATE', $update_ary) . '
		WHERE contrib_id = ' . $contrib_id;
	$db->sql_query($sql);

	$revision_data['contrib_id'] = $contrib_id;

	$cache->destroy('sql', array(SITE_CONTRIBS_TABLE, SITE_REVISIONS_TABLE, SITE_QUEUE_TABLE, SITE_CONTRIB_TAGS_TABLE));

	return $revision_data;
}

/**
* Creates a Contrib Revision Entry in the database
*
* @param int $contrib_id id of contrib that we are creating a revision of
* @param array $data assoc array containing necessary revision data: version, revision_name
* @param string &$file form name from where the file is loaded from.
* @return mixed if creation succeeds, contains an array of the revisions details, else false
*/
function create_revision($contrib_id, $data, &$file)
{
	global $db, $config, $root_path, $phpEx, $phpbb_root_path;
	global $cache;

	$revision_ary = array(
		'contrib_id'				=> $contrib_id,
		'contrib_type'				=> $data['type'],
		'revision_name'				=> $data['revision_name'],
		'revision_version'			=> $data['version'],
		'revision_date'				=> $data['time'],
		'revision_phpbb_version'	=> $data['phpbbversion'],
		'user_id'					=> $data['user_id'],
		'revision_repackager'		=> isset($data['revision_repackager']) ? $data['revision_repackager'] : ANONYMOUS,
	);

	upload_file($file, $data);
	if (sizeof($file->error))
	{
		global $template;
		$template->assign_var('S_ERROR_STR', implode('<br />', $file->error));

		return false;
	}

	$hash = md5_file($file->destination_path . $file->realname);
	$filesize = @filesize($file->destination_path . $file->realname);

	$revision_ary += array(
		'revision_filename'				=> $data['old_real_name'],
		'revision_filename_internal'	=> $file->realname,
		'revision_md5'					=> $hash,
		'revision_filesize'				=> $filesize,
	);

	$sql = 'INSERT INTO ' . SITE_REVISIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $revision_ary);
	$db->sql_query($sql);
	$revision_id = $db->sql_nextid();

	$revision_data = array(
		'revision_id'	=> $revision_id,
		'filename'		=> $revision_ary['revision_filename_internal'],
		'md5'			=> $revision_ary['revision_md5'],
		'filesize'		=> $revision_ary['revision_filesize'],
	);

	$cache->destroy('sql', array(SITE_CONTRIBS_TABLE, SITE_REVISIONS_TABLE, SITE_QUEUE_TABLE, SITE_CONTRIB_TAGS_TABLE));

	return $revision_data;
}

/**
* Confirms that $contrib_id is a valid contrib of the proper type and is owned by the current user (if applicable)
*
* @return array the contribs database entry
*/
function select_contrib($contrib_id, $contrib_type, $mode, $modes, $user_id = 0)
{
	global $template, $user, $db;

	if (!$contrib_id || !isset($modes[$mode]))
	{
		return false;
	}

	$sql = 'SELECT c.*, u.username, u.user_colour
		FROM ' . SITE_CONTRIBS_TABLE . ' c, ' . USERS_TABLE . ' u
		WHERE c.contrib_type = ' . (int) $contrib_type . '
			AND u.user_id = c.user_id
			AND c.contrib_id = ' . (int) $contrib_id;

	if ($user_id)
	{
		$sql .= ' AND c.user_id = ' . (int) $user_id;
	}
	$result = $db->sql_query($sql);
	$data = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$data)
	{
		return false;
	}

	$template->assign_vars( array(
		'S_CONTRIB'							=> true,
		'CONTRIB_NAME'					=> $data['contrib_name'],
		'CONTRIB_VERSION'				=> $data['contrib_version'],
		'CONTRIB_REVISION_NAME'	=> $data['contrib_revision_name'],
	));

	foreach ($modes as $submode => $null)
	{
		$template->assign_block_vars('contriblinks', array(
			'S_SELECTED'	=> ($mode == $submode),
			'U_TITLE'			=> module_url(array(
									'mode'			=> $submode,
									'contrib_id'	=> $data['contrib_id'],
								)),
			'ICON'				=> true,
			'IMG_PATH'		=> sprintf('contrib_%s_mini.gif', $submode),
			'L_TITLE'			=> $user->lang['CONTRIB_' . strtoupper($submode)],
		));
	}

	return $data;
}

function swap(&$var1, &$var2)
{
	list($var1, $var2) = array($var2, $var1);
}

/**
* Generates a set of "virtual" submodules
*
* @param array &$parent the module to be the submodules' parent
* @param array $row a template of the virtual module to be added
* @param array $data an array containing data pertaining to the differences between the virtual submodules
*/
function generate_virtual_submodules(&$parent, $row, $data)
{
	// some big number
	static $fake_id = 10000;
	global $user, $module;

	$parents = $module->module_cache['parents'][$parent['id']];
	$parents[$parent['id']] = $parent['parent'];

	// Function for building 'url_extra'
	$url_func = '_module_' . $row['module_name'] . '_url';

	// Function for building the language name
	$lang_func = '_module_' . $row['module_name'] . '_lang';

	// Function for building the language name
	$mode_func = '_module_' . $row['module_name'] . '_mode';

	// Custom function for calling parameters on module init (for example assigning template variables)
	$custom_func = '_module_' . $row['module_name'];

	foreach ($data as $entry)
	{
		// get a new fake id
		$fake_id++;

		$module_row = array(
			'depth'		=> $row['module_depth'],

			'id'			=> $fake_id,		// give our selves a fake id
			'parent'	=> (int) $parent['id'],
			'cat'			=> $row['module_cat'],

			'is_duplicate'	=> false,

			'name'		=> (string) $row['module_name'],
			'mode'		=> (string) (function_exists($mode_func)) ? $mode_func($row['module_mode'], $entry) : $row['module_mode'],
			'display'	=> (int) $row['module_display'],

			'url_extra'	=> (function_exists($url_func)) ? $url_func($row['module_mode'], $entry) : '',

			'lang'			=> ($row['module_name'] && function_exists($lang_func)) ? $lang_func($row['module_mode'], $row['module_langname'], $entry) : ((!empty($user->lang[$row['module_langname']])) ? $user->lang[$row['module_langname']] : $row['module_langname']),
			'langname'	=> $row['module_langname'],

			'left'		=> $row['left_id'],
			'right'		=> $row['right_id'],
		);

		// insert us into cache records, just in case
		$module->module_cache['parents'][$fake_id] = $parents;

		if (function_exists($custom_func))
		{
			$custom_func($module_row['mode'], $module_row, $entry);
		}

		// Add the virtual module to the list of "real" modules
		$module->module_ary[] = $module_row;
	}
}

/**
* Prep submodule generation for browse
*
* @TODO Beat Xore with a stick if he ever touches this again
*/
function _module_browse($mode, &$row, $extra = false)
{
	global $module, $db;

	if ($mode == 'groups')
	{
		$sql = 'SELECT *
			FROM ' . SITE_TAGS_TABLE . "
			WHERE tag_class IN ('" . $db->sql_escape($module->p_class) . "', '*')
				AND tag_name = '_'";
		$result = $db->sql_query($sql, 3600);
		$data = array();

		while ($tag_group = $db->sql_fetchrow($result))
		{
			$data[] = $tag_group;
		}

		$data[] = array('tag_group' => 'stats', 'tag_label' => 'Queue stats');

		$db->sql_freeresult($result);

		$row['display'] = false;
		$row['depth'] -= 1;
		$module->module_ary[] = $row;

		$module_row = array(
			'module_depth'		=> $row['depth'] + 1,
			'module_name'			=> $row['name'],
			'module_mode'			=> 'group',
			'module_cat'			=> false,
			'module_display'	=> true,
			'module_langname'	=> '',
			'left_id'					=> $row['right'],
			'right_id'				=> $row['right'],
		);

		generate_virtual_submodules($row, $module_row, $data);
	}
}

/**
* browse submodule mode tweaking
*
* @TODO Beat Xore with a stick if he ever touches this again
*/
function _module_browse_mode($mode, $extra = false)
{
	global $module;

	// strlen("group:") == 6
	if ($mode == 'group' && is_array($extra))
	{
		return 'group:' . urlencode($extra['tag_group']);
	}

	return $mode;
}

/**
* browse submodule lang/link titles
*
* @TODO Beat Xore with a stick if he ever touches this again
*/
function _module_browse_lang($mode, $lang, $extra = false)
{
	global $module, $user;

	// strlen("group:") == 6
	if ($mode == 'group' && is_array($extra))
	{
		return sprintf($extra['tag_label'], $user->lang[strtoupper($module->p_class)]);
	}
	return (!empty($user->lang[$lang])) ? $user->lang[$lang] : $lang;
}


/**
* Pack module get variables into the url
*
* @param $args array associative array. keys and values should be pre-sanitized.
*/
function module_url($args = false)
{
	global $user, $module, $phpbb_root_path;

	$base = $phpbb_root_path . (($user->page['page_dir']) ? $user->page['page_dir'] . '/' : '') . $user->page['page_name'];

	$vars = array(
		'i'			=> $module->p_name,
		'mode'	=> $module->p_mode,
	);

	if (is_array($args))
	{
		$vars = array_merge($vars, $args);
	}

	$query_args = array();
	foreach ($vars as $key => $value)
	{
		if (is_array($value))
		{
			continue;
		}
		$query_args[] = urlencode($key) . '=' . urlencode($value);
	}

	return append_sid($base, implode('&amp;', $query_args));
}

/**
* builds a full url including http://server.tld/
*
* @param array $variables All variables to include in the url
*
*/
function build_full_url($variables = array())
{
	global $user;

	$url = generate_board_url(true) . '/' . (($user->page['page_dir']) ? str_replace('../', '', $user->page['page_dir']) . '/' : '') . $user->page['page_name'];

	$vars = array();
	foreach ($variables as $key => $value)
	{
		if (is_array($value))
		{
			continue;
		}
		$vars[] = urlencode($key) . '=' . urlencode($value);
	}

	if (!empty($vars))
	{
		$url .= '?' . implode('&amp;', $vars);
	}

	return append_sid($url);
}


/**
* Get mimetype. Utilize mime_content_type if the function exist.
* Copied from includes/functions_upload.php, used in download function.
*/
function get_mimetype($filename)
{
	$mimetype = '';

	if (function_exists('mime_content_type'))
	{
		$mimetype = mime_content_type($filename);
	}

	// Some browsers choke on a mimetype of application/octet-stream
	if (!$mimetype || $mimetype == 'application/octet-stream')
	{
		$mimetype = 'application/octetstream';
	}

	return $mimetype;
}

function upload_file(&$file, &$data)
{
	global $config, $phpbb_root_path, $phpEx;
	if (!class_exists('fileupload'))
	{
		require_once($phpbb_root_path . 'includes/functions_upload.' . $phpEx);
  }

  $upload = new fileupload('ARIEL_', array('mod', 'zip'));

  $file = $upload->form_upload($file);

	$data['old_real_name'] = $file->realname;

	//Directory isnt in community root!
	$uploadfile = '../' . $config['site_upload_dir'] . '/';

	$file->clean_filename('unique_ext');

	return $file->move_file($uploadfile);
}

/**
* Strips all bbcode from a text and returns the plain content
*/
function strip_bbcode_no_uid(&$text)
{
	$text = preg_replace("#\[\/?[a-z0-9\*\+\-]+(?:=.*?)?(?::[a-z])?\]#", ' ', $text);

	$match = get_preg_expression('bbcode_htm');
	$replace = array('\1', '\1', '\2', '\1', '', '');

	$text = preg_replace($match, $replace, $text);
}

/**
* Get file extension
*/
function get_extension($filename)
{
	if (strpos($filename, '.') === false)
	{
		return '';
	}

	$filename = explode('.', $filename);
	return array_pop($filename);
}

function remove_tags($contrib_id, $tags)
{
	global $db;

	$sql =  'DELETE FROM ' . SITE_CONTRIB_TAGS_TABLE .  " WHERE contrib_id = $contrib_id AND " . $db->sql_in_set('tag_id', $tags);
	$db->sql_query($sql);
}

function del_folder($path)
{
	if (is_dir($path))
	{
		$entries = scandir($path);
		foreach ($entries as $entry)
		{
			if ($entry != '.' && $entry != '..')
			{
				del_folder($path . '/' . $entry);
			}
		}

		rmdir($path);
	}
	else
	{
		unlink($path);
	}
}

/**
* Install a style
* mostly taken from acp_styles, this function also utilises it
*
* @param array $error Reference that will contain errors
* @param string $install_path The name of the style
* @param bool $store_db Store style in the database
* @param bool $style_active Make style active
* @param bool $style_default Make style default
* @return mixed style_id
*/
function install_style(&$error, $install_path, $store_db = false, $style_active = true, $style_default = false)
{
	global $user, $cache;
	global $phpbb_root_path, $phpEx;
	global $board_root_path;

	if (!isset($board_root_path))
	{
		$board_root_path = $phpbb_root_path;
	}

	if (!class_exists('acp_styles'))
	{
		include($phpbb_root_path . 'includes/acp/acp_styles.' . $phpEx);
	}

	// acp_styles::main()

	if (!defined('TEMPLATE_BITFIELD'))
	{
		// Hardcoded template bitfield to add for new templates
		$bitfield = new bitfield();
		$bitfield->set(0);
		$bitfield->set(3);
		$bitfield->set(8);
		$bitfield->set(9);
		$bitfield->set(11);
		$bitfield->set(12);
		define('TEMPLATE_BITFIELD', $bitfield->get_base64());
		unset($bitfield);
	}

	$install_style = new acp_styles();
	$install_style->imageset_keys = array(
		'logos' => array(
			'site_logo',
		),
		'buttons'	=> array(
			'icon_back_top', 'icon_contact_aim', 'icon_contact_email', 'icon_contact_icq', 'icon_contact_jabber', 'icon_contact_msnm', 'icon_contact_pm', 'icon_contact_yahoo', 'icon_contact_www', 'icon_post_delete', 'icon_post_edit', 'icon_post_info', 'icon_post_quote', 'icon_post_report', 'icon_user_online', 'icon_user_offline', 'icon_user_profile', 'icon_user_search', 'icon_user_warn', 'button_pm_forward', 'button_pm_new', 'button_pm_reply', 'button_topic_locked', 'button_topic_new', 'button_topic_reply',
		),
		'icons'		=> array(
			'icon_post_target', 'icon_post_target_unread', 'icon_topic_attach', 'icon_topic_latest', 'icon_topic_newest', 'icon_topic_reported', 'icon_topic_unapproved', 'icon_friend', 'icon_foe',
		),
		'forums'	=> array(
			'forum_link', 'forum_read', 'forum_read_locked', 'forum_read_subforum', 'forum_unread', 'forum_unread_locked', 'forum_unread_subforum', 'subforum_read', 'subforum_unread'
		),
		'folders'	=> array(
			'topic_moved', 'topic_read', 'topic_read_mine', 'topic_read_hot', 'topic_read_hot_mine', 'topic_read_locked', 'topic_read_locked_mine', 'topic_unread', 'topic_unread_mine', 'topic_unread_hot', 'topic_unread_hot_mine', 'topic_unread_locked', 'topic_unread_locked_mine', 'sticky_read', 'sticky_read_mine', 'sticky_read_locked', 'sticky_read_locked_mine', 'sticky_unread', 'sticky_unread_mine', 'sticky_unread_locked', 'sticky_unread_locked_mine', 'announce_read', 'announce_read_mine', 'announce_read_locked', 'announce_read_locked_mine', 'announce_unread', 'announce_unread_mine', 'announce_unread_locked', 'announce_unread_locked_mine', 'global_read', 'global_read_mine', 'global_read_locked', 'global_read_locked_mine', 'global_unread', 'global_unread_mine', 'global_unread_locked', 'global_unread_locked_mine', 'pm_read', 'pm_unread',
		),
		'polls'		=> array(
			'poll_left', 'poll_center', 'poll_right',
		),
		'ui'		=> array(
			'upload_bar',
		),
		'user'		=> array(
			'user_icon1', 'user_icon2', 'user_icon3', 'user_icon4', 'user_icon5', 'user_icon6', 'user_icon7', 'user_icon8', 'user_icon9', 'user_icon10',
		),
	);

	$user->add_lang('acp/styles');

	// acp_styles::install()

	$error = $installcfg = $style_row = array();
	$root_path = $cfg_file = '';
	static $element_ary = array('template' => STYLES_TEMPLATE_TABLE, 'theme' => STYLES_THEME_TABLE, 'imageset' => STYLES_IMAGESET_TABLE);

	// Installing, obtain cfg file contents
	if ($install_path)
	{
		$root_path = $board_root_path . 'styles/' . $install_path . '/';
		$cfg_file = $root_path . 'style.cfg';

		if (!file_exists($cfg_file))
		{
			$error[] = $user->lang['STYLE_ERR_NOT_STYLE'];
		}
		else
		{
			$installcfg = parse_cfg_file($cfg_file);
		}
	}

	// Installing
	if (sizeof($installcfg))
	{
		global $custom_installcfg;
		if (isset($custom_installcfg))
		{
			$installcfg = array_merge($installcfg, $custom_installcfg);
		}

		$name		= $installcfg['name'];
		$copyright	= $installcfg['copyright'];
		$version	= $installcfg['version'];

		$style_row = array(
			'style_id'			=> 0,
			'style_name'		=> $installcfg['name'],
			'style_copyright'	=> $installcfg['copyright']
		);

		$reqd_template = (isset($installcfg['required_template'])) ? $installcfg['required_template'] : false;
		$reqd_theme = (isset($installcfg['required_theme'])) ? $installcfg['required_theme'] : false;
		$reqd_imageset = (isset($installcfg['required_imageset'])) ? $installcfg['required_imageset'] : false;

		// Check to see if each element is already installed, if it is grab the id
		foreach ($element_ary as $element => $table)
		{
			$style_row = array_merge($style_row, array(
				$element . '_id'			=> 0,
				$element . '_name'			=> '',
				$element . '_copyright'		=> '')
			);

 			$install_style->test_installed($element, $error, (${'reqd_' . $element}) ? $board_root_path . 'styles/' . $reqd_template . '/' : $root_path, ${'reqd_' . $element}, $style_row[$element . '_id'], $style_row[$element . '_name'], $style_row[$element . '_copyright']);

			if (!$style_row[$element . '_name'])
			{
				$style_row[$element . '_name'] = $reqd_template;
			}
		}
	}

	$style_row['store_db']		= (int) $store_db;
	$style_row['style_active']	= (int) $style_active;
	$style_row['style_default']	= (int) $style_default;

	// User has submitted form and no errors have occurred
	if (!sizeof($error))
	{
		foreach ($element_ary as $element => $table)
		{
			${$element . '_root_path'} = (${'reqd_' . $element}) ? $phpbb_root_path . 'styles/' . ${'reqd_' . $element} . '/' : false;
			${$element . '_path'} = (${'reqd_' . $element}) ? ${'reqd_' . $element} : false;
		}
		$install_style->install_style($error, 'install', $root_path, $style_row['style_id'], $style_row['style_name'], $install_path, $style_row['style_copyright'], $style_row['style_active'], $style_row['style_default'], $style_row, $template_root_path, $template_path, $theme_root_path, $theme_path, $imageset_root_path, $imageset_path);

		if (!sizeof($error))
		{
			$cache->destroy('sql', STYLES_TABLE);

			// installed
			return (int) $style_row['style_id'];
		}
	}

	return false;
}

/**
 * Uninstalls a style
 * this function is slightly inspired by acp_styles::remove()
 *
 * @param string $install_path
 * @param int $style_id
 * @param int $new_id
 * @return bool success
 */
function uninstall_style($style_id, $new_id)
{
	global $db, $cache;

	$style_id	= (int) $style_id;
	$new_id		= (int) $new_id;

	$sql = 'SELECT template_id, theme_id, imageset_id
		FROM ' . STYLES_TABLE . '
			WHERE style_id = ' . $style_id;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$row)
	{
		return false;
	}

	$sql_ary = array();
	$sql_ary[] = 'DELETE FROM ' . STYLES_TEMPLATE_TABLE . ' WHERE template_id = ' . (int) $row['template_id'];
	$sql_ary[] = 'DELETE FROM ' . STYLES_THEME_TABLE . ' WHERE theme_id = ' . (int) $row['theme_id'];
	$sql_ary[] = 'DELETE FROM ' . STYLES_IMAGESET_TABLE . ' WHERE imageset_id = ' . (int) $row['imageset_id'];
	$sql_ary[] = 'DELETE FROM ' . STYLES_TABLE . ' WHERE style_id = ' . $style_id;
	$sql_ary[] = 'UPDATE ' . USERS_TABLE . " SET user_style = $new_id WHERE user_style = $style_id";
	$sql_ary[] = 'UPDATE ' . FORUMS_TABLE . " SET forum_style = $new_id WHERE forum_style = $style_id";

	foreach ($sql_ary as $sql)
	{
		$db->sql_query($sql);
	}

	if ($style_id == $config['default_style'])
	{
		set_config('default_style', $new_id);
	}

	$cache->destroy('sql', STYLES_TABLE);

	return true;
}

/**
 * Refresh a style
 *
 * @param int $style_id
 */
function refresh_style($style_id)
{
	/**
	 * @todo db template refreshing
	 */

	global $phpbb_root_path, $phpEx;
	global $db, $cache;

	$style_id = (int) $style_id;

	if (!class_exists('acp_styles'))
	{
		include($phpbb_root_path . 'includes/acp/acp_styles.' . $phpEx);
	}

	$acp_styles = &new acp_styles();

	$acp_styles->imageset_keys = array(
		'logos' => array(
			'site_logo',
		),
		'buttons'	=> array(
			'icon_back_top', 'icon_contact_aim', 'icon_contact_email', 'icon_contact_icq', 'icon_contact_jabber', 'icon_contact_msnm', 'icon_contact_pm', 'icon_contact_yahoo', 'icon_contact_www', 'icon_post_delete', 'icon_post_edit', 'icon_post_info', 'icon_post_quote', 'icon_post_report', 'icon_user_online', 'icon_user_offline', 'icon_user_profile', 'icon_user_search', 'icon_user_warn', 'button_pm_forward', 'button_pm_new', 'button_pm_reply', 'button_topic_locked', 'button_topic_new', 'button_topic_reply',
		),
		'icons'		=> array(
			'icon_post_target', 'icon_post_target_unread', 'icon_topic_attach', 'icon_topic_latest', 'icon_topic_newest', 'icon_topic_reported', 'icon_topic_unapproved', 'icon_friend', 'icon_foe',
		),
		'forums'	=> array(
			'forum_link', 'forum_read', 'forum_read_locked', 'forum_read_subforum', 'forum_unread', 'forum_unread_locked', 'forum_unread_subforum', 'subforum_read', 'subforum_unread'
		),
		'folders'	=> array(
			'topic_moved', 'topic_read', 'topic_read_mine', 'topic_read_hot', 'topic_read_hot_mine', 'topic_read_locked', 'topic_read_locked_mine', 'topic_unread', 'topic_unread_mine', 'topic_unread_hot', 'topic_unread_hot_mine', 'topic_unread_locked', 'topic_unread_locked_mine', 'sticky_read', 'sticky_read_mine', 'sticky_read_locked', 'sticky_read_locked_mine', 'sticky_unread', 'sticky_unread_mine', 'sticky_unread_locked', 'sticky_unread_locked_mine', 'announce_read', 'announce_read_mine', 'announce_read_locked', 'announce_read_locked_mine', 'announce_unread', 'announce_unread_mine', 'announce_unread_locked', 'announce_unread_locked_mine', 'global_read', 'global_read_mine', 'global_read_locked', 'global_read_locked_mine', 'global_unread', 'global_unread_mine', 'global_unread_locked', 'global_unread_locked_mine', 'pm_read', 'pm_unread',
		),
		'polls'		=> array(
			'poll_left', 'poll_center', 'poll_right',
		),
		'ui'		=> array(
			'upload_bar',
		),
		'user'		=> array(
			'user_icon1', 'user_icon2', 'user_icon3', 'user_icon4', 'user_icon5', 'user_icon6', 'user_icon7', 'user_icon8', 'user_icon9', 'user_icon10',
		),
	);

	// take care of the template
	$sql = 'SELECT *
		FROM ' . STYLES_TEMPLATE_TABLE . "
		WHERE template_id = $style_id";
	$result = $db->sql_query($sql);
	$template_row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if ($template_row)
	{
		$acp_styles->clear_template_cache($template_row);
	}

	// refresh theme
	$sql = 'SELECT *
		FROM ' . STYLES_THEME_TABLE . "
		WHERE theme_id = $style_id";
	$result = $db->sql_query($sql);
	$theme_row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if ($theme_row && $theme_row['theme_storedb'] && file_exists("{$phpbb_root_path}styles/{$theme_row['theme_path']}/theme/stylesheet.css"))
	{
		// Save CSS contents
		$sql_ary = array(
			'theme_mtime'	=> (int) filemtime("{$phpbb_root_path}styles/{$theme_row['theme_path']}/theme/stylesheet.css"),
			'theme_data'	=> $acp_styles->db_theme_data($theme_row),
		);

		$sql = 'UPDATE ' . STYLES_THEME_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
			WHERE theme_id = $style_id";
		$db->sql_query($sql);

		$cache->destroy('sql', STYLES_THEME_TABLE);
	}

	// and now the imageset
	$sql = 'SELECT *
		FROM ' . STYLES_IMAGESET_TABLE . "
		WHERE imageset_id = $style_id";
	$result = $db->sql_query($sql);
	$imageset_row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if ($imageset_row)
	{
		$sql_ary = array();

		$imageset_definitions = array();
		foreach ($acp_styles->imageset_keys as $topic => $key_array)
		{
			$imageset_definitions = array_merge($imageset_definitions, $key_array);
		}

		$cfg_data_imageset = parse_cfg_file("{$phpbb_root_path}styles/{$imageset_row['imageset_path']}/imageset/imageset.cfg");

		$db->sql_transaction('begin');

		$sql = 'DELETE FROM ' . STYLES_IMAGESET_DATA_TABLE . '
			WHERE imageset_id = ' . $style_id;
		$result = $db->sql_query($sql);

		foreach ($cfg_data_imageset as $image_name => $value)
		{
			if (strpos($value, '*') !== false)
			{
				if (substr($value, -1, 1) === '*')
				{
					list($image_filename, $image_height) = explode('*', $value);
					$image_width = 0;
				}
				else
				{
					list($image_filename, $image_height, $image_width) = explode('*', $value);
				}
			}
			else
			{
				$image_filename = $value;
				$image_height = $image_width = 0;
			}

			if (strpos($image_name, 'img_') === 0 && $image_filename)
			{
				$image_name = substr($image_name, 4);
				if (in_array($image_name, $imageset_definitions))
				{
					$sql_ary[] = array(
						'image_name'		=> (string) $image_name,
						'image_filename'	=> (string) $image_filename,
						'image_height'		=> (int) $image_height,
						'image_width'		=> (int) $image_width,
						'imageset_id'		=> (int) $style_id,
						'image_lang'		=> '',
					);
				}
			}
		}

		$sql = 'SELECT lang_dir
			FROM ' . LANG_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if (@file_exists("{$phpbb_root_path}styles/{$imageset_row['imageset_path']}/imageset/{$row['lang_dir']}/imageset.cfg"))
			{
				$cfg_data_imageset_data = parse_cfg_file("{$phpbb_root_path}styles/{$imageset_row['imageset_path']}/imageset/{$row['lang_dir']}/imageset.cfg");
				foreach ($cfg_data_imageset_data as $image_name => $value)
				{
					if (strpos($value, '*') !== false)
					{
						if (substr($value, -1, 1) === '*')
						{
							list($image_filename, $image_height) = explode('*', $value);
							$image_width = 0;
						}
						else
						{
							list($image_filename, $image_height, $image_width) = explode('*', $value);
						}
					}
					else
					{
						$image_filename = $value;
						$image_height = $image_width = 0;
					}

					if (strpos($image_name, 'img_') === 0 && $image_filename)
					{
						$image_name = substr($image_name, 4);
						if (in_array($image_name, $imageset_definitions))
						{
							$sql_ary[] = array(
								'image_name'		=> (string) $image_name,
								'image_filename'	=> (string) $image_filename,
								'image_height'		=> (int) $image_height,
								'image_width'		=> (int) $image_width,
								'imageset_id'		=> (int) $style_id,
								'image_lang'		=> (string) $row['lang_dir'],
							);
						}
					}
				}
			}
		}
		$db->sql_freeresult($result);

		$db->sql_multi_insert(STYLES_IMAGESET_DATA_TABLE, $sql_ary);

		$db->sql_transaction('commit');

		$cache->destroy('sql', STYLES_IMAGESET_DATA_TABLE);
	}
}

/**
* Install a phpbb2 style, this requires a phpbb2 installation on the same db
*
* @param string $install_path The style name
* @return mixed style_id
*/
function install_style_phpbb2($install_path, $style_name = '')
{
	global $db;
	global $phpbb2_root_path, $phpbb2_table_prefix;

	include($phpbb2_root_path . 'templates/' . basename($install_path) . '/theme_info.cfg');

	$template_name = &$$install_path;

	// install specific style?
	if (!empty($style_name))
	{
		for ($i = 0; $i < sizeof($template_name); $i++)
		{
			if ($template_name[$i]['style_name'] == $style_name)
			{
				$style_key = $i;
				break;
			}
		}
	}

	// if specific style not suplied or found, reset to 0
	if (!isset($style_key))
	{
		$style_key = 0;
	}

	// make sql array
	$sql_ary = array();

	foreach ($template_name[$style_key] as $key => $val)
	{
		$sql_ary[$key] = $val;
	}

	$sql = 'INSERT INTO ' . "{$phpbb2_table_prefix}themes" . ' ' . $db->sql_build_array('INSERT', $sql_ary);
	$db->sql_query($sql);

	return $db->sql_nextid();
}

/**
* Refresh a phpbb2 style
*
* @param string $install_path The style name
* @return mixed style_id
*/
function refresh_style_phpbb2($style_id, $install_path, $style_name = '')
{
	global $db;
	global $phpbb2_root_path, $phpbb2_table_prefix;

	$style_id = (int) $style_id;

	$sql = "SELECT themes_id
		FROM {$phpbb2_table_prefix}themes
			WHERE themes_id = $style_id";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$row)
	{
		return false;
	}

	include($phpbb2_root_path . 'templates/' . basename($install_path) . '/theme_info.cfg');

	$template_name = &$$install_path;

	// install specific style?
	if (!empty($style_name))
	{
		for ($i = 0; $i < sizeof($template_name); $i++)
		{
			if ($template_name[$i]['style_name'] == $style_name)
			{
				$style_key = $i;
				break;
			}
		}
	}

	// if specific style not suplied or found, reset to 0
	if (!isset($style_key))
	{
		$style_key = 0;
	}

	// make sql array
	$sql_ary = array();

	foreach ($template_name[$style_key] as $key => $val)
	{
		$sql_ary[$key] = $val;
	}

	$sql = 'UPDATE ' . "{$phpbb2_table_prefix}themes" . ' SET ' . $db->sql_build_array('INSERT', $sql_ary) . "WHERE themes_id = $style_id";
	$db->sql_query($sql);

	return $db->sql_nextid();
}

/**
 * phpbb2 version of uninstall_style
 *
 * @param int $style_id
 * @param int $new_id
 * @return bool success
 */
function uninstall_style_phpbb2($style_id, $new_id)
{
	global $db;
	global $phpbb2_root_path, $phpbb2_table_prefix;

	$style_id	= (int) $style_id;
	$new_id		= (int) $new_id;

	$sql = "SELECT themes_id
		FROM {$phpbb2_table_prefix}themes
			WHERE themes_id = $style_id";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$row)
	{
		return false;
	}

	$sql_ary = array();
	$sql_ary[] = "DELETE FROM {$phpbb2_table_prefix}themes WHERE themes_id = $style_id";
	$sql_ary[] = "UPDATE {$phpbb2_table_prefix}users SET themes_id = $new_id WHERE themes_id = $style_id";
	$sql_ary[] = "UPDATE {$phpbb2_table_prefix}config SET config_value = $new_id WHERE config_name = 'default_style' AND config_value = '$style_id'";

	foreach ($sql_ary as $sql)
	{
		$db->sql_query($sql);
	}

	return true;
}

?>