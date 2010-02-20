<?php
/**
*
* authors [English]
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
'SUBMIT_TIME' => 'Submission Time',
	'VALIDATION_QUEUE'	=> 'Validation Queue',
	'VALIDATION_NOTES'	=> 'Validation Notes',
	'VALIDATION_MPV' => 'MPV Notes',
	'VALIDATION'	=> 'Validation',
	'VALIDATION_SUBMISSION' => 'Validation Submission',
	'RETEST_MPV'	=> 'Re-test MPV',
	'RETEST_AUTOMOD'	=> 'Re-test Automod',
	'REPACK' => 'Repack',
	'QUEUE_NEW' => 'New',
	'QUEUE_ATTENTION' => 'Attention',
	'QUEUE_REPACK' => 'Repack',
	'QUEUE_VALIDATING' => 'Validating',
	'QUEUE_TESTING' => 'Testing',
	'QUEUE_APPROVE' => 'Awaiting Approval',
	'QUEUE_DENY' => 'Awaiting Denial',
	'MOVE' => 'Move',
	'APPROVE' => 'Approve',
	'DENY' => 'Deny',
	'ALTER_NOTES' => 'Alter Validation Notes',
	'MOVE_QUEUE' => 'Move Queue',
	'MOVE_QUEUE_CONFIRM' => 'Select the new queue location and confirm.',
	'EDIT_VALIDATION_NOTES' => 'Edit Validation Notes',
));
