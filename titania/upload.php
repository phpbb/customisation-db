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

// Some basic stuff
titania::add_lang('attachments');

// Request some variables.
$delete 			= request_var('delete', false);
$object_id 			= request_var('attachment_id', false);
$form_name			= request_var('form_name', 'uploadify');
$object_type		= request_var('object_type', '');
$attachment_type	= request_var('attachment_type', TITANIA_DOWNLOAD_CONTRIB);

// Setup attachment object.
$attachment = new titania_attachments($attachment_type, $object_type, $object_id);

// Do we want to delete an attachment?
if ($delete && $object_id)
{
    // @todo
	$attachment->object_id = $object_id;
	$attachment->delete();
}
else
{
	// If we reach this point, try to create an attachment, if this fails the upload response method will catch it.
	$attachment->create($form_name);
}

// The attachment class handles the response and calls the page_header and page_footer functions.