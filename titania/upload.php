<?php
/**
 *
 * @package Titania Attachments
 * @version $Id:$
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
titania::load_object('attachments');
titania::add_lang('attachments');

// Request some variables.
$delete 		= request_var('delete', '');
$attachment_id 	= request_var('attachment_id', false);
// @todo For now this will always be a download for a contrib untill uploadify class if built to handle the SWF object or the form that
// that user submits.
$attacment_type = request_var('attachment_type', TITANIA_DOWNLOAD_CONTRIB);

// Setup attachment object.
$attachment = new titania_attachments($attacment_type, $attachment_id);

// Do we want to delete an attachment?
if ($delete)
{
	$attachment->object_id = $delete;
	$attachment->delete();
}
else
{
	// If we reach this point, try to create an attachment, if this fails the upload response method will catch it.
	$attachment->create();
}

// So what eneded up happinning?
$attachment->uploader->response($attachment);