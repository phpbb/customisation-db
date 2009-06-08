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

/**
* Titania append_sid function.  Why?  Because this is easier. :P
*
* @param mixed $page What you would put between TITANIA_ROOT and '.' . PHP_EXT (if this doesn't work for you, use append_sid!)
* @param mixed $params Same as append_sid
* @param mixed $is_amp Same as append_sid
* @param mixed $session_id Same as append_sid
* @return string Same as append_sid
*/
function titania_sid($page, $params = false, $is_amp = true, $session_id = false)
{
	return append_sid(TITANIA_ROOT . $page . '.' . PHP_EXT, $params = false, $is_amp = true, $session_id = false);
}