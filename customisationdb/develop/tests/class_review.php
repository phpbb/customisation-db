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
die("Yes. I'm dead.");

$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/titania/common.' . $phpEx);
include($phpbb_root_path . 'includes/titania/class_review.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

echo '<pre>';

// Inserting
$review = new titania_review();
$review->review_user_id = 2;
$review->review_rating = 5;
$review->submit();

// Getting
/*$review = new titania_review(1);*/

// Updating
/*$review = new titania_review(2);
$review->set_text('[b]Blub blub[/b] :-P');
$review->review_status = 0;
$review->generate_text_for_storage(true, true, true);
$review->submit();*/

//echo $review->get_text();

//var_dump($review);

$mtime = explode(' ', microtime());
$totaltime = $mtime[0] + $mtime[1] - $starttime;
echo "\n\n" . sprintf('Time : %.3fs | ' . $db->sql_num_queries() . ' Queries', $totaltime);

echo '</pre>';