<?php
/**
 *
 * @package titania
 * @version $Id$
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

class contribs_details_info
{
	function module()
	{
		return array(
			'modes'		=> array(
				'details'		=> array('title' => 'CONTRIB_DETAILS', 'auth' => ''),
				'screenshots'	=> array('title' => 'CONTRIB_SCREENSHOTS', 'auth' => ''),
				'preview'		=> array('title' => 'CONTRIB_PREVIEW', 'auth' => ''),
				'changes'		=> array('title' => 'CONTRIB_CHANGES', 'auth' => ''),
				'email'			=> array('title' => 'CONTRIB_EMAIL_FRIEND', 'auth' => ''),
				'styles'		=> array('title' => 'CONTRIB_STYLES', 'auth' => ''),
				'translations'	=> array('title' => 'CONTRIB_TRANSLATIONS', 'auth' => ''),
			),
		);
	}
}
