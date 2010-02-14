<?php
/**
*
* @package Titania
* @version $Id: subscriptions.php SyntaxError90 $
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_TITANIA'))
{
	exit;
}

// These lines can be removed/commented out when we get it integrated.
define('SUBSCRIPTION_EMAIL', 1);
define('SUBSCRIPTION_WATCH', 2);

class titania_subscriptions
{
	/*
	* Subscribe
	*/
	public static function subscribe($object_type, $object_id, $user_id, $subscription_type = SUBSCRIPTION_EMAIL)
	{
		// We are just going to force one or the other on them.
		$subscription_type = ($subscription_type == SUBSCRIPTION_EMAIL) ? SUBSCRIPTION_EMAIL : SUBSCRIPTION_WATCH;

		$sql = 'SELECT watch_object_id FROM ' . TITANIA_WATCH_TABLE . ' WHERE ' . phpbb::$db->sql_build_array('SELECT', array(
			'watch_object_type'		=> (int) $object_type,
			'watch_type'			=> (int) $subscription_type,
			'watch_object_id'		=> (int) $object_id,
			'watch_user_id'			=> (int) $user_id,
		));
		
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);
		
		// If they are already subscribed, send them out.
		if($row)
		{
			return false;
		}
		
		// Build an insert
		$sql = 'INSERT INTO ' . TITANIA_WATCH_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', array(
			'watch_object_type'		=> (int) $object_type,
			'watch_type'			=> (int) $subscription_type,
			'watch_object_id'		=> (int) $object_id,
			'watch_user_id'			=> (int) $user_id,
			'watch_mark_time'		=> time(),
		));
		
		// Query and we're done
		phpbb::$db->sql_query($sql);
		
		$sql = 'SELECT contrib_name 
			FROM ' . TITANIA_CONTRIBS_TABLE . '
			WHERE contrib_id = ' . (int) $object_id;
		phpbb::$db->sql_query($sql);
		
		$messenger->template('subscribe_conf', 'en'); // Forcing English
		$messenger->to(phpbb::$user->data['user_email'], phpbb::$user->data['username']);
		
		$messenger->assign_vars(array(
			'SUBJECT'   	=> $row['contrib_name'],
			'USERNAME'		=> phpbb::$user->data['username'],
		));
		
		$messenger->send();		
		
		return true;
	}
	
	/*
	* Unsubscribe
	*/
	public static function unsubscribe($object_type, $object_id, $user_id, $subscription_type = SUBSCRIPTION_EMAIL)
	{
		// We are just going to force one or the other on them.
		$subscription_type = ($subscription_type == SUBSCRIPTION_EMAIL) ? SUBSCRIPTION_EMAIL : SUBSCRIPTION_WATCH;
		
		$sql = 'SELECT watch_object_id FROM ' . TITANIA_WATCH_TABLE . ' WHERE ' . phpbb::$db->sql_build_array('SELECT', array(
			'watch_object_type'		=> (int) $object_type,
			'watch_type'			=> (int) $subscription_type,
			'watch_object_id'		=> (int) $object_id,
			'watch_user_id'			=> (int) $user_id,
		));
		
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);
		
		// If they are NOT already subscribed, send them out.
		if(!$row)
		{
			return false;
		}
		
		// Get our delete query
		$sql = 'DELETE FROM ' . TITANIA_WATCH_TABLE . "
				WHERE watch_type = $subscription_type 
				AND watch_object_id = " . (int) $object_id . '
				AND watch_user_id = ' .(int) $user_id . '
				AND watch_object_type = ' . (int) $object_type;
		
		// Query and we're done
		phpbb::$db->sql_query($sql);
		
		$sql = 'SELECT contrib_name 
			FROM ' . TITANIA_CONTRIBS_TABLE . '
			WHERE contrib_id = ' . (int) $object_id;
		phpbb::$db->sql_query($sql);
		
		$messenger->template('subscribe_remove', 'en'); // Forcing English
		$messenger->to(phpbb::$user->data['user_email'], phpbb::$user->data['username']);
		
		$messenger->assign_vars(array(
			'SUBJECT'   	=> $row['contrib_name'],
			'USERNAME'		=> phpbb::$user->data['username'],
		));
		
		$messenger->send();
		
		return true;
	}
	
	/*
	* Send Notifications
	*/	
	public static function send_notifications($object_type, $object_id, $url)
	{
		$sql = 'SELECT w.watch_user_id, u.user_id, u.username, u.user_email, c.contrib_name
				FROM ' . TITANIA_WATCH_TABLE . ' w, ' . USERS_TABLE . ' u, ' . TITANIA_CONTRIBS_TABLE . ' c
				WHERE w.watch_user_id = u.user_id
				AND w.watch_object_id = c.contrib_id
				AND w.watch_object_type = ' . (int) $object_type . '
				AND w.watch_object_id = ' . (int) $object_id . '
				AND w.watch_type = ' . SUBSCRIPTION_EMAIL;
		
		$result = phpbb::$db->sql_query($sql);
		
		// Throw everything here
		$user_data = array();
		while($row = phpbb::$db->sql_fetchrow($result))
		{
			$user_data[] = array(
				'username'	=> $row['username'],
				'user_email'=> $row['user_email'],
			);
		}
		
		// No one subscribed? We're done.
		if(!sizeof($user_data))
		{
			return;
		}
		
		$messenger = new messenger();
		
		// Send to each user
		foreach($user_data as $data)
		{
			$messenger->template('subscribe_notify', 'en'); // Forcing English
			// $messenger->from('','');
			$messenger->to($data['user_email'], $data['username']);
			
			$messenger->assign_vars(array(
				'TYPE'   			=> titania_types::$types[$data['contrib_type']]->lang,
				'CONTRIBNAME'		=> $data['contrib_name'], 
				'USERNAME'			=> $data['username'],
			//	'EMAIL_SIG'			=> '',
				
				'U_CONTRIB_OVERVIEW'	=> $url,
			));
			
			$messenger->send();
		}
		
		return;
	}
}

?>