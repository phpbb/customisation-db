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

include(TITANIA_ROOT . 'includes/functions_posting.' . PHP_EXT);
phpbb::$user->add_lang('posting');

$submit = (isset($_POST['submit'])) ? true : false;

add_form_key('titania_author');

if ($submit && !check_form_key('titania_author'))
{
	trigger_error('INVALID_FORM');
}

titania::$author->load();

if (titania::$author->user_id != phpbb::$user->data['user_id'] && !phpbb::$auth->acl_get('titania_author_mod'))
{
	trigger_error('NOT_AUTHORISED');
}

$errors = array();

if ($submit)
{
	titania::$author->author_realname   = utf8_normalize_nfc(request_var('realname', '', true));
	titania::$author->author_website    = request_var('website', '');
	titania::$author->author_desc       = utf8_normalize_nfc(request_var('message', '', true));

	if (!sizeof($errors))
	{
		// updating the data
		titania::$author->generate_text_for_storage(true, true, true); // @todo BBCode Permissions
		titania::$author->submit();

		// redirecting to the details page
		$redirect_url = titania::$author->get_url();
		meta_refresh(3, $redirect_url);

		titania::error_box('SUCCESS', 'AUTHOR_DATA_UPDATED', TITANIA_SUCCESS);
	}
}

$template->assign_vars(array(
	'S_POST_ACTION'		=> titania::$author->get_url('edit'),

	'ERROR_MSG'			=> (sizeof($errors)) ? implode('<br />', $errors) : false,

	'AUTHOR_REALNAME'	=> titania::$author->author_realname,
	'AUTHOR_WEBSITE'	=> titania::$author->author_website,

	'POSTING_FORM_NAME'			=> 'postform',
	'POSTING_TEXT_NAME'			=> 'message',
	'POSTING_MESSAGE_DEFAULT'	=> titania::$author->generate_text_for_edit(),
));

display_posting_bbcode_smilies(titania::$author->author_desc_options, true, true); // @todo BBCode permissions

titania::page_header('EDIT_AUTHOR');
titania::page_footer(true, 'authors/author_edit.html');