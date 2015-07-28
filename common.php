<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

global $phpbb_container;

$ext_root_path = $phpbb_container->getParameter('phpbb.titania.root_path');
$php_ext = $phpbb_container->getParameter('core.php_ext');

// Include the non-dynamic constants
require($ext_root_path . 'includes/constants.' . $php_ext);

// Include core classes
require($ext_root_path . 'includes/core/phpbb.' . $php_ext);
require($ext_root_path . 'includes/core/titania.' . $php_ext);

titania::configure(
	$phpbb_container->get('phpbb.titania.config'),
	$ext_root_path,
	$php_ext
);

// Include our core functions
titania::_include('functions');

// Set up our auto-loader
spl_autoload_register(array('titania', 'autoload'));

// Include the dynamic constants (after reading the Titania config file, but before loading the phpBB common file)
titania::_include('dynamic_constants');

// Initialise phpBB
phpbb::initialise();

// Initialise Titania
titania::initialise();
