<?php
/**
 *
 * @package titania
 * @version $Id$
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

class mods_details_info
{
	function module()
	{
		return array(
			'modes'		=> array(
				'details'		=> array('title' => 'MODS_DETAILS', 'auth' => ''),
				'screenshots'	=> array('title' => 'MODS_SCREENSHOTS', 'auth' => ''),
				'preview'		=> array('title' => 'MODS_PREVIEW', 'auth' => ''),
				'changes'		=> array('title' => 'MODS_CHANGES', 'auth' => ''),
				'email'			=> array('title' => 'MODS_EMAIL_FRIEND', 'auth' => ''),
				'styles'		=> array('title' => 'MODS_STYLES', 'auth' => ''),
				'translations'	=> array('title' => 'MODS_TRANSLATIONS', 'auth' => ''),
			),
		);
	}
}

?>