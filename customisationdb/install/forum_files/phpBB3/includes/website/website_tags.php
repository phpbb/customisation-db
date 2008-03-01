<?php
/** 
*
* @package website
* @version $Id: website_tags.php,v 1.4 2007/05/24 04:55:20 wgeric Exp $
* @copyright (c) 2006 phpBB Group 
* @license Not for redistribution
*
*/

/**
* @package website
*/
class website_tags
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $template, $root_path, $phpEx, $table_prefix;
		
		if (!defined('SITE_TAGS_TABLE'))
		{
			include ("{$root_path}includes/constants.$phpEx");
		}
		
		$this->tpl_name = 'website_tags';
		$this->page_title = 'Ariel tags Management';

		$action = request_var('action', '');
		$submit = (isset($_POST['submit'])) ? true : false;
		if ($action)
		{
			switch ($action)
			{		
				case 'delete':
					$tag_id = request_var('id', 0);
	
					if (confirm_box(true))
					{
						$sql = "DELETE FROM " . SITE_TAGS_TABLE . " 
							WHERE tag_id = $tag_id";
						$db->sql_query($sql);

						trigger_error('Tag deleted' . adm_back_link($this->u_action));
					}
					else
					{
						confirm_box(false, 'Are you sure you want to delete this tag?', build_hidden_fields(array(
							'i'			=> $id,
							'mode'		=> $mode,
							'id'		=> $tag_id,
							'action'	=> 'delete',
						)));
					}
				break;
				case 'edit':
					$id = request_var('id', 0);
					if (!$id)
					{
						trigger_error('No tag id found', E_USER_WARNING);
					}
					
					$sql = 'SELECT tag_group, tag_label, tag_class, tag_name FROM ' . SITE_TAGS_TABLE . ' WHERE tag_id = ' . $id;
					$result = $db->sql_query($sql);
					
					$data = $db->sql_fetchrow($result); 
					
					if (!$data)
					{
						trigger_error('No tag found', E_USER_WARNING);
					}
					
				case 'add':
					if ($action == 'add')
					{
						$data = array(
							'tag_group'		=> '',
							'tag_label'		=> '',
							'tag_class'		=> '',
							'tag_name'		=> '',
						);
					}
					
					
					$data = array(
						'tag_group'		=> utf8_normalize_nfc(request_var('tag_group', $data['tag_group'], true)),
						'tag_label'		=> utf8_normalize_nfc(request_var('tag_label', $data['tag_label'], true)),
						'tag_class'		=> utf8_normalize_nfc(request_var('tag_class', $data['tag_class'], true)),
						'tag_name'		=> utf8_normalize_nfc(request_var('tag_name', $data['tag_name'], true)),
					);

					$errors = array();
					if ($submit)
					{
						if (empty($data['tag_group']))
						{
							$errors[] = 'Tag group name is empty.';
						}
						
						if ($data['tag_name'] != '_')
						{
							$sql = 'SELECT tag_id 
								FROM ' . SITE_TAGS_TABLE . '
									WHERE tag_group = \'' . $db->sql_escape($data['tag_group']) . '\' 
									ORDER BY tag_class';
							$result = $db->sql_query($sql);
							
							if (!$db->sql_fetchrow($result))
							{
								$errors[] = 'This tag group dont exists';
							}
						}
						
						$db->sql_freeresult($result);

						if (empty($data['tag_class']))
						{
							$errors[] = 'The tag class is empty.';
						}
						
						$sql = 'SELECT tag_id 
							FROM ' . SITE_TAGS_TABLE . '
								WHERE tag_class = \'' . $db->sql_escape($data['tag_class']) . '\' 
								ORDER BY tag_class';
						$result = $db->sql_query($sql);
						
						if (!$db->sql_fetchrow($result))
						{
							$errors[] = 'This tag class dont exists';
						}
						
						$db->sql_freeresult($result);
						
						if (empty($data['tag_label']))
						{
							$errors[] = 'The tag label is empty.';
						}

						if (empty($data['tag_name']))
						{
							$errors[] = 'The tag name is empty.';
						}
						
						if (!sizeof($errors))
						{
							if ($action == 'edit')
							{
								$sql = 'UPDATE ' . SITE_TAGS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $data) . " WHERE tag_id = $id";
								$message = 'Tag edited.';
							}
							else
							{
								$sql = 'INSERT INTO ' . SITE_TAGS_TABLE . ' ' . $db->sql_build_array('INSERT', $data);
								$message = 'Tag inserted.';
							}
							// Time to insert the new group to the db
							
							$db->sql_query($sql);
							 
							trigger_error($message . adm_back_link($this->u_action));
						}
					}
					
					$template->assign_vars(array(
						'U_ACTION'		=> $this->u_action,
						'TAG_GROUP'		=> $data['tag_group'],
						'TAG_LABEL'		=> $data['tag_label'],
						'TAG_CLASS'		=> $data['tag_class'],
						'TAG_NAME'		=> $data['tag_name'],
						
						'ERROR_MSG'		=> sizeof($errors) ? implode("<br />", $errors) : false,
						
						'S_EDIT'					=> true,
						'HIDDEN_FIELDS'		=> build_hidden_fields(array(
							'action'	=> $action,
							'id'		=> $id,
						)),
					));
					
					$sql = 'SELECT DISTINCT tag_class FROM ' . SITE_TAGS_TABLE . ' GROUP BY tag_class ORDER BY tag_class';
					$result = $db->sql_query($sql);
					
					while ($row = $db->sql_fetchrow($result))
					{
						$template->assign_block_vars('class', array(
							'CLASS' => $row['tag_class'],
						));
					}

					$sql = 'SELECT tag_group, tag_label FROM ' . SITE_TAGS_TABLE . ' WHERE tag_name = \'_\' GROUP BY tag_group ORDER BY tag_class';
					$result = $db->sql_query($sql);
					
					while ($row = $db->sql_fetchrow($result))
					{
						$template->assign_block_vars('group', array(
							'GROUP' => $row['tag_group'],
							'LABEL'	=> $row['tag_label'],
						));
					}
					
				break;
				case 'add_group':
					$data = array(
						'tag_group'		=> utf8_normalize_nfc(request_var('tag_group', '', true)),
						'tag_label'		=> utf8_normalize_nfc(request_var('tag_label', '', true)),
						'tag_class'		=> utf8_normalize_nfc(request_var('tag_class', '', true)),
						'tag_name'		=> '_',
						
					);
					$errors = array();
					if ($submit)
					{
						if (empty($data['tag_group']))
						{
							$errors[] = 'Tag group name is empty.';
						}
						if (empty($data['tag_class']))
						{
							$errors[] = 'The tag class is empty.';
						}
						
						$sql = 'SELECT tag_id 
							FROM ' . SITE_TAGS_TABLE . '
								WHERE tag_class = \'' . $db->sql_escape($data['tag_class']) . '\' 
								ORDER BY tag_class';
						$result = $db->sql_query($sql);
						
						if (!$db->sql_fetchrow($result))
						{
							$errors[] = 'This tag class dont exists';
						}
						
						$db->sql_freeresult($result);
						
						if (empty($data['tag_label']))
						{
							$errors[] = 'The tag label is empty.';
						}
						
						if (!sizeof($errors))
						{
							// Time to insert the new group to the db
							$sql = 'INSERT INTO ' . SITE_TAGS_TABLE . ' ' . $db->sql_build_array('INSERT', $data);
							$db->sql_query($sql);
							 
							trigger_error('Tag group inserted.' . adm_back_link($this->u_action));
						}
					}
					
					$template->assign_vars(array(
						'U_ACTION'		=> $this->u_action,
						'TAG_GROUP'		=> $data['tag_group'],
						'TAG_LABEL'		=> $data['tag_label'],
						
						'ERROR_MSG'		=> sizeof($errors) ? implode("<br />", $errors) : false,
						
						'S_ADD_GROUP'	=> true,
						'HIDDEN_FIELDS'		=> build_hidden_fields(array(
							'action'	=> 'add_group',
						)),
					));
					
					$sql = 'SELECT DISTINCT tag_class FROM ' . SITE_TAGS_TABLE . ' GROUP BY tag_class ORDER BY tag_class';
					$result = $db->sql_query($sql);
					
					while ($row = $db->sql_fetchrow($result))
					{
						$template->assign_block_vars('class', array(
							'CLASS' => $row['tag_class'],
						));
					}
				break;
				default:
					trigger_error('No valid mode');
			}	
		}
		else
		{
			
			// Gather available tags out of the database
			$sql = 'SELECT tag_id, tag_group, tag_name, tag_label, tag_class
				FROM ' . SITE_TAGS_TABLE . "
				ORDER BY tag_class ASC, tag_group ASC, tag_name ASC";
			$result = $db->sql_query($sql);
			
			$prev_class = '';
			
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['tag_class'] != $prev_class)
				{
					$template->assign_block_vars('class', array(
						'CLASS'	=> $row['tag_class'],
					));
					$prev_class = $row['tag_class'];
				}
				$template->assign_block_vars('class.tags', array(
					'TAG_GROUP'		=> $row['tag_group'],
					'TAG_NAME'		=> $row['tag_name'],
					'TAG_LABEL'		=> $row['tag_label'],
					'U_DELETE'		=> append_sid($this->u_action, 'action=delete&amp;id=' . $row['tag_id']),
					'U_EDIT'			=> append_sid($this->u_action, 'action=edit&amp;id=' . $row['tag_id']),
				));
			}
		}
	}
}

?>
