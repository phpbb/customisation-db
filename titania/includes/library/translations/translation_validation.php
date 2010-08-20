<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2010 phpBB Customisation Database Team
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

/**
 * Description of translation_validation
 *
 * @author Vojtěch Vondra
 * @package Titania
 */
class translation_validation extends titania_contrib_tools
{

  const	NOT_REQUIRED = 0;
  const REQUIRED = 1;
  const REQUIRED_EMPTY = 2;
  const REQUIRED_DEFAULT = 3;

  public function __construct($original_zip, $new_dir_name)
  {
	parent::__construct($original_zip, $new_dir_name);
  }

  /**
   * Checks the file for the array contents
   * Make sure it has all the keys present in the newest version
   */
  public function check_package()
  {
	// Basically the individual parts of the translation, we check them separately, because they have colliding filenames
	$types = array(
	  'language' => 'language/',
	  'prosilver' => 'styles/prosilver/imageset/',
	  'subsilver2' => 'styles/subsilver2/imageset/',
	);

	// Do the check for all types
	foreach ($types as $type => $path)
	{
	  // Get all the files present in the uploaded package for the currently iterated type
	  $filelist = $this->lang_filelist($this->unzip_dir, $path);
	  ksort($filelist);
	  $lang_root_path = key($filelist);

	  // Strip the path from the filenames
	  $filelist_tmp = array();
	  foreach ($filelist as $path => $file_ary)
	  {
		  foreach ($file_ary as $file)
		  {
			  $filelist_tmp[] = str_replace($lang_root_path, '', $path) . $file;
		  }
	  }
	  
	  $filelist = $filelist_tmp;
	  unset($filelist_tmp);

	  // Check if even one match is obtained...
	  $match = false;
	  $type_list = $this->get_file_list($type);
	  $original_language_directory = realpath(dirname(__FILE__) . '/../languages/en') . '/';

	  foreach ($type_list as $file => $status)
	  {
		  if (in_array($file, $filelist))
		  {
			  $match = true;
			  break;
		  }
	  }

	  // No files from the list were found, so it's probably a bad package
	  if (!$match)
	  {
		$error[] = phpbb::$user->lang['NO_TRANSLATION'];
		return $error;
	  }

	  $lang_root_path = (substr($lang_root_path, -1) != '/') ? $lang_root_path . '/' : $lang_root_path;
	  $lang_root_path = (substr($lang_root_path, 0, 1) == '/') ? substr($lang_root_path, 1) : $lang_root_path;

	  // Build new filelist by including/excluding and also adding data to it.
	  $new_filelist = array();
	  foreach ($type_list as $file => $status_array)
	  {
		  $status = (!is_array($status_array)) ? $status_array : $status_array['status'];

		  // If it does not exist, we error out...
		  if ($status == self::REQUIRED && !in_array($file, $filelist))
		  {
			  $error[] = 'Required file <em>' . $file . '</em> is missing from the package.';
			  continue;
		  }

		  // Include data
		  switch ($status)
		  {
			  case self::REQUIRED:
				  $new_filelist[$file] = file_get_contents($tmp_path . $lang_root_path . $file);
			  break;

			  case self::NOT_REQUIRED:
				  if (in_array($file, $filelist))
				  {
					  $new_filelist[$file] = file_get_contents($tmp_path . $lang_root_path . $file);
				  }
			  break;

			  case self::REQUIRED_EMPTY:
				  if (in_array($file, $filelist))
				  {
					  $new_filelist[$file] = file_get_contents($tmp_path . $lang_root_path . $file);
				  }
				  else
				  {
					  $new_filelist[$file] = $status_array['data'];
				  }
			  break;

			  case self::REQUIRED_DEFAULT:
				  if (!in_array($file, $filelist))
				  {
					  $new_filelist[$file] = file_get_contents($original_language_directory . $file);
				  }
				  else
				  {
					  $new_filelist[$file] = file_get_contents($tmp_path . $lang_root_path . $file);
				  }
			  break;
		  }
	  }

	  // Check for the license file
	  if ($type == 'language')
	  {
		  $error = $this->check_license_files($tmp_path, $lang_root_path, $new_filelist);
	  }

	  if (sizeof($error))
	  {
		  return $error;
	  }

	  // Now check if there are any mod files we add...
	  if ($type == 'language')
	  {
		  foreach ($filelist as $file)
		  {
			  if (preg_match('/\.(php|txt)$/', $file))
			  {
				  $new_filelist[$file] = file_get_contents($tmp_path . $lang_root_path . $file);
			  }
		  }
	  }
	  unset($filelist);

	  $missing_keys = array();

	  // Now check for missing keys...
	  if ($type == 'language')
	  {
		  foreach ($new_filelist as $file => $contents)
		  {
			  if (!file_exists($original_language_directory . $file))
			  {
				  continue;
			  }

			  if (strpos($file, '.php') === false)
			  {
				  continue;
			  }

			  // Check original file by including the language entries...
			  $lang = array();
			  include($original_language_directory . $file);
			  $lang_keys = $this->multiarray_keys($lang);

			  // General cleanup
			  $file_data = trim(str_replace(array("\r", "\t", ' '), '', $contents)); // Remove whitespace so we can use strpos to find the key presence
			  $file_data = explode("\n", $file_data);

			  // Unfortunately getting this one line requires the whole file to be iterated through
			  // It could be replaced by a preg_match with multiline mode switched on.
			  $translation_line = false;
			  foreach ($file_data as $line)
			  {
				  if (strpos($line, 'TRANSLATION_INFO') !== false)
				  {
					  $translation_line = $line;
					  break;
				  }
			  }

			  $file_data = str_replace("\n", '', implode("\n", $file_data));

			  // Now we have all array keys... let us have a look within $contents if all keys are there...
			  // This can take a bit because we check every key... luckily with strpos...
			  foreach ($lang_keys as $current_key)
			  {
				  if (strpos($file_data, "'{$current_key}'=>") === false)
				  {
					  if (empty($lang[$current_key]) || is_array($lang[$current_key]))
					  {
						  continue;
					  }

					  $missing_keys[$file][] = $current_key;
				  }
			  }

			  // Check TRANSLATION variable
			  if ($translation_line && substr_count($translation_line, '</a>') > 2)
			  {
				  $error[] = sprintf(phpbb::$user->lang['TOO_MANY_TRANSLATOR_LINKS'], substr_count($translation_line, '</a>'));
			  }
		  }
	  }

	  if (sizeof($missing_keys))
	  {
		foreach ($missing_keys as $file => $keys)
		{
			$error[] = sprintf(phpbb::$user->lang['MISSING_KEYS'], $file, implode('<br />', $keys));
		}
	  }

	  return $error;
	}

  }

  /**
   * Basically flattens the files from all subdirectories of $root_dir into an array
   * 
   * @param string $root_dir
   * @param string $dir
   * @return array
   */
  private function lang_filelist($root_dir, $dir = '')
  {
	  clearstatcache();
	  $matches = array();

	  // Add closing / if present
	  $root_dir = ($root_dir && substr($root_dir, -1) != '/') ? $root_dir . '/' : $root_dir;

	  // Remove initial / if present
	  $dir = (substr($dir, 0, 1) == '/') ? substr($dir, 1) : $dir;
	  // Add closing / if present
	  $dir = ($dir && substr($dir, -1) != '/') ? $dir . '/' : $dir;

	  $dp = opendir($root_dir . $dir);
	  while (($fname = readdir($dp)))
	  {
		  if (is_file("$root_dir$dir$fname"))
		  {
			  $matches[$dir][] = $fname;
		  }
		  else if ($fname[0] != '.' && is_dir("$root_dir$dir$fname"))
		  {
			  $matches += lang_filelist($root_dir, "$dir$fname");
		  }
	  }
	  closedir($dp);

	  return $matches;
  }

  private function check_license_files($tmp_path, $lang_root_path, $filelist)
  {
	$error = array();
	$license_file	= '';
	$license_files	= array('LICENSE', 'COPYING', 'docs/LICENSE', 'docs/COPYING');

	foreach ($license_files as $file)
	{
		if (in_array($file, $filelist))
		{
			$license_file = $file;
			break;
		}
	}

	// Missing license
	if (empty($license_file))
	{
		$error_msg = 'The LICENSE or COPYING file is missing from your submission. Please ensure that you have a copy of the <a href="/community/docs/COPYING">COPYING file</a> in your language pack to indicate that it is released under the GNU General Public License, version 2. This file may be placed in one of the following locations: ';
		foreach ($license_files as $file)
		{
			$error_msg .= '/' . $file . ', ';
		}

		$error[] = substr($error_msg, 0, -2) . '.';
	}

	// Check the contents of the license file to ensure that it's a GNU v2
	else
	{
		$filelist[$license_file] = file_get_contents($tmp_path . $lang_root_path . $license_file);
		if (strpos($filelist[$license_file], 'GNU GENERAL PUBLIC LICENSE') === false || strpos($filelist[$license_file], 'Version 2, June 1991') === false)
		{
			$error[] = 'The license file in your package (/' . $license_file . ') should be a GNU General Public License, version 2.';
		}

		unset($license_data);
	}

	return $error;
  }

  /**
   * array_keys for a multidimensional array
   * 
   * @param array $array
   * @return array
   */
  private function multiarray_keys($array)
  {
	$keys = array();

	foreach($array as $k => $v)
	{
	  $keys[] = $k;
	  if (is_array($array[$k]))
	  {
		$keys = array_merge($keys, multiarray_keys($array[$k]));
	  }
	}
	return $keys;
  }

  /**
   * Returns an array of files with their requirements in the package
   *
   * @param string $type One of language, prosilver, subsilver2
   * @return array
   */
  public function get_file_list($type)
  {
	switch ($type)
	{
	  // Language files
	  case 'language':

		return  array(
		  'README'			=> self::NOT_REQUIRED,
		  'docs/README'		=> self::NOT_REQUIRED,
		  'captcha_qa.php'	=> self::REQUIRED,
		  'captcha_recaptcha.php' => self::REQUIRED,
		  'common.php'		=> self::REQUIRED,
		  'groups.php'		=> self::REQUIRED,
		  'help_bbcode.php'	=> self::REQUIRED,
		  'help_faq.php'		=> self::REQUIRED,
		  'index.htm'			=> array('status' => self::REQUIRED_EMPTY, 'data' => $html_body),
		  'install.php'		=> self::REQUIRED,
		  'iso.txt'			=> self::NOT_REQUIRED,
		  'mcp.php'			=> self::REQUIRED,
		  'memberlist.php'	=> self::REQUIRED,
		  'posting.php'		=> self::REQUIRED,
		  'search.php'		=> self::REQUIRED,
		  'search_ignore_words.php'	=> self::NOT_REQUIRED,
		  'search_synonyms.php'		=> self::NOT_REQUIRED,
		  'ucp.php'			=> self::REQUIRED,
		  'viewforum.php'		=> self::REQUIRED,
		  'viewtopic.php'		=> self::REQUIRED,

		  'mods/index.htm'		=> array('status' => self::REQUIRED_EMPTY, 'data' => $html_body),

		  'acp/index.htm'			=> array('status' => self::REQUIRED_EMPTY, 'data' => $html_body),
		  'acp/attachments.php'	=> self::REQUIRED_DEFAULT,
		  'acp/ban.php'			=> self::REQUIRED_DEFAULT,
		  'acp/board.php'			=> self::REQUIRED_DEFAULT,
		  'acp/bots.php'			=> self::REQUIRED_DEFAULT,
		  'acp/common.php'		=> self::REQUIRED_DEFAULT,
		  'acp/database.php'		=> self::REQUIRED_DEFAULT,
		  'acp/email.php'			=> self::REQUIRED_DEFAULT,
		  'acp/forums.php'		=> self::REQUIRED_DEFAULT,
		  'acp/groups.php'		=> self::REQUIRED_DEFAULT,
		  'acp/language.php'		=> self::REQUIRED_DEFAULT,
		  'acp/modules.php'		=> self::REQUIRED_DEFAULT,
		  'acp/permissions.php'	=> self::REQUIRED_DEFAULT,
		  'acp/permissions_phpbb.php'		=> self::REQUIRED_DEFAULT,
		  'acp/posting.php'		=> self::REQUIRED_DEFAULT,
		  'acp/profile.php'		=> self::REQUIRED_DEFAULT,
		  'acp/prune.php'			=> self::REQUIRED_DEFAULT,
		  'acp/search.php'		=> self::REQUIRED_DEFAULT,
		  'acp/styles.php'		=> self::REQUIRED_DEFAULT,
		  'acp/users.php'			=> self::REQUIRED_DEFAULT,

		  'email/README'							=> self::NOT_REQUIRED,
		  'email/admin_activate.txt'				=> self::REQUIRED,
		  'email/admin_send_email.txt'			=> self::REQUIRED,
		  'email/admin_welcome_activated.txt'		=> self::REQUIRED,
		  'email/admin_welcome_inactive.txt'		=> self::REQUIRED,
		  'email/coppa_resend_inactive.txt'		=> self::REQUIRED,
		  'email/coppa_welcome_inactive.txt'		=> self::REQUIRED,
		  'email/email_notify.txt'				=> self::REQUIRED,
		  'email/forum_notify.txt'				=> self::REQUIRED,
		  'email/group_added.txt'					=> self::REQUIRED,
		  'email/group_approved.txt'				=> self::REQUIRED,
		  'email/group_request.txt'				=> self::REQUIRED,
		  'email/index.htm'						=> array('status' => self::REQUIRED_EMPTY, 'data' => $html_body),
		  'email/installed.txt'					=> self::REQUIRED,
		  'email/newtopic_notify.txt'				=> self::REQUIRED,
		  'email/pm_report_closed.txt'			=> self::REQUIRED,
		  'email/pm_report_deleted.txt'			=> self::REQUIRED,
		  'email/post_approved.txt'				=> self::REQUIRED,
		  'email/post_disapproved.txt'			=> self::REQUIRED,
		  'email/privmsg_notify.txt'				=> self::REQUIRED,
		  'email/profile_send_email.txt'			=> self::REQUIRED,
		  'email/profile_send_im.txt'				=> self::REQUIRED,
		  'email/report_closed.txt'				=> self::REQUIRED,
		  'email/report_deleted.txt'				=> self::REQUIRED,
		  'email/topic_approved.txt'				=> self::REQUIRED,
		  'email/topic_disapproved.txt'			=> self::REQUIRED,
		  'email/topic_notify.txt'				=> self::REQUIRED,
		  'email/user_activate.txt'				=> self::REQUIRED,
		  'email/user_activate_inactive.txt'		=> self::REQUIRED,
		  'email/user_activate_passwd.txt'		=> self::REQUIRED,
		  'email/user_remind_inactive.txt'		=> self::REQUIRED,
		  'email/user_resend_inactive.txt'		=> self::REQUIRED,
		  'email/user_welcome.txt'				=> self::REQUIRED,
		  'email/user_welcome_inactive.txt'		=> self::REQUIRED,
		  'email/user_reactivate_account.txt'		=> self::REQUIRED,
	   );

	  case 'prosilver':

		return array(
		  'README'			=> self::NOT_REQUIRED,
		  'docs/README'		=> self::NOT_REQUIRED,

		  'index.htm'					=> array('status' => self::REQUIRED_EMPTY, 'data' => $html_body),
		  'imageset.cfg'				=> self::REQUIRED,

		  'icon_contact_pm.gif'		=> self::REQUIRED,

		  'icon_post_edit.gif'		=> self::REQUIRED,
		  'icon_post_quote.gif'		=> self::REQUIRED,

		  'icon_user_online.gif'		=> self::REQUIRED,

		  'button_pm_forward.gif'		=> self::REQUIRED,
		  'button_pm_new.gif'			=> self::REQUIRED,
		  'button_pm_reply.gif'		=> self::REQUIRED,
		  'button_topic_new.gif'		=> self::REQUIRED,
		  'button_topic_reply.gif'	=> self::REQUIRED,
		  'button_topic_locked.gif'	=> self::REQUIRED,
		);

	  case 'subsilver2':

		return array(
		  'README'			=> self::NOT_REQUIRED,
		  'docs/README'		=> self::NOT_REQUIRED,

		  'index.htm'					=> array('status' => self::REQUIRED_EMPTY, 'data' => $html_body),
		  'imageset.cfg'				=> self::REQUIRED,

		  'icon_contact_aim.gif'		=> self::REQUIRED,
		  'icon_contact_email.gif'	=> self::REQUIRED,
		  'icon_contact_icq.gif'		=> self::REQUIRED,
		  'icon_contact_jabber.gif'	=> self::REQUIRED,
		  'icon_contact_msnm.gif'		=> self::REQUIRED,
		  'icon_contact_pm.gif'		=> self::REQUIRED,
		  'icon_contact_yahoo.gif'	=> self::REQUIRED,
		  'icon_contact_www.gif'		=> self::REQUIRED,

		  'icon_post_delete.gif'		=> self::REQUIRED,
		  'icon_post_edit.gif'		=> self::REQUIRED,
		  'icon_post_info.gif'		=> self::REQUIRED,
		  'icon_post_quote.gif'		=> self::REQUIRED,
		  'icon_post_report.gif'		=> self::REQUIRED,

		  'icon_user_online.gif'		=> self::REQUIRED,
		  'icon_user_offline.gif'		=> self::REQUIRED,
		  'icon_user_profile.gif'		=> self::REQUIRED,
		  'icon_user_search.gif'		=> self::REQUIRED,
		  'icon_user_warn.gif'		=> self::REQUIRED,

	  //	'button_pm_forward.gif'		=> self::NOT_REQUIRED,
		  'button_pm_new.gif'			=> self::REQUIRED,
		  'button_pm_reply.gif'		=> self::REQUIRED,
		  'button_topic_locked.gif'	=> self::REQUIRED,
		  'button_topic_new.gif'		=> self::REQUIRED,
		  'button_topic_reply.gif'	=> self::REQUIRED,
	   );

	  default:

		return array();
	}
  }

}
?>
