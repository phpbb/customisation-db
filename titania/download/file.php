<?php
/**
*
* @package titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_TITANIA', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './../');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require(TITANIA_ROOT . 'common.' . PHP_EXT);
include(TITANIA_ROOT . 'includes/class_download.' . PHP_EXT);

// Get download by id.
$download_id = request_var('id', 0);

if ($download_id)
{
	$download = new titania_download(0);
	$download->load();

	if ($download->has_access($user->data['user_id']))
	{
		$download->stream();
	}

	// @todo beautiful message
	header('HTTP/1.0 404 not found');
}

// Download the newest revision of a contribution.
$contrib_id = request_var('contrib_id', 0);

if ($contrib_id)
{
	// @todo
}

// @todo beautiful message
header('HTTP/1.0 404 not found');
die('Download not found.');