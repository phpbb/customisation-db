<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Group
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

if (!phpbb::$auth->acl_gets('u_titania_mod_author_mod', 'u_titania_mod_contrib_mod', 'u_titania_mod_faq_mod', 'u_titania_mod_post_mod') && !sizeof(titania_types::find_authed('moderate')))
{
	titania::needs_auth();
}

phpbb::$user->add_lang('mcp');

<<<<<<< HEAD
$valid_confirm_box = titania::confirm_box(true);

$attention_id = phpbb::$request->variable('a', 0);
$object_type = phpbb::$request->variable('type', 0);
$object_id = phpbb::$request->variable('id', 0);
=======
$attention_id = request_var('a', 0);
$object_type = request_var('type', 0);
$object_id = request_var('id', 0);
$disapprove_reason = request_var('disapprove_reason', 0);
$disapprove_explain = utf8_normalize_nfc(request_var('disapprove_explain', '', true));
>>>>>>> master

$close = phpbb::$request->is_set_post('close');
$approve = phpbb::$request->is_set_post('approve');
$disapprove = phpbb::$request->is_set_post('disapprove');
$delete = phpbb::$request->is_set_post('delete');

$submit = ($close || $approve || $delete || $disapprove) ? true : false;

if ($attention_id || ($object_type && $object_id))
{
	// Check the form token before doing anything
	if ($submit && !check_form_key('attention'))
	{
		trigger_error('FORM_INVALID');
	}

	$attention = attention_overlord::get_attention_object($attention_id, $object_type, $object_id);

	if (!$attention)
	{
		trigger_error('NO_ATTENTION_ITEM');
	}

	// Setup
	$object_type = (int) $attention->attention_object_type;
	$object_id = (int) $attention->attention_object_id;
	add_form_key('attention');

	if (!$attention->load_source_object())
	{
		$attention->delete();

		$error = array(
			TITANIA_POST	=> 'NO_POST',
			TITANIA_CONTRIB	=> 'NO_CONTRIB',
		);

		trigger_error($error[$object_type]);
	}

	if ($delete)
	{
		$attention->delete();
	}
	else if ($close)
	{
		$attention->report_handled();
	}

	switch ($object_type)
	{
		case TITANIA_POST :
			// Approve/disapprove the post
			if ($approve)
			{
				$attention->approve();
			}
			else if ($disapprove)
			{
				$result = false;

				if (titania::confirm_box(true))
				{
					$result = $attention->disapprove($disapprove_reason, $disapprove_explain);
				}

				if (!$result || $result === 'reason_empty')
				{
					if ($result)
					{
						phpbb::$template->assign_var('ADDITIONAL_MSG', phpbb::$user->lang['NO_REASON_DISAPPROVAL']);

						// Make sure we can reuse the confirm box
						unset($_REQUEST['confirm_key'], $_POST['confirm_key'], $_POST['confirm']);
					}

					phpbb::_include('functions_display', 'display_reasons');
					display_reasons($disapprove_reason);

					titania::confirm_box(false, 'DISAPPROVE_ITEM', '', array('disapprove' => true), 'manage/disapprove_body.html');
				}
			}

			$title = censor_text($attention->post->post_subject);
		break;

		case TITANIA_CONTRIB :
			$title = censor_text($attention->contrib->contrib_name);
		break;

		default :
			trigger_error('NO_ATTENTION_TYPE');
		break;
	}

	if ($submit)
	{
		if ($disapprove || $delete)
		{
			redirect(titania_url::build_url(titania_url::$current_page));
		}
		else
		{
			redirect(titania_url::build_url(titania_url::$current_page_url));
		}
	}

	// Display the current attention items
	$options = array(
		'attention_object_id'		=> $object_id,
		'exclude_attention_types'	=> TITANIA_ATTENTION_UNAPPROVED,
	);
	attention_overlord::display_attention_list($options);

	// Display the old (closed) attention items
	$options['only_closed'] = true;
	$options['template_block'] = 'attention_closed';
	$options['exclude_attention_types'] = false;

	attention_overlord::display_attention_list($options);

	$attention->assign_source_object_details();

	titania::page_header($title . ' - ' . phpbb::$user->lang['ATTENTION']);

	titania::page_footer(true, 'manage/attention_details.html');
}
else
{
	$type = phpbb::$request->variable('type', '');
	if (phpbb::$request->is_set_post('sort'))
	{
		$closed = phpbb::$request->is_set_post('closed');
		$open = (phpbb::$request->is_set_post('open') || !$closed) ? true : false;

		if ($open && $closed)
		{
			titania_url::$params['open'] = 1;
			titania_url::$params['closed'] = 1;
		}
		else if ($closed && !$open)
		{
			titania_url::$params['closed'] = 1;
		}
	}
	else
	{
		$closed = phpbb::$request->variable('closed', false);
		$open = (phpbb::$request->variable('open', false) || !$closed) ? true : false;
	}

	/*$close = (isset($_POST['close'])) ? true : false;
	$id_list = phpbb::$request->variable('id_list', array(0));

	if ($close && sizeof($id_list))
	{
		$attention_object = new titania_attention;
		foreach ($id_list as $attention_id)
		{
			$attention_object->attention_id = $attention_id;
			$attention_object->load();
		}
	}*/

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

	$options = array(
		'attention_type'	=> $type,
		'display_closed'	=> $closed,
		'only_closed'		=> (!$open && $closed) ? true : false,
	);
	attention_overlord::display_attention_list($options);

	phpbb::$template->assign_vars(array(
		'S_ACTION'			=> titania_url::build_url('manage/attention'),
		'S_OPEN_CHECKED'	=> $open,
		'S_CLOSED_CHECKED'	=> $closed,
	));

	// Subscriptions
	titania_subscriptions::handle_subscriptions(TITANIA_ATTENTION, 0, titania_url::build_url('manage/attention'));

	titania::page_header('ATTENTION');

	titania::page_footer(true, 'manage/attention.html');
}
