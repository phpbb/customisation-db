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
 * @todo This file shouldn't be defining the PHPBB_ROOT_PATH I think.
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
include(TITANIA_ROOT . 'common.' . $phpEx);
include(TITANIA_ROOT . 'class_review.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

echo '<pre>';

// Inserting
$review = new titania_review();
$review->set_review_user_id(2);
$review->set_review_rating(5);
$review->set_review_text('[b]Hehe blah moek[/b] :-)');
$review->generate_text_for_storage(true, true, true);
$review->submit();

// Getting
/*$review = new titania_review(1);
$review->load();*/

// Updating
/*$review = new titania_review(2);
$review->load();
$review->set_review_text('[b]Blub blub[/b] :-P');
$review->set_review_status(0);
$review->generate_text_for_storage(true, true, true);
$review->submit();*/

// Deleting
/*$review = new titania_review(1);
$review->delete();*/

echo $review->get_review_text();

//var_dump($review);

$mtime = explode(' ', microtime());
$totaltime = $mtime[0] + $mtime[1] - $starttime;
echo "\n\n" . sprintf('Time : %.3fs | ' . $db->sql_num_queries() . ' Queries', $totaltime);

echo '</pre>';