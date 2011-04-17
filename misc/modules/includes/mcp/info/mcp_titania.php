<?php
/**
*
* @package Titania
* @version $Id: mcp_titania.php 1071 2010-04-17 05:10:36Z exreaction $
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package module_install
*/
class mcp_titania_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_titania',
			'title'		=> 'MCP_TITANIA',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'attention'			=> array('title' => 'MCP_TITANIA_ATTENTION', 'auth' => 'acl_u_titania_mod_author_mod || acl_u_titania_mod_contrib_mod || acl_u_titania_mod_faq_mod || acl_u_titania_mod_post_mod', 'cat' => array('MCP_MAIN')),
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