<?php
/**
 *
 * @package titania
 * @version $Id$
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

class contribs_faq_info
{
	function module()
	{
		return array(
			'modes'		=> array(
				'faq'			=> array('title' => 'CONTRIB_FAQ', 'auth' => ''),
			),
		);
	}
}
