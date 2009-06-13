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

/**
* Titania append_sid function.  Why?  Because this is easier. :P
*
* @param mixed $page What you would put between TITANIA_ROOT and '.' . PHP_EXT (if this doesn't work for you, use append_sid!)
* @param mixed $params Same as append_sid
* @param mixed $is_amp Same as append_sid
* @param mixed $session_id Same as append_sid
* @return string Same as append_sid
*/
function titania_sid($page, $params = false, $is_amp = true, $session_id = false)
{
	return append_sid(TITANIA_ROOT . $page . '.' . PHP_EXT, $params = false, $is_amp = true, $session_id = false);
}

/**
* Quick function to take a normal select query, turn it into a count query, run the query, then return the result.
*
* @param string $query The normal SQL Query
* @param string $count_column The name of the column you would like to count (probably the primary key...)
*/
function sql_count_query($query, $count_column)
{
	$query = preg_replace('#SELECT(.*)FROM#', 'SELECT COUNT(' . $count_column . ') AS cnt FROM', $query);
	$query = preg_replace('#LIMIT ([0-9]+)(, ([0-9]+))?#', '', $query);

	phpbb::$db->sql_query($query);
	$cnt = (int) phpbb::$db->sql_fetchfield('cnt');
	phpbb::$db->sql_freeresult();

	return $cnt;
}

/**
* Get version string from version number
*
* @param int|string $version The version number 20 for 2.0.x, 30 for 3.0.x
*/
function get_version_string($version)
{
	$version = (string) $version;
	$major = (isset($version[0])) ? $version[0] : 0;
	$minor = (isset($version[1])) ? $version[1] : 0;
	$revision = (isset($version[2])) ? substr($version, 2) : 0;

	return sprintf('%u.%u.%u', $major, $minor, $revision);
}