<?php
/** 
*
* @package ariel
* @version $Id: functions_feed.php,v 1.8 2007/06/02 12:33:51 paul999 Exp $
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/** 
* @package ariel
*/
class feed
{	
	var $title = false;
	var $description = false;
	var $link = false;
	var $link2 = false;
	var $image; 
	var $template = false;
	var $type = 'rss';
	var $updated;

	/**
	* feed::feed()
	**/
	function feed()
	{
		$this->image = array(
			'url' 		=> '',
			'height'	=> 0,
			'width'		=> 0,
		);
		$this->updated = time();
	}
	
	/**
	* feed::set_opt()
	* 
	* @param mixed $var
	* @param mixed $data
	* @return 
	**/
	function set_opt($var, $data)
	{
		if (isset($this->$var)) 
		{
			$this->$var = $data;
		}
		else
		{
			return false;
		}
		return true;
	}

	/**
	* feed::set_image_opt()
	* 
	* @param mixed $var
	* @param mixed $data
	* @return 
	**/
	function set_image_opt($var, $data)
	{
		if (!isset($this->image[$var])) 
		{
			return false;
		}

		// Int or string? (why not use settype(), gettype() here?)
		$this->image[$var] = (is_numeric($this->image[$var])) ? (int) $data : $data;
		return true;
	}

	/**
	* feed::add_tag()
	* 
	* @return boolean result
	**/
	function add_tag()
	{
		global $template;

		switch ($this->type)
		{
			case 'rss': 
				$tagname = 'item';
			break;

			case 'atom': 
				$tagname = 'entry';
			break;
		}

		// Add tag to tpl.
		$template->assign_block_vars('tag', array(
			'TAG_NAME'		=> $tagname,
		));

		return true;
	}

	/**
	* feed::add_node()
	* 
	* @param string $nodename Name of the node.
	* @param string $nodevalue Value for the node
	* @param bool $date Value is date or not.
	* @return bool result
	**/
	function add_node($nodename, $nodevalue, $date = false, $link = false)
	{
		global $template, $user;

		if ($date === true)
		{
			$nodevalue = $user->format_date(($nodevalue === false) ? $this->updated : $nodevalue, (($this->type == 'atom') ? "Y-m-d\TH:i:sO" : 'r'));

			if ($this->type == 'atom')
			{
				$nodevalue = substr($nodevalue, 0, 22) . ':' . substr($nodevalue, -2);
			}
		}
//		if ($this->type == 'rss' && $nodename == 'author')
//		{
//			$nodename = 'dd:author';
//		}

		// Atom uses different names ;)
		if ($this->type == 'atom') 
		{
			switch ($nodename)
			{
				case 'description': 
					$nodename = 'content';
				break;

				case 'pubDate': 
					$nodename = 'updated';
				break;
				case 'link':
					$this->add_node('id', $nodevalue, $date, $link);
				break;
			}
		}

		// Add tag to tpl.
		$template->assign_block_vars('tag.node', array(
			'NODE_NAME'		=> $nodename,
			'NODE_VALUE'	=> $nodevalue,
			'LINK'			=> $link,
		));

		return true;
	}

	/**
	* feed::set_type()
	* 
	* @param mixed $type
	* @return bool result
	**/
	function set_type($type)
	{
		switch ($type)
		{
			case 'atom': 
				$this->template = 'contrib/atom_body.xml';
				$this->type = 'atom';
			break;

			case 'rss': 
				$this->template = 'contrib/rss_body.xml';
				$this->type = 'rss';
			break;

			default:
				$this->template = 'contrib/rss_body.xml';
				$this->type = 'rss';
		}
	}

	/**
	* feed::parse()
	* 
	* @return 
	**/
	function parse()
	{
		global $template, $user;

		if ($this->template === false || $this->type === false)
		{
			trigger_error('Not setting correct template file for RSS/ATOM.', E_USER_ERROR);
		}
		
		// Whee, lets set image and more things:)
		if (!empty($this->image['url']) && $this->type != 'atom') 
		{
			$template->assign_vars(array(
				'IMAGE'		=> $this->image['url'],
				'WIDTH'		=> $this->image['width'],
				'HEIGHT'	=> $this->image['height'],
			));
		}

		$date = $user->format_date($this->updated, (($this->type == 'atom') ? "Y-m-d\TH:i:sO" : 'r'));

		if ($this->type == 'atom')
		{
			$date = substr($date, 0, 22) . ':' . substr($date, -2);
		}

		$template->assign_vars(array(
			'LINK'		=> $this->link,
			'LINK2'		=> $this->link2,
			'DESC'		=> $this->description,
			'TITLE'		=> $this->title,
			'UPDATED'	=> $date,
		));

		// Assign tpl ;)
		$template->set_filenames(array(
			'xml'	=> $this->template
		));

		// Sending headers
		switch ($this->type)
		{
			case 'rss': 
				header("Content-Type: application/rss+xml; charset=UTF-8");
			break;

			case 'atom': 
				header("Content-Type: application/atom+xml; charset=UTF-8");
			break;

			default:
				return false;
		}

		$template->display('xml');
		die();
	}
}

?>