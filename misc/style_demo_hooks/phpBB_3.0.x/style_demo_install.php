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

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/style_demo_hook.' . $phpEx);
include($phpbb_root_path . 'includes/style_demo_manager.' . $phpEx);

$hook = new titania_style_demo_hook($config, $db, $user, $phpbb_root_path, $phpEx);
$result = $hook->run(request_var('key', ''));

echo json_encode($result);
garbage_collection();
exit_handler();
