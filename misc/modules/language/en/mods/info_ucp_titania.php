<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Merge the following language entries into the lang array
$lang = array_merge($lang, array(
	'NO_SECTIONS'					=> 'You are not subscribed to any sections.',
	'NO_ITEMS'						=> 'You are not subscribed to any items.',
	'NO_SUBSCRIPTIONS_SELECTED'		=> 'No subscriptions selected.',
	'NO_TYPES_SELECTED'				=> 'No subscriptions type selected.',
	
	'SUBSCRIPTION_ATTENTION'			=> 'Attention queue',
	'SUBSCRIPTION_CONTRIB'				=> 'Contributions',
	'SUBSCRIPTION_ITEMS_MANAGE'			=> 'Manage items subscriptions',
	'SUBSCRIPTION_SECTIONS_MANAGE'		=> 'Manage sections subscriptions',
	'SUBSCRIPTION_ITEMS_MANAGE_EXPLAIN'	=> 'Below is a list of items you are subscribed to in the customisation database. You will be notified of new posts in either. <br />To unsubscribe mark the items then press the Unwatch marked button.',
	'SUBSCRIPTION_SECTIONS_MANAGE_EXPLAIN'	=> 'Below is a list of sections you are subscribed to in the customisation database. You will be notified of new posts in either. <br />To unsubscribe mark the sections then press the Unwatch marked button.',
	'SUBSCRIPTION_QUEUE'			=> 'Validation Queue',
	'SUBSCRIPTION_QUEUE_VALIDATION'	=> 'Validation Discussion',
	'SUBSCRIPTION_SUPPORT_TOPIC'	=> 'Support topic',
	'SUBSCRIPTION_TARGET'			=> 'Target',
	'SUBSCRIPTION_TITANIA'			=> 'Customisation Database subscriptions',
	'SUBSCRIPTION_TOPIC'			=> 'Items',
	'SUBSCRIPTION_SUPPORT'			=> 'Discussion/Support Section',
	
	'UNWATCH_SUBSCRIPTION_MARKED'	=> 'Unwatch marked',
	'UNWATCHED_SUBSCRIPTIONS'		=> 'You are no longer subscribed to the selected subscriptions.',
	
	'WATCHED_SECTIONS'				=> 'Watched sections',
	'WATCHED_SINCE'					=> 'Watched since',
	'WATCHED_ITEMS'					=> 'Watched items',
));

?>