<?php
/** 
*
* @package acp
* @version $Id: website_tags.php,v 1.1 2007/04/20 21:20:36 paul999 Exp $
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package module_install
*/
class website_tags_info
{
	function module()
	{
		return array(
			'filename'	=> 'website_tags',
			'title'		=> 'Ariel tags Management',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'manage'		=> array('title' => 'Manage Ariel Tags',  'auth' => 'acl_a_', 'cat' => array('Tags Management')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>
