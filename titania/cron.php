<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_TITANIA', true);
define('IN_CRON', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;

@set_time_limit(1200);

$cron_type = request_var('cron_type', '');
$use_shutdown_function = (@function_exists('register_shutdown_function')) ? true : false;

// Output transparent gif
header('Cache-Control: no-cache');
header('Content-type: image/gif');
header('Content-length: 43');

echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

// test without flush ;)
// flush();

/**
* Run cron-like action
*/
if (!isset(phpbb::$config['titania_last_automod_run']) || titania::$time - 30 > phpbb::$config['titania_last_automod_run'])
{
	set_config('titania_last_automod_run', titania::$time, true);

	$use_shutdown_function = false;

	$sql = 'SELECT aq.*, a.attachment_directory, a.physical_filename, c.contrib_id, c.contrib_name_clean, r.revision_version, r.revision_status
		FROM ' . TITANIA_AUTOMOD_QUEUE_TABLE . ' aq, ' . TITANIA_REVISIONS_TABLE . ' r, ' . TITANIA_ATTACHMENTS_TABLE . ' a, ' . TITANIA_CONTRIBS_TABLE . ' c
		WHERE r.revision_id = aq.revision_id
			AND a.attachment_id = r.attachment_id
			AND c.contrib_id = r.contrib_id';
	$result = phpbb::$db->sql_query_limit($sql, 2);
	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		// Delete here in case any errors come up from the test so that it does't get stuck.
		$sql = 'DELETE FROM ' . TITANIA_AUTOMOD_QUEUE_TABLE . '
			WHERE row_id = ' . $row['row_id'];
		phpbb::$db->sql_query($sql);

		$new_dir_name = $row['contrib_name_clean'] . '_' . preg_replace('#[^0-9a-z]#', '_', strtolower($row['revision_version']));
		$version = $row['phpbb_version_branch'][0] . '.' . $row['phpbb_version_branch'][1] . '.' . $row['phpbb_version_revision'];
		$zip = titania::$config->upload_path . utf8_basename($row['attachment_directory']) . '/' . utf8_basename($row['physical_filename']);

		$details = $results = $bbcode_results = '';
		$contrib_tools = new titania_contrib_tools($zip, $new_dir_name);

		$package_root = $contrib_tools->find_root();
		$contrib_tools->restore_root($package_root);

		if (sizeof($contrib_tools->error))
		{
			continue;
		}

		if (!($phpbb_path = $contrib_tools->automod_phpbb_files($version)))
		{
			continue;
		}

		if ($contrib_tools->automod($phpbb_path, $details, $results, $bbcode_results))
		{
			$sql_ary = array(
				'revision_id'				=> $row['revision_id'],
				'contrib_id'				=> $row['contrib_id'],
				'phpbb_version_branch'		=> $row['phpbb_version_branch'],
				'phpbb_version_revision'	=> get_real_revision_version($row['phpbb_version_revision']),
				'revision_validated'		=> ($row['revision_status'] == TITANIA_REVISION_APPROVED) ? true : false,
			);
			phpbb::$db->sql_query('INSERT INTO ' . TITANIA_REVISIONS_PHPBB_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_ary));
		}

		$contrib_tools->remove_temp_files();

		unset($contrib_tools);
	}
	phpbb::$db->sql_freeresult($result);
}


// Unloading cache and closing db after having done the dirty work.
if ($use_shutdown_function)
{
	register_shutdown_function('garbage_collection');
}
else
{
	garbage_collection();
}

exit;