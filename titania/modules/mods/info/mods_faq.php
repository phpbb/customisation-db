<?php
/**
 *
 * @package titania
 * @version $Id$
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

class mods_faq_info
{
	function module()
	{
		return array(
			'modes'		=> array(
				'faq'			=> array('title' => 'MODS_FAQ', 'auth' => ''),
				'manage'		=> array('title' => 'MODS_MANAGE_FAQ', 'auth' => ''),
				'view'			=> array('title' => 'MODS_VIEW_FAQ', 'auth' => ''),
			),
		);
	}
}

?>