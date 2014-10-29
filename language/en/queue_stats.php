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
	'APPROVAL_DENIAL_RATE'		=> 'Overall approval to denial rate',
	'AVG_CURRENT_QUEUE_WAIT'	=> 'The current unvalidated revisions have spent an average of <strong>%s</strong> in the queue.',
	'AVG_PAST_VALIDATION_TIME'	=> 'The average validation time for revisions validated in the past year is <strong>%s</strong>.',
	'NO_QUEUE_ACTIVITY'			=> 'There has been no queue activity during this time span.',
	'NO_QUEUE_STATS'			=> 'There are no queue statistics to display.',
	'NUM_REVISIONS_IN_QUEUE'	=> 'There are currently <strong>%s revisions</strong> in the queue.',
	'OLDEST_UNVALIDATED_REV'	=> 'The oldest unvalidated revision was submitted <strong>%s</strong> ago.',
	'QUEUE_ACTIVITY_30_DAYS'	=> 'Queue activity in the past 30 days',
	'SINCE_X_VALIDATED_REVS'	=> 'Since <strong>%1$s</strong>, the team has validated <strong>%2$s</strong> revisions, of which <strong>%3$s</strong> have been denied and <strong>%4$s</strong> have been approved.'
));
