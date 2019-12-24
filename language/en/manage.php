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

use phpbb\titania\ext;

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
	'CONFIG_SETTINGS'			=> 'Configuration Settings',

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
	'QUEUE_REPACK_NOT_ALLOWED'	=> 'Repacking [b]Not[/b] Allowed',
	'QUEUE_REPLY_ALLOW_REPACK'	=> 'Set to allow the author to repack',
	'QUEUE_REPLY_APPROVED'		=> 'Revision %1$s [b]approved[/b] with the following note: [quote]%2$s[/quote]',
	'QUEUE_REPLY_DENIED'		=> 'Revision %1$s [b]denied[/b] for the following reason: [quote]%2$s[/quote]',
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
	'VALIDATION_TV'				=> 'Translation Validation Notes',
	'VALIDATION_QUEUE'			=> 'Validation Queue',
	'VALIDATION_SUBMISSION'		=> 'Validation Submission',
    'VALIDATION_REPACK_DIFF'	=> 'Repack Diff',

	// CONFIG SETTINGS
	'CAN_MODIFY_STYLE_DEMO_URL'         => 'Can modify style demo URL',
	'CAN_MODIFY_STYLE_DEMO_URL_EXPLAIN' => 'When editing styles, can non-team members modify the demo URL?',
	'CLEANUP_TITANIA'                   => 'Clean up Titania',
	'CLEANUP_TITANIA_EXPLAIN'           => 'Remove un-submitted revisions and attachments.',
	'COLORIZEIT'                        => 'ColorizeIt',
	'COLORIZEIT_AUTH'                   => 'ColorizeIt Auth',
	'COLORIZEIT_AUTH_EXPLAIN'           => '',
	'COLORIZEIT_EXPLAIN'                => '',
	'COLORIZEIT_URL'					=> 'ColorizeIt URL',
	'COLORIZEIT_URL_EXPLAIN'			=> '',
	'COLORIZEIT_VALUE'                  => 'ColorizeIt Value',
	'COLORIZEIT_VALUE_EXPLAIN'          => '',
	'COLORIZEIT_VAR'                    => 'ColorizeIt Var',
	'COLORIZEIT_VAR_EXPLAIN'            => '',
	'DEMO_STYLE_HOOK'                   => 'Style demo hook',
	'DEMO_STYLE_HOOK_EXPLAIN'           => 'URL for style demo board management hook, for each phpBB version branch. Example: <samp>https://www.phpbb.com/styles/demo/3.2/board/style_demo_install.php</samp>',
	'DEMO_STYLE_PATH'                   => 'Style demo path',
	'DEMO_STYLE_PATH_EXPLAIN'           => 'Path to the style demo board where styles will be installed upon validation, for each phpBB version branch. Example: <samp>../styles/demo/3.2/board/</samp>',
	'DEMO_STYLE_URL'                    => 'Style demo URL',
	'DEMO_STYLE_URL_EXPLAIN'            => 'Full URL to the style demo board, for each phpBB version branch. Example <samp>http://www.phpbb.com/styles/demo/3.2/?style_id=%s</samp>',
	'FORUM_EXTENSION_DATABASE'          => 'Extensions database forum',
	'FORUM_EXTENSION_DATABASE_EXPLAIN'  => 'Select a forum to assign Extensions database releases to (for each phpBB version branch).',
	'FORUM_EXTENSION_ROBOT'             => 'Extensions database forum robot',
	'FORUM_EXTENSION_ROBOT_EXPLAIN'     => 'ID of a user account robot for making release posts/topics in the Extensions database forum.',
	'FORUM_MOD_DATABASE'                => 'MOD database forum',
	'FORUM_MOD_DATABASE_EXPLAIN'        => 'Select a forum to assign MODs database releases to (for each phpBB version branch).',
	'FORUM_MOD_ROBOT'                   => 'MOD database forum robot',
	'FORUM_MOD_ROBOT_EXPLAIN'           => 'ID of a user account robot for making release posts/topics in the MOD database forum.',
	'FORUM_STYLE_DATABASE'              => 'Styles database forum',
	'FORUM_STYLE_DATABASE_EXPLAIN'      => 'Select a forum to assign Styles database releases to (for each phpBB version branch).',
	'FORUM_STYLE_ROBOT'                 => 'Styles database forum robot',
	'FORUM_STYLE_ROBOT_EXPLAIN'         => 'ID of a user account robot for making release posts/topics in the Styles database forum.',
	'PHPBB_ROOT_PATH'                   => 'phpBB root path',
	'PHPBB_ROOT_PATH_EXPLAIN'           => 'Relative path to the phpBB installation, from the titania root path, if it is different from a standard phpBB installation. Example: <samp>../../community/</samp>',
	'PHPBB_SCRIPT_PATH'                 => 'phpBB script path',
	'PHPBB_SCRIPT_PATH_EXPLAIN'         => 'Relative path from the server root, if it is different from a standard phpBB installation. Example: <samp>community/</samp>',
	'SEARCH_BACKEND'                    => 'Search back end',
	'SEARCH_BACKEND_EXPLAIN'            => 'Search engine, i.e.: fulltext_sphinx, zend or solr (if solr, set the correct ip/port).',
	'SEARCH_BACKEND_IP'					=> 'Search back end IP',
	'SEARCH_BACKEND_IP_EXPLAIN'			=> '',
	'SEARCH_BACKEND_PORT'				=> 'Search back end port',
	'SEARCH_BACKEND_PORT_EXPLAIN'		=> '',
	'SEARCH_ENABLED'					=> 'Enable search',
	'SEARCH_ENABLED_EXPLAIN'			=> '',
	'SITE_HOME_URL'						=> 'Site home URL',
	'SITE_HOME_URL_EXPLAIN'				=> '',
	'TABLE_PREFIX'                      => 'Titania table prefix',
	'TABLE_PREFIX_EXPLAIN'              => 'Prefix of the sql tables. Not the prefix for the phpBB tables, prefix for the Titania tables only. This MUST NOT be the same as the phpBB prefix!',
	'TEAM_GROUPS'                       => 'Team groups',
	'TEAM_GROUPS_EXPLAIN'               => 'Select the groups who will receive TITANIA_ACCESS_TEAMS level auth.',
	'TITANIA_EXTENSIONS_QUEUE'          => 'Extensions validation queue forums',
	'TITANIA_EXTENSIONS_QUEUE_EXPLAIN'  => 'Select the extensions validation queue parent category, discussion and trash forums.',
	'TITANIA_MODS_QUEUE'                => 'MODs validation queue forums',
	'TITANIA_MODS_QUEUE_EXPLAIN'        => 'Select the MODs validation queue parent category, discussion and trash forums.',
	'TITANIA_SCRIPT_PATH'               => 'Titania script path',
	'TITANIA_SCRIPT_PATH_EXPLAIN'       => 'Relative path from the server root, if it is different from a standard phpBB installation. Example: <samp>customise/</samp>',
	'TITANIA_STYLES_QUEUE'              => 'Styles validation queue forums',
	'TITANIA_STYLES_QUEUE_EXPLAIN'      => 'Select the styles validation queue parent category, discussion and trash forums.',
	'TI_KEY_' . ext::TITANIA_CLR_SCREENSHOT   => 'ColorizeIt Screenshots',
	'TI_KEY_' . ext::TITANIA_CONTRIB          => 'Contributions',
	'TI_KEY_' . ext::TITANIA_QUEUE            => 'Category',
	'TI_KEY_' . ext::TITANIA_QUEUE_DISCUSSION => 'Forum',
	'TI_KEY_' . ext::TITANIA_SCREENSHOT       => 'Screenshots',
	'TI_KEY_' . ext::TITANIA_TRANSLATION      => 'Translations',
	'TI_KEY_30'                               => '3.0 branch',
	'TI_KEY_31'                               => '3.1 branch',
	'TI_KEY_32'                               => '3.2 branch',
	'TI_KEY_33'                               => '3.3 branch',
	'TI_KEY_trash'                            => 'Trash Can',
	'UPLOAD_MAX_FILESIZE'               => 'Upload maximum file sizes',
	'UPLOAD_MAX_FILESIZE_EXPLAIN'       => 'Max file sizes of uploaded attachments (in bytes).',
));
