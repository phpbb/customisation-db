<?php
/**
 *
 * @package titania
 * @version $Id$
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

class authors_main_info
{
	function module()
	{
		return array(
			'modes'		=> array(
				'list'			=> array('title' => 'AUTHORS_LIST', 'auth' => ''),
				'profile'		=> array('title' => 'AUTHOR_PROFILE', 'auth' => ''),
				'search'		=> array('title' => 'AUTHOR_SEARCH', 'auth' => ''),
			),
		);
	}
}

?>