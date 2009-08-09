<?php
/**
 *
 * @package Titania Attachments
 * @version $Id$
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
* @ignore
*/
define('IN_TITANIA', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
include(TITANIA_ROOT . 'common.' . PHP_EXT);

// All we are doing here is uploading a little file correct?
titania::load_object('attachments');
titania::add_lang('attachments');

// Request some variables.
$delete 		= request_var('delete', '');
$attachment_id 	= request_var('attachment_id', false);

// Setup attachment object.
$attachment = new titania_attachments(TITANIA_DOWNLOAD_CONTRIB, $attachment_id);

// Are we deleting an attachment?
if ($delete && $attachment_id)
{
	$attachment->delete();
}

// Create our attachment.
$filedata = $attachment->create();

if (!$filedata['error'])
{
	phpbb::$template->set_filenames(array(
		'file'	=> 'uploadify_file.html'
	));

	header('Content-type: application/json');

	$attachment->display_attachments($attachment->attachment_id);

	// No page_header();

	$response = array(
		'html' 	=> phpbb::$template->assign_display('file'),
		'id'	=> $attachment->attachment_id,
	);

	phpbb::$template->assign_var('JSON', json_encode($response));

	titania::page_footer(false, 'json_response.html');
}

// @todo Handle file errors such as file to big, not a valid extension, etc...