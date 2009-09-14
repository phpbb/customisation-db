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

if (!function_exists('generate_type_select') || !function_exists('generate_category_select'))
{
	require TITANIA_ROOT . 'includes/functions_posting.' . PHP_EXT;
}

titania::load_object('contribution');
load_contrib();

if (!titania::$contrib->is_author && !titania::$contrib->is_active_coauthor && !phpbb::$auth->acl_get('titania_contrib_mod'))
{
	trigger_error('NO_AUTH');
}

// Load the message object
titania::load_tool('message');
$message = new titania_message(titania::$contrib);
$message->set_auth(array(
	'bbcode'	=> phpbb::$auth->acl_get('titania_bbcode'),
	'smilies'	=> phpbb::$auth->acl_get('titania_smilies'),
));
$message->set_settings(array(
	'display_error'		=> false,
	'display_subject'	=> false,
	'subject_name'		=> 'name',
));

$submit = (isset($_POST['submit'])) ? true : false;

$contrib_categories = array();

if ($submit)
{
	$post_data = $message->request_data();

	titania::$contrib->post_data($post_data);
	$contrib_categories = request_var('contrib_category', array(0));

	$error = titania::$contrib->validate($contrib_categories);

	if (($validate_form_key = $message->validate_form_key()) !== false)
	{
		$error[] = $validate_form_key;
	}

	if (!sizeof($error))
	{
		titania::$contrib->submit();

		// Create relations
		titania::$contrib->put_contrib_in_categories($contrib_categories);

		titania::error_box('SUCCESS', 'CONTRIB_UPDATED', TITANIA_SUCCESS);
	}
}
else
{
	$sql = 'SELECT category_id
		FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . '
		WHERE contrib_id = ' . titania::$contrib->contrib_id;
	$result = phpbb::$db->sql_query($sql);
	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$contrib_categories[] = $row['category_id'];
	}
	phpbb::$db->sql_freeresult($result);
}

// Generate some stuff
generate_type_select(titania::$contrib->contrib_type);
generate_category_select($contrib_categories);
titania::$contrib->assign_details();
$message->display();

$template->assign_vars(array(
	'S_POST_ACTION'				=> titania::$contrib->get_url('manage'),

	'ERROR_MSG'					=> ($submit && sizeof($error)) ? implode('<br />', $error) : false,
));

titania::page_header('MANAGE_CONTRIBUTION');
titania::page_footer(true, 'contributions/contribution_manage.html');