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

if (!phpbb::$auth->acl_get('titania_contrib_submit'))
{
	trigger_error('NO_AUTH');
}

titania::load_object(array('contribution', 'author'));

titania::$contrib = new titania_contribution();

titania::$contrib->contrib_user_id = phpbb::$user->data['user_id'];
titania::$contrib->author = new titania_author(phpbb::$user->data['user_id']);
titania::$contrib->author->load();

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
$active_coauthors = $nonactive_coauthors = '';

if ($submit)
{
	$post_data = $message->request_data();

	titania::$contrib->post_data($post_data);
	$contrib_categories = request_var('contrib_category', array(0));
	titania::$contrib->__set_array(array(
		'contrib_type'			=> request_var('contrib_type', 0),
		'contrib_name_clean'	=> request_var('permalink', '', true),
		'contrib_visible'		=> 1,
	));

	$error = titania::$contrib->validate($contrib_categories);

	if (($validate_form_key = $message->validate_form_key()) !== false)
	{
		$error[] = $validate_form_key;
	}

	$missing_active = $missing_nonactive = array();
	$active_coauthors = $active_coauthors_list = utf8_normalize_nfc(request_var('active_coauthors', '', true));
	$nonactive_coauthors = $nonactive_coauthors_list = utf8_normalize_nfc(request_var('nonactive_coauthors', '', true));
	get_author_ids_from_list($active_coauthors_list, $missing_active);
	get_author_ids_from_list($nonactive_coauthors_list, $missing_nonactive);
	if (sizeof($missing_active) || sizeof($missing_nonactive))
	{
		$error[] = sprintf(phpbb::$user->lang['COULD_NOT_FIND_USERS'], implode(', ', array_merge($missing_active, $missing_nonactive)));
	}
	if (array_intersect($active_coauthors_list, $nonactive_coauthors_list))
	{
		$error[] = sprintf(phpbb::$user->lang['DUPLICATE_AUTHORS'], implode(', ', array_keys(array_intersect($active_coauthors_list, $nonactive_coauthors_list))));
	}
	if (isset($active_coauthors_list[phpbb::$user->data['username']]) || isset($nonactive_coauthors_list[phpbb::$user->data['username']]))
	{
		$error[] = phpbb::$user->lang['CANNOT_ADD_SELF_COAUTHOR'];
	}

	if (!sizeof($error))
	{
		titania::$contrib->submit();

		titania::$contrib->set_coauthors($active_coauthors_list, $nonactive_coauthors_list, true);

		// Create relations
		titania::$contrib->put_contrib_in_categories($contrib_categories);

		// Update are attachments.
		$attachment->update_orphans(titania::$contrib->contrib_id);

		redirect(titania::$contrib->get_url());
	}
}

// Generate some stuff
generate_type_select(titania::$contrib->contrib_type);
generate_category_select($contrib_categories);
titania::$contrib->assign_details();
$message->display();

$template->assign_vars(array(
	'S_POST_ACTION'			=> titania::$url->build_url('contributions/create'),
	'S_CREATE'				=> true,

	'CONTRIB_PERMALINK'		=> titania::$contrib->contrib_name_clean,
	'ERROR_MSG'				=> ($submit && sizeof($error)) ? implode('<br />', $error) : false,
	'ACTIVE_COAUTHORS'		=> $active_coauthors,
	'NONACTIVE_COAUTHORS'	=> $nonactive_coauthors,
));

titania::page_header('CREATE_CONTRIBUTION');
titania::page_footer(true, 'contributions/contribution_manage.html');