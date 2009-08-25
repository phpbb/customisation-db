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

//@todo Logged in users only.

titania::add_lang('attachments');
titania::load_object('contribution');

load_contrib();

$submit = (isset($_POST['submit'])) ? true : false;

titania::load_object('attachments');
$attachment = new titania_attachments(TITANIA_DOWNLOAD_CONTRIB);

$contrib_categories = array();

if ($submit)
{
	titania::$contrib->contrib_name 		= utf8_normalize_nfc(request_var('name', '', true));
	titania::$contrib->contrib_desc 		= utf8_normalize_nfc(request_var('description', '', true));
	$contrib_categories						= request_var('contrib_category', array(0));
	titania::$contrib->contrib_type			= request_var('contrib_type', 0);
	
	$error = titania::$contrib->validate_data($contrib_categories);

	if (!sizeof($error))
	{
		titania::$contrib->submit();

		// Create relations
		titania::$contrib->put_contrib_in_categories($contrib_categories, true);

		// Update are attachments.
		$attachment->update_orphans(titania::$contrib->contrib_id);

		meta_refresh(3, titania::$contrib->get_url());

		titania::error_box('SUCCESS', 'CONTRIB_EDITED', TITANIA_SUCCESS);
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

// Generate the selects
generate_type_select(titania::$contrib->contrib_type);
generate_category_select($contrib_categories);

$template->assign_vars(array(
	'U_ACTION'					=> titania::$contrib->get_url('edit'),

	'ERROR_MSG'					=> ($submit && sizeof($error)) ? implode('<br />', $error) : false,

	'CONTRIB_NAME'				=> titania::$contrib->contrib_name,
	'CONTRIB_DESC'				=> titania::$contrib->contrib_desc,
));

titania::page_header('EDIT_CONTRIBUTION');
titania::page_footer(true, 'contributions/contribution_edit.html');