<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

// Config
define('TRANSLATION_CAT_ID', 22);

/**
 * @ignore
 */
define('IN_TITANIA', true);
define('IN_TITANIA_CONVERT', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;
titania::add_lang('manage');

if (!class_exists('titania_type_translation'))
{
  require TITANIA_ROOT . 'includes/types/translation.php';
}

// Give founders access to run this
if (phpbb::$user->data['user_type'] != USER_FOUNDER)
{
	titania::needs_auth();
}

// Hopefully this helps
@set_time_limit(0);

// Delete all translations
$contrib = new titania_contribution;
$sql = 'SELECT * FROM ' . TITANIA_CONTRIBS_TABLE . ' WHERE contrib_type = ' . TITANIA_TYPE_TRANSLATION;
$result = phpbb::$db->sql_query($sql);
while ($row = phpbb::$db->sql_fetchrow($result))
{
	$contrib->__set_array($row);
	$contrib->delete();
}
phpbb::$db->sql_freeresult($result);

// All the language packs we'll need, we wont transfer inactive ones, they need to be cleaned up anyways
$sql = 'SELECT * FROM lang_packs WHERE display > 0 AND version = 3';
$result = $db->sql_query($sql);

// All the authors we'll need
$sql2 = 'SELECT * FROM lang_packs_authors WHERE lang_id IN ( SELECT lang_id FROM lang_packs WHERE display > 0 ) ';
$authors = $db->sql_fetchrowset($db->sql_query($sql2));

$i = 0;
while ($row = $db->sql_fetchrow($result))
{
	$permalink = titania_url::url_slug($row['english_name']);
	$conflict = $cnt = false;
	do {
		$permalink_test = ($cnt !== false) ? $permalink . '_' . $cnt : $permalink;
		$sql = 'SELECT contrib_id FROM ' . TITANIA_CONTRIBS_TABLE . '
			WHERE contrib_name_clean = \'' . phpbb::$db->sql_escape($permalink_test) . '\'';
		$p_result = phpbb::$db->sql_query($sql);
		if (phpbb::$db->sql_fetchrow($p_result))
		{
			$conflict = true;
			$cnt = ($cnt === false) ? 2 : $cnt + 1;
		}
		else
		{
			$conflict = false;
			$permalink = $permalink_test;
		}
		phpbb::$db->sql_freeresult($p_result);
	} while ($conflict == true);

  // This basically maps old entries to the contribution format
  $pack = array(
	'contrib_type' => TITANIA_TYPE_TRANSLATION,
	'contrib_user_id' => $row['user_id'],
	'contrib_name' => $row['english_name'],
	'contrib_name_clean' => $permalink,
	'contrib_desc' => $row['intro_text'],
	'contrib_status'  => TITANIA_CONTRIB_NEW,
	'contrib_visible' => true,
	'contrib_views'					=> 0,
	'contrib_rating'				=> 0,
	'contrib_rating_count'			=> 0,
	'contrib_last_update' => $row['last_update'],
	'contrib_iso_code' => $row['package_name'],
	'contrib_local_name' => $row['local_name'],
  );

  $id = titania_insert(TITANIA_CONTRIBS_TABLE, $pack);

  $cat_ary = array(
  	'category_id'	=> TRANSLATION_CAT_ID,
  	'contrib_id'	=> $id,
  );
  titania_insert(TITANIA_CONTRIB_IN_CATEGORIES_TABLE, $cat_ary);

  // and add the respective authors
  foreach ($authors as $author)
  {
	if ($author['lang_id'] == $row['lang_id'])
	{
	  $sql_ary = array(
		'contrib_id' => $id, // new contribution (language pack) id
		'user_id' => $author['user_id'],
		'active' => true,
	  );

	  titania_insert(TITANIA_CONTRIB_COAUTHORS_TABLE, $sql_ary);
	}
  }

  $i++;
}

$sync = new titania_sync;

$sync->authors('count');

$sync->contribs('validated');

$sync->categories('count');

phpbb::$cache->purge();

echo $i . ' language packs added to Titania';

/**
 * Wrapper for inserting data into a table for Titania
 *
 * @author EXReaction
 * @param string $table
 * @param array $sql_ary
 * @return int
 */
function titania_insert($table, $sql_ary)
{
	$sql = 'INSERT INTO ' . $table . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_ary);

	phpbb::$db->sql_return_on_error(true);

	phpbb::$db->sql_query($sql);

	if (phpbb::$db->sql_error_triggered && phpbb::$db->sql_error_returned['code'] != 1062) // Ignore duplicate entry errors
	{
		echo '<br />' . $sql . '<br />';
		echo 'SQL ERROR [ ' . phpbb::$db->sql_layer . ' ]<br /><br />' . phpbb::$db->sql_error_returned['message'] . ' [' . phpbb::$db->sql_error_returned['code'] . ']<br />';
	}

	phpbb::$db->sql_return_on_error(false);

	return phpbb::$db->sql_nextid();
}

