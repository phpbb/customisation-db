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
if (!defined('IN_TITANIA'))
{
	exit;
}

class titania_type_mod
{
	public static function get_type_data()
	{
		return array(
			'type_name'				=> phpbb::$user->lang['MODIFICATION'],
			'type_slug' 			=> 'mod',
			'author_count_field' 	=> 'author_mods',
		);

	}
}