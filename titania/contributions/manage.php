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
if (!defined('IN_TITANIA'))
{
	exit;
}

titania::_include('functions_posting', 'generate_type_select');

load_contrib();

// Used later when submitting
$contrib_clone = clone titania::$contrib;

if (!((((titania::$contrib->is_author || titania::$contrib->is_active_coauthor) && phpbb::$auth->acl_get('u_titania_post_edit_own')) && !in_array(titania::$contrib->contrib_status, array(TITANIA_CONTRIB_CLEANED, TITANIA_CONTRIB_DISABLED))) || titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate')))
{
	titania::needs_auth();
}

// Set some main vars up
$submit = (isset($_POST['submit'])) ? true : false;
$change_owner = request_var('change_owner', '', true); // Blame Nathan, he said this was okay
$contrib_categories = request_var('contrib_category', array(0));
$contrib_demo = utf8_normalize_nfc(request_var('demo_url', '', true));
$active_coauthors = $active_coauthors_list = utf8_normalize_nfc(request_var('active_coauthors', '', true));
$nonactive_coauthors = $nonactive_coauthors_list = utf8_normalize_nfc(request_var('nonactive_coauthors', '', true));
$error = array();
$contrib_status = request_var('contrib_status', (int) titania::$contrib->contrib_status);
$status_list = array(
	TITANIA_CONTRIB_NEW					=> 'CONTRIB_NEW',
	TITANIA_CONTRIB_APPROVED			=> 'CONTRIB_APPROVED',
	TITANIA_CONTRIB_DOWNLOAD_DISABLED	=> 'CONTRIB_DOWNLOAD_DISABLED',
	TITANIA_CONTRIB_CLEANED				=> 'CONTRIB_CLEANED',
	TITANIA_CONTRIB_HIDDEN				=> 'CONTRIB_HIDDEN',
	TITANIA_CONTRIB_DISABLED			=> 'CONTRIB_DISABLED',
);
$permalink = utf8_normalize_nfc(request_var('permalink', titania::$contrib->contrib_name_clean, true));

/**
* ---------------------------- Confirm main author change ----------------------------
*/
if (titania::confirm_box(true))
{
	if (!titania::$contrib->is_author && !titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate'))
	{
		titania::needs_auth();
	}

	$change_owner_id = request_var('change_owner_id', 0);

	if ($change_owner_id !== ANONYMOUS && $change_owner_id)
	{
		titania::$contrib->set_contrib_user_id($change_owner_id);

		titania::$contrib->load(utf8_normalize_nfc(request_var('c', '', true))); // Reload the contrib (to make sure the authors list is updated)
		$submit = false; // Set submit as false to keep the main stuff from being resubmitted again

		redirect(titania::$contrib->get_url());
	}
}

/**
* ---------------------------- Main Page ----------------------------
*/

// Load the message object
$message = new titania_message(titania::$contrib);
$message->set_auth(array(
	'bbcode'		=> phpbb::$auth->acl_get('u_titania_bbcode'),
	'smilies'		=> phpbb::$auth->acl_get('u_titania_smilies'),
	'edit_subject'	=> (titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate')),
));
$message->set_settings(array(
	'display_error'		=> false,
	'display_subject'	=> false,
	'subject_name'		=> 'name',
));

// Screenshots
$screenshot = new titania_attachment(TITANIA_SCREENSHOT, titania::$contrib->contrib_id);
$screenshot->load_attachments();
$screenshot->upload(175);
$error = array_merge($error, $screenshot->error);

if ($screenshot->uploaded || isset($_POST['preview']) || $submit)
{
	titania::$contrib->post_data($message);
	titania::$contrib->__set_array(array(
		'contrib_demo'			=> (titania::$config->can_modify_style_demo_url || titania_types::$types[TITANIA_TYPE_STYLE]->acl_get('moderate') || titania::$contrib->contrib_type != TITANIA_TYPE_STYLE) ? $contrib_demo : titania::$contrib->contrib_demo,
		'contrib_local_name' => utf8_normalize_nfc(request_var('contrib_local_name', '', true)),
		'contrib_iso_code' => request_var('contrib_iso_code', ''),
	));
}

// ColorizeIt sample
if(strlen(titania::$config->colorizeit) && titania_types::$types[titania::$contrib->contrib_type]->acl_get('colorizeit'))
{
    $clr_sample = new titania_attachment(TITANIA_CLR_SCREENSHOT, titania::$contrib->contrib_id);
    $clr_sample->load_attachments();
    $clr_sample->upload();
    $error = array_merge($error, $clr_sample->error);
    if ($clr_sample->uploaded || isset($_POST['preview']) || $submit)
    {
        titania::$contrib->post_data($message);
    }
}

if (isset($_POST['preview']))
{
	$message->preview();
}
else if ($submit)
{
	// Handle the deletion routine
	if (isset($_POST['delete']) && phpbb::$auth->acl_get('u_titania_admin'))
	{
		titania::$contrib->delete();

		redirect(titania_url::build_url(''));
	}

	titania::$contrib->post_data($message);

	// Begin Error checking
	$error = array_merge($error, titania::$contrib->validate($contrib_categories));

	if (($validate_form_key = $message->validate_form_key()) !== false)
	{
		$error[] = $validate_form_key;
	}

	$missing_active = $missing_nonactive = array();
	get_author_ids_from_list($active_coauthors_list, $missing_active);
	get_author_ids_from_list($nonactive_coauthors_list, $missing_nonactive);
	$author_username = users_overlord::get_user(titania::$contrib->contrib_user_id, 'username', true);
	$author_username_clean = users_overlord::get_user(titania::$contrib->contrib_user_id, 'username_clean', true);
	if (sizeof($missing_active) || sizeof($missing_nonactive))
	{
		$error[] = sprintf(phpbb::$user->lang['COULD_NOT_FIND_USERS'], implode(', ', array_merge($missing_active, $missing_nonactive)));
	}
	if (array_intersect($active_coauthors_list, $nonactive_coauthors_list))
	{
		$error[] = sprintf(phpbb::$user->lang['DUPLICATE_AUTHORS'], implode(', ', array_keys(array_intersect($active_coauthors_list, $nonactive_coauthors_list))));
	}
	if (isset($active_coauthors_list[$author_username]) || isset($active_coauthors_list[$author_username_clean]) || isset($nonactive_coauthors_list[$author_username]) || isset($nonactive_coauthors_list[$author_username_clean]))
	{
		$error[] = phpbb::$user->lang['CANNOT_ADD_SELF_COAUTHOR'];
	}
	if ($contrib_demo && !preg_match('#^http[s]?://(.*?\.)*?[a-z0-9\-]+\.[a-z]{2,4}#i', $contrib_demo))
	{
		$error[] = phpbb::$user->lang['WRONG_DATA_WEBSITE'];
	}

	if ($change_owner != '')
	{
		$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . "
				WHERE username_clean = '" . phpbb::$db->sql_escape(utf8_clean_string($change_owner)) . "'";
			$result = phpbb::$db->sql_query($sql);
			$change_owner_id = (int) phpbb::$db->sql_fetchfield('user_id');
			phpbb::$db->sql_freeresult($result);

		if ($change_owner_id < 1)
		{
			$error[] = sprintf(phpbb::$user->lang['CONTRIB_CHANGE_OWNER_NOT_FOUND'], $change_owner);
		}
	}

	// Changed permalink?
	if ($permalink != titania::$contrib->contrib_name_clean)
	{
		// We check permalink
		if (!$permalink)
		{
			// If they leave it blank automatically create it
			$permalink = titania_url::url_slug(titania::$contrib->contrib_name);

			$append = '';
			$i = 2;
			while (titania::$contrib->validate_permalink($permalink . $append) == false)
			{
				$append = '_' . $i;
				$i++;
			}

			$permalink = $permalink . $append;
		}
		elseif (titania_url::url_slug($permalink) !== $permalink)
		{
			$error[] = sprintf(phpbb::$user->lang['INVALID_PERMALINK'], titania_url::url_slug($permalink));
		}
		elseif (!titania::$contrib->validate_permalink($permalink))
		{
			$error[] = phpbb::$user->lang['CONTRIB_NAME_EXISTS'];
		}
	}

	// Did we succeed or have an error?
	if (!sizeof($error))
	{
		// Check for changes in the description or categories to file a report
		if (!titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate'))
		{
			$attention_message = array();

			// Changed description?
			$old_description = $contrib_clone->generate_text_for_edit();
			$old_description = $old_description['text'];

			$description = titania::$contrib->generate_text_for_edit();
			$description = $description['text'];

			if ($old_description != $description)
			{
				$attention_message[] = sprintf(phpbb::$user->lang['ATTENTION_CONTRIB_DESC_CHANGED'], $old_description, $description);
			}

			// Changed categories?
			$old_contrib_categories = array();
			$sql = 'SELECT category_id
				FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . '
				WHERE contrib_id = ' . titania::$contrib->contrib_id;
			$result = phpbb::$db->sql_query($sql);
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$old_contrib_categories[] = $row['category_id'];
			}
			phpbb::$db->sql_freeresult($result);

			if (sizeof(array_diff($old_contrib_categories, $contrib_categories)) || sizeof(array_diff($contrib_categories, $old_contrib_categories)))
			{
				$categories_ary = titania::$cache->get_categories();

				$old_category_names = $category_names = array();
				foreach ($old_contrib_categories as $category_id)
				{
					$old_category_names[] = (isset(phpbb::$user->lang[$categories_ary[$category_id]['category_name']])) ? phpbb::$user->lang[$categories_ary[$category_id]['category_name']] : $categories_ary[$category_id]['category_name'];
				}
				foreach ($contrib_categories as $category_id)
				{
					$category_names[] = (isset(phpbb::$user->lang[$categories_ary[$category_id]['category_name']])) ? phpbb::$user->lang[$categories_ary[$category_id]['category_name']] : $categories_ary[$category_id]['category_name'];
				}
				$attention_message[] = sprintf(phpbb::$user->lang['ATTENTION_CONTRIB_CATEGORIES_CHANGED'], implode("\n", $old_category_names), implode("\n", $category_names));
			}

			if (sizeof($attention_message))
			{
				titania::$contrib->report(nl2br(implode("\n\n", $attention_message)));
			}
		}

		// Submit screenshots
		$screenshot->submit();
		
		// ColorizeIt stuff
        if(strlen(titania::$config->colorizeit) && titania_types::$types[titania::$contrib->contrib_type]->acl_get('colorizeit'))
        {
            $clr_sample->submit();
            $contrib_clr_colors = utf8_normalize_nfc(request_var('change_colors', titania::$contrib->contrib_clr_colors));
            titania::$contrib->__set('contrib_clr_colors', $contrib_clr_colors);
        }

		// Update contrib_status/permalink if we can moderate. only if contrib_status is valid and permalink altered
		if (titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate'))
		{
			if (array_key_exists($contrib_status, $status_list))
			{
				titania::$contrib->change_status($contrib_status);
			}

			if ($permalink != titania::$contrib->contrib_name_clean)
			{
				titania::$contrib->change_permalink($permalink);
			}
		}

		// Submit the changes
		titania::$contrib->submit();

		// Set the coauthors
		titania::$contrib->set_coauthors($active_coauthors_list, $nonactive_coauthors_list, true);

		// Create relations
		titania::$contrib->put_contrib_in_categories($contrib_categories);

		// Update the release topic
		titania::$contrib->update_release_topic();

		if ($change_owner == '')
		{
			redirect(titania::$contrib->get_url());
		}
		else
		{
			$s_hidden_fields = array(
				'submit'			=> true,
				'change_owner'		=> $change_owner,
				'change_owner_id'	=> $change_owner_id,
			);

			titania::confirm_box(false, sprintf(phpbb::$user->lang['CONTRIB_CONFIRM_OWNER_CHANGE'], '<a href="' .  phpbb::append_sid('memberlist', 'mode=viewprofile&amp;u=' . $change_owner_id) . '">' . $change_owner . '</a>'), titania_url::append_url(titania_url::$current_page), $s_hidden_fields);
		}
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

	$active_coauthors = $nonactive_coauthors = array();
	foreach (titania::$contrib->coauthors as $row)
	{
		// User does not exist anymore...
		if (users_overlord::get_user($row['user_id'], 'user_id') != $row['user_id'])
		{
			continue;
		}

		if ($row['active'])
		{
			$active_coauthors[] = users_overlord::get_user($row['user_id'], 'username');
		}
		else
		{
			$nonactive_coauthors[] = users_overlord::get_user($row['user_id'], 'username');
		}
	}
	$active_coauthors = implode("\n", $active_coauthors);
	$nonactive_coauthors = implode("\n", $nonactive_coauthors);
}

// Generate some stuff
generate_category_select($contrib_categories);
titania::$contrib->assign_details();
$message->display();

foreach ($status_list as $status => $row)
{
	phpbb::$template->assign_block_vars('status_select', array(
		'S_SELECTED'		=> ($status == titania::$contrib->contrib_status) ? true : false,
		'VALUE'				=> $status,
		'NAME'				=> phpbb::$user->lang[$row],
	));
}

// ColorizeIt
if(strlen(titania::$config->colorizeit) && titania_types::$types[titania::$contrib->contrib_type]->acl_get('colorizeit'))
{
    $clr_testsample = '';
    if(titania::$contrib->has_colorizeit(true) || is_array(titania::$contrib->clr_sample))
    {
        $clr_testsample = 'http://' . titania::$config->colorizeit_url . '/testsample.html?sub=' . titania::$config->colorizeit . '&amp;sample=' . urlencode(titania_url::build_url('download', array('id' => titania::$contrib->clr_sample['attachment_id'])));
    }
    phpbb::$template->assign_vars(array(
        'MANAGE_COLORIZEIT'         => titania::$config->colorizeit,
        'CLR_SCREENSHOTS'           => $clr_sample->parse_uploader('posting/attachments/simple.html'),
        'CLR_COLORS'                => htmlspecialchars(titania::$contrib->contrib_clr_colors),
        'U_TESTSAMPLE'              => $clr_testsample,
    ));
}

phpbb::$template->assign_vars(array(
	'S_POST_ACTION'				=> titania::$contrib->get_url('manage'),
	'S_EDIT_SUBJECT'			=> (titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate')) ? true : false,
	'S_DELETE_CONTRIBUTION'		=> (phpbb::$auth->acl_get('u_titania_admin')) ? true : false,
	'S_IS_OWNER'				=> (titania::$contrib->is_author) ? true : false,
	'S_IS_MODERATOR'			=> (titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate')) ? true : false,
	'S_CAN_EDIT_STYLE_DEMO'		=> (titania::$config->can_modify_style_demo_url || titania_types::$types[TITANIA_TYPE_STYLE]->acl_get('moderate') || titania::$contrib->contrib_type != TITANIA_TYPE_STYLE) ? true : false,
	'S_CAN_EDIT_CONTRIB'		=> (phpbb::$auth->acl_get('u_titania_contrib_submit')) ? true : false,

	'CONTRIB_PERMALINK'			=> $permalink,
	'SCREENSHOT_UPLOADER'		=> (phpbb::$auth->acl_get('u_titania_contrib_submit')) ? $screenshot->parse_uploader('posting/attachments/simple.html') : false,
	'ERROR_MSG'					=> (sizeof($error)) ? implode('<br />', $error) : false,
	'ACTIVE_COAUTHORS'			=> $active_coauthors,
	'NONACTIVE_COAUTHORS'		=> $nonactive_coauthors,

	'S_TRANSLATION_TYPE_ID'		=> (defined('TITANIA_TYPE_TRANSLATION')) ? TITANIA_TYPE_TRANSLATION : 0,
));

titania::page_header(titania::$contrib->contrib_name . ' - ' . phpbb::$user->lang['MANAGE_CONTRIBUTION']);
titania::page_footer(true, 'contributions/contribution_manage.html');
