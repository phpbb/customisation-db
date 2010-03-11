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
if (!defined('IN_TITANIA'))
{
	exit;
}

if (!phpbb::$auth->acl_gets('u_titania_mod_author_mod', 'u_titania_mod_contrib_mod', 'u_titania_mod_faq_mod', 'u_titania_mod_post_mod') && !sizeof(titania_types::find_authed('moderate')))
{
	titania::needs_auth();
}

$attention_id = request_var('a', 0);
$object_type = request_var('type', 0);
$object_id = request_var('id', 0);

if ($attention_id || ($object_type && $object_id))
{
	if ($attention_id)
	{
		$row = attention_overlord::load_attention($attention_id);
	}
	else
	{
		$row = attention_overlord::load_attention(false, $object_type, $object_id);
	}

	if (!$row)
	{
		trigger_error('NO_ATTENTION_ITEM');
	}

	// Setup
	$attention_object = new titania_attention;
	$attention_object->__set_array($row);

	// Grab some users
	$user_ids = array($row['attention_requester']);
	if ($row['attention_close_user'])
	{
		$user_ids[] = $row['attention_close_user'];
	}
	users_overlord::load_users($user_ids);

	$output = array_merge(
		$attention_object->assign_details(true),
		users_overlord::assign_details($row['attention_requester'])
	);

	// Do we have to?
	if ($row['attention_close_user'])
	{
		$output = array_merge(
			$output,
			users_overlord::assign_details($row['attention_close_user'], 'CLOSE_')
		);
	}

	phpbb::$template->assign_block_vars('attention', $output);

	unset($attention_object);
}
else
{
	$type = request_var('type', '');
	$display_all = request_var('display_all', false);

	switch ($type)
	{
		case 'reported' :
			$type = TITANIA_ATTENTION_REPORTED;
		break;

		case 'unapproved' :
			$type = TITANIA_ATTENTION_UNAPPROVED;
		break;

		default :
			$type = false;
		break;
	}

	attention_overlord::display_attention_list($type, $display_all);
}

titania::page_header('ATTENTION');

titania::page_footer(true, 'manage/attention.html');
