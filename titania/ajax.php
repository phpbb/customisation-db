<?php
/**
 *
 * @package Titania
 * @version $Id$
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
* @ignore
*/
define('IN_TITANIA', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;

// application/xhtml+xml not used because of IE
header('Content-type: text/html; charset=UTF-8');

header('Cache-Control: private, no-cache="set-cookie"');
header('Expires: 0');
header('Pragma: no-cache');

$action = request_var('action', '');

switch ($action)
{
	/**
	* Quick Edit
	*/
	case 'quick_edit' :
		phpbb::$user->add_lang('viewtopic');

		$post_id = request_var('p', 0);

		posts_overlord::load_post($post_id);
		$post = posts_overlord::get_post_object($post_id);

		if (!$post || !$post->acl_get('edit'))
		{
			ajax_error('NO_POST');
		}

		$post_message = $post->post_text;
		titania_decode_message($post_message, $post->post_text_uid);

		add_form_key('postform');

		phpbb::$template->assign_vars(array(
			'MESSAGE'		=> $post_message,

			'U_QR_ACTION'	=> $post->get_url('quick_edit'),
		));

		phpbb::$template->set_filenames(array(
			'quick_edit'	=> 'posting/quickedit_editor.html'
		));

		phpbb::$template->display('quick_edit');
	break;
}

garbage_collection();
exit_handler();

function ajax_error($error)
{
	echo phpbb::$user->lang['ERROR'] . ': ' . ((isset(phpbb::$user->lang[$error])) ? phpbb::$user->lang[$error] : $error);

	garbage_collection();
	exit_handler();
}