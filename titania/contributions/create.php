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

//@todo Logged in users only.

titania::add_lang('attachments');
titania::load_object('contribution');

$contrib = new titania_contribution();

$submit = (isset($_POST['submit'])) ? true : false;

titania::load_object('attachments');
$attachment = new titania_attachments(TITANIA_DOWNLOAD_CONTRIB, $contrib->contrib_id);

$contrib->contrib_name 			= utf8_normalize_nfc(request_var('name', '', true));
$contrib->contrib_desc 			= utf8_normalize_nfc(request_var('description', '', true));
$contrib->contrib_categories	= request_var('contrib_category', array(0));
$contrib->contrib_type			= request_var('contrib_type', 0);
$contrib->contrib_name_clean	= request_var('permalink', '');

if ($submit)
{
	$error = $contrib->validate_data();
	
	if (!sizeof($error))
	{
		// Temporary
		$contrib->contrib_visible = TITANIA_STATUS_APPROVED;
		
		$contrib->submit();

		// Create relations
		$contrib->put_contrib_in_categories();
		
		// Update are attachments.
		$attachment->update_orphans($contrib->contrib_id);

		meta_refresh(3, $contrib->get_url());

		titania::error_box('SUCCESS', 'CONTRIB_CREATED', TITANIA_SUCCESS);
	}
}

// Generate the selects
$contrib->generate_type_select($contrib->contrib_type);
$contrib->generate_category_select($contrib->contrib_categories);

$for_edit = $contrib->generate_text_for_edit();

$template->assign_vars(array(
	'U_ACTION'					=> titania::$url->build_url('contributions/create'),

	'ERROR_MSG'					=> (sizeof($error)) ? implode('<br />', $error) : false,

	'CONTRIB_NAME'				=> $contrib->contrib_name,
	'CONTRIB_PERMALINK'			=> $contrib->contrib_name_clean,
	'CONTRIB_DESC'				=> $for_edit['text'],
));

titania::page_header('CREATE_CONTRIBUTION');
titania::page_footer(true, 'contributions/contribution_create.html');