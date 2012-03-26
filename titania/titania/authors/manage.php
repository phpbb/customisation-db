<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

titania::$author->load();

if (titania::$author->user_id != phpbb::$user->data['user_id'] && !phpbb::$auth->acl_get('u_titania_mod_author_mod'))
{
	trigger_error('NOT_AUTHORISED');
}

$message = new titania_message(titania::$author);
$message->set_auth(array(
	'bbcode'	=> phpbb::$auth->acl_get('u_titania_bbcode'),
	'smilies'	=> phpbb::$auth->acl_get('u_titania_smilies'),
));
$message->set_settings(array(
	'display_error'		=> false,
	'display_subject'	=> false,
));

$submit = (isset($_POST['submit'])) ? true : false;

if ($submit)
{
	titania::$author->post_data($message);

	titania::$author->__set_array(array(
		'author_realname'	=> utf8_normalize_nfc(request_var('realname', '', true)),
		'author_website'	=> request_var('website', ''),
	));

	$error = titania::$author->validate();

	if (($validate_form_key = $message->validate_form_key()) !== false)
	{
		$error[] = $validate_form_key;
	}

	if (!sizeof($error))
	{
		// Enhanced editor data is stored in the users table
		$titania_enhanced_editor = (isset($_POST['enhanced_editor'])) ? true : false;
		if (titania::$author->user_id == phpbb::$user->data['user_id'] && $titania_enhanced_editor != phpbb::$user->data['titania_enhanced_editor'])
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET titania_enhanced_editor = ' . (int) $titania_enhanced_editor . '
				WHERE user_id = ' . phpbb::$user->data['user_id'];
			phpbb::$db->sql_query($sql);
		}

		titania::$author->submit();

		redirect(titania::$author->get_url());
	}
}

titania::$author->assign_details();
$message->display();

$template->assign_vars(array(
	'S_POST_ACTION'				=> titania::$author->get_url('manage'),

	'S_DISPLAY_ENHANCED_EDITOR'			=> (titania::$author->user_id == phpbb::$user->data['user_id']) ? true : false,
	'S_ENHANCED_EDITOR'					=> phpbb::$user->data['titania_enhanced_editor'],

	'AUTHOR_WEBSITE'			=> (titania::$author->get_website_url() || phpbb::$user->data['user_id'] != titania::$author->user_id) ? titania::$author->get_website_url() : phpbb::$user->data['user_website'],

	'ERROR_MSG'					=> ($submit && sizeof($error)) ? implode('<br />', $error) : false,
));

titania::page_header(titania::$author->get_username_string('username') . ' - ' . phpbb::$user->lang['MANAGE_AUTHOR']);
titania::page_footer(true, 'authors/author_manage.html');
