<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
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
	'ADMINISTRATION'			=> 'Administration',
	'ALLOW_AUTHOR_REPACK'		=> 'Allow author to repack',
	'ALTER_NOTES'				=> 'Alter Validation Notes',
	'APPROVE'					=> 'Approve',
	'APPROVE_QUEUE'				=> 'Approve',
	'APPROVE_QUEUE_CONFIRM'		=> 'Are you sure you want to <strong>approve</strong> this item?',
	'ATTENTION'					=> 'Attention',
	'AUTHOR_REPACK_LINK'		=> 'Click here to repack the revision',

	'CATEGORY_NAME_CLEAN'		=> 'Category URL',
	'CHANGE_STATUS'				=> 'Change Status/Move',
	'CLOSED_ITEMS'				=> 'Closed Items',

	'DELETE_QUEUE'				=> 'Delete Queue Entry',
	'DELETE_QUEUE_CONFIRM'		=> 'Are you sure you want to delete this queue entry?  All posts for the queue will be lost and the revision will be set to pulled if it is new.',
	'DENY'						=> 'Deny',
	'DENY_QUEUE'				=> 'Deny',
	'DENY_QUEUE_CONFIRM'		=> 'Are you sure you want to <strong>deny</strong> this item?',
	'DISAPPROVE_ITEM'			=> 'Disapprove',
	'DISAPPROVE_ITEM_CONFIRM'	=> 'Are you sure you want to <strong>disapprove</strong> this item?',
	'DISCUSSION_REPLY_MESSAGE'	=> 'Queue discussion reply message',

	'EDIT_VALIDATION_NOTES'		=> 'Edit Validation Notes',
	'ERROR_REVISION_ON_HOLD'	=> 'You cannot approve this revision. The next phpBB version has not been released.',

	'INVALID_TOOL'				=> 'The tool selected does not exist.',

	'MANAGE_CATEGORIES'			=> 'Manage Categories',
	'MARK_IN_PROGRESS'			=> 'Mark "In Progress"',
	'MARK_NO_PROGRESS'			=> 'Unmark "In Progress"',
	'MARK_TESTED'				=> 'Mark as “Tested”',
	'MARK_UNTESTED'				=> 'Unmark as “Tested”',
	'MOVE_QUEUE'				=> 'Move Queue',
	'MOVE_QUEUE_CONFIRM'		=> 'Select the new queue location and confirm.',

	'NO_ATTENTION'				=> 'No items need attention.',
	'NO_ATTENTION_ITEM'			=> 'Attention item does not exist.',
	'NO_ATTENTION_TYPE'			=> 'Inappropriate attention type.',
	'NO_NOTES'					=> 'No Notes',
	'NO_QUEUE_ITEM'				=> 'Queue item does not exist.',

	'OLD_VALIDATION_AUTOMOD'	=> 'Automod Test from pre-repack',
	'OLD_VALIDATION_MPV'		=> 'MPV Notes from pre-repack',
	'OPEN_ITEMS'				=> 'Open Items',

	'PLEASE_WAIT_FOR_TOOL'		=> 'Please wait for the tool to finish running.',
	'PUBLIC_NOTES'				=> 'Public release notes',

	'QUEUE_APPROVE'				=> 'Awaiting Approval',
	'QUEUE_ATTENTION'			=> 'Attention',
	'QUEUE_CLOSED'				=> 'Closed',
	'QUEUE_DENY'				=> 'Awaiting Denial',
	'QUEUE_DISCUSSION_TOPIC'	=> 'Queue Discussion Topic',
	'QUEUE_NEW'					=> 'New',
	'QUEUE_REPACK'				=> 'Repack',
	'QUEUE_REPACK_ALLOWED'		=> 'Repacking Allowed',
	'QUEUE_REPACK_NOT_ALLOWED'	=> 'Repacking <strong>Not</strong> Allowed',
	'QUEUE_REPLY_ALLOW_REPACK'	=> 'Set to allow the author to repack',
	'QUEUE_REPLY_APPROVED'		=> 'Revision %1$s [b]approved[/b] with the following note:<br /><br />[quote]%2$s[/quote]',
	'QUEUE_REPLY_DENIED'		=> 'Revision %1$s [b]denied[/b] for the following reason:<br /><br />[quote]%2$s[/quote]',
	'QUEUE_REPLY_IN_PROGRESS'	=> 'Marked as in-progress',
	'QUEUE_REPLY_MOVE'			=> 'Moved from %1$s to %2$s',
	'QUEUE_REPLY_NO_PROGRESS'	=> 'Unmarked as in-progress',
	'QUEUE_REVIEW'				=> 'Queue review',
	'QUEUE_STATUS'				=> 'Queue Status',
	'QUEUE_TESTING'				=> 'Testing',
	'QUEUE_VALIDATING'			=> 'Validating',

	'REBUILD_FIRST_POST'		=> 'Rebuild first post',
	'REPACK'					=> 'Repack',
	'REPORTED'					=> 'Reported',
	'RETEST_AUTOMOD'			=> 'Re-test Automod',
	'RETEST_PV'					=> 'Re-test prevalidator',
	'REVISION_REPACKED'			=> 'This revision has been repacked.',

	'SUBMIT_TIME'				=> 'Submission Time',
	'SUPPORT_ALL_VERSIONS'		=> 'Support all phpBB versions.',

	'TEAM_ONLY'					=> 'May only be selected by team members.',

	'UNKNOWN'					=> 'Unknown',

	'VALIDATION'				=> 'Validation',
	'VALIDATION_AUTOMOD'		=> 'Automod Test',
	'VALIDATION_MESSAGE'		=> 'Validation Message/Reason',
	'VALIDATION_NOTES'			=> 'Validation Notes',
	'VALIDATION_PV'				=> 'Prevalidator Notes',
	'VALIDATION_QUEUE'			=> 'Validation Queue',
	'VALIDATION_SUBMISSION'		=> 'Validation Submission',
));
