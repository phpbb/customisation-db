<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_version_add
{
	function display_options()
	{
		return array(
			'title'	=> 'PHPBB_VERSION_ADD',
			'vars'	=> array(
				'new_phpbb_version'		=> array('lang' => 'NEW_PHPBB_VERSION', 'type' => 'text:40:255', 'explain' => true),
				'limit_phpbb_version'	=> array('lang' => 'VERSION_RESTRICTION', 'type' => 'select_multiple', 'function' => 'pva_generate_phpbb_version_select', 'explain' => true, 'default' => ''),
				'category'				=> array('lang' => 'CATEGORY', 'type' => 'select_multiple', 'function' => 'pva_generate_category_select', 'explain' => true),
			)
		);
	}

	function run_tool()
	{
		$new_phpbb_version = request_var('new_phpbb_version', '');
		$limit_phpbb_versions = request_var('limit_phpbb_version', array(''));
		$categories = request_var('category', array(0));

		if (!$new_phpbb_version || strlen($new_phpbb_version) < 5 || $new_phpbb_version[1] != '.' || $new_phpbb_version[3] != '.')
		{
			trigger_back('NO_VERSION_SELECTED');
		}

		$phpbb_version_branch = (int) $new_phpbb_version[0] . (int) $new_phpbb_version[2];
		$phpbb_version_revision = get_real_revision_version(substr($new_phpbb_version, 4));

		// Is it in our version cache?
		$versions = titania::$cache->get_phpbb_versions();
		if (!isset($versions[$phpbb_version_branch . $phpbb_version_revision]))
		{
			titania::$cache->destroy('_titania_phpbb_versions');
		}

		// Categories limiter
		$contribs = $revisions = array();
		if (sizeof($categories) > 1 || (sizeof($categories) && $categories[0] != 0))
		{
			$sql = 'SELECT contrib_id FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . '
				WHERE ' . phpbb::$db->sql_in_set('category_id', array_map('intval', $categories));
			$result = phpbb::$db->sql_query($sql);
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$contribs[] = $row['contrib_id'];
			}
			phpbb::$db->sql_freeresult($result);

			if (!sizeof($contribs))
			{
				trigger_back('NO_REVISIONS_UPDATED');
			}
		}

		if (sizeof($limit_phpbb_versions) > 1 || (sizeof($limit_phpbb_versions) && $limit_phpbb_versions[0] != 0))
		{
			// phpBB versions limiter
			foreach ($limit_phpbb_versions as $limit_phpbb_version)
			{
				$sql = 'SELECT rp.contrib_id, rp.revision_id, r.revision_status FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . ' rp, ' . TITANIA_REVISIONS_TABLE . ' r
					WHERE rp.phpbb_version_branch = ' . (int) substr($limit_phpbb_version, 0, 2) . '
						AND rp.phpbb_version_revision = \'' . phpbb::$db->sql_escape(substr($limit_phpbb_version, 2)) . '\'' .
						((sizeof($contribs)) ? ' AND ' . phpbb::$db->sql_in_set('rp.contrib_id', array_map('intval', $contribs)) : '') . '
						AND r.revision_id = rp.revision_id';
				$result = phpbb::$db->sql_query($sql);
				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$revisions[$row['revision_id']] = $row;
				}
				phpbb::$db->sql_freeresult($result);
			}
		}
		else if (sizeof($categories) > 1 || (sizeof($categories) && $categories[0] != 0))
		{
			// Only category limited
			$sql = 'SELECT contrib_id, revision_id, revision_status FROM ' . TITANIA_REVISIONS_TABLE . '
				WHERE ' . phpbb::$db->sql_in_set('contrib_id', array_map('intval', $contribs));
			$result = phpbb::$db->sql_query($sql);
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$revisions[$row['revision_id']] = $row;
			}
			phpbb::$db->sql_freeresult($result);
		}
		else
		{
			// All
			$sql = 'SELECT contrib_id, revision_id, revision_status FROM ' . TITANIA_REVISIONS_TABLE;
			$result = phpbb::$db->sql_query($sql);
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$revisions[$row['revision_id']] = $row;
			}
			phpbb::$db->sql_freeresult($result);
		}

		if (!sizeof($revisions))
		{
			trigger_back('NO_REVISIONS_UPDATED');
		}

		// Don't include those which already are marked for this phpBB version
		$sql = 'SELECT revision_id FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . '
			WHERE ' . phpbb::$db->sql_in_set('revision_id', array_map('intval', array_keys($revisions))) . '
				AND phpbb_version_branch = ' . $phpbb_version_branch . '
				AND phpbb_version_revision = \'' . phpbb::$db->sql_escape($phpbb_version_revision) . '\'';
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			unset($revisions[$row['revision_id']]);
		}
		phpbb::$db->sql_freeresult($result);

		if (!sizeof($revisions))
		{
			trigger_back('NO_REVISIONS_UPDATED');
		}

		$sql_ary = array();
		foreach ($revisions as $revision_id => $row)
		{
			$sql_ary[] = array(
				'contrib_id'				=> (int) $row['contrib_id'],
				'revision_id'				=> (int) $revision_id,
				'phpbb_version_branch'		=> $phpbb_version_branch,
				'phpbb_version_revision'	=> $phpbb_version_revision,
				'revision_validated'		=> ($row['revision_status'] == TITANIA_REVISION_APPROVED) ? true : false,
			);
		}

		phpbb::$db->sql_multi_insert(TITANIA_REVISIONS_PHPBB_TABLE, $sql_ary);

		trigger_back(sprintf(phpbb::$user->lang['REVISIONS_UPDATED'], sizeof($revisions)));
	}
}

if (!function_exists('pva_generate_phpbb_version_select'))
{
	function pva_generate_phpbb_version_select()
	{
		$versions = titania::$cache->get_phpbb_versions();

		$select = '<option value="0" selected="selected">-----</option>';

		foreach ($versions as $version => $name)
		{
			$select .= '<option value="' . $version . '">' . $name . '</option>';
		}

		return $select;
	}
}

if (!function_exists('pva_generate_category_select'))
{
	function pva_generate_category_select()
	{
		titania::_include('functions_posting', 'generate_category_select');

		phpbb::$template->destroy_block_vars('category_select');
		generate_category_select();

		phpbb::$template->set_filenames(array(
			'generate_category_select'		=> 'manage/generate_category_select.html',
		));

		$select = phpbb::$template->assign_display('generate_category_select');

		return $select;
	}
}