<?php
/** 
*
* @package website
* @version $Id: website_ariel.php,v 1.1 2007/05/05 15:22:06 paul999 Exp $
* @copyright (c) 2005 phpBB Group 
* @license Not for redistribution 
*
*/

/**
* @package module_install
*/
class website_ariel_info
{
	function module()
	{
		return array(
			'filename'	=> 'website_ariel',
			'title'		=> 'Ariel configuration',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'global'		=> array('title' => 'Global config', 'auth' => 'acl_a_', 'cat' => array('Ariel configuration')),
				'mods'		=> array('title' => 'MODs config', 'auth' => 'acl_a_', 'cat' => array('Ariel configuration')),
				'styles'		=> array('title' => 'Styles config', 'auth' => 'acl_a_', 'cat' => array('Ariel configuration')),
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
