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

class titania_demo_manager extends acp_styles
{
	/** @var \phpbb\db\db_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $style_dir;

	/**
	* Construct.
	*
	* @param \phpbb\db\db_interface $db
	* @param \phpbb\user $user
	* @param string $phpbb_root_path
	* @param string $php_ext
	*/
	public function __construct($config, $db, $user, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->default_style = $config['default_style'];
		$this->styles_path = $this->phpbb_root_path . $this->styles_path_absolute . '/';
	}

	/**
	* Configure manager.
	*
	* @param string $style_dir_name		Style directory name
	* @return null
	*/
	public function configure($style_dir_name)
	{
		$this->style_dir = $style_dir_name;
	}

	/**
	* Get style information.
	*
	* @param string $directory		Style directory name
	* @return mixed Returns array containing style info or false on failure.
	*/
	protected function get_style_info($directory)
	{
		$styles = array_merge(
			$this->find_available(false),
			$this->get_styles()
		);
		foreach ($styles as $style)
		{
			if ($directory == $style['style_path'])
			{
				return $style;
			}
		}
		return false;
	}

	/**
	* Install style.
	*
	* @return Returns style if or error string.
	*/
	public function install()
	{
		$style = $this->get_style_info($this->style_dir);

		if ($style === false)
		{
			return 'DIR_NOT_FOUND';
		}

		$style_id = (empty($style['style_id'])) ? $this->install_style($style) : (int) $style['style_id'];

		if (!empty($style_id) && !$style['style_active'])
		{
			$this->activate($style_id);
		}
		return $style_id;
	}

	/**
	* Activate style.
	*
	* @param int $style_id.
	* @return null
	*/
	protected function activate($style_id)
	{
		$sql = 'UPDATE ' . STYLES_TABLE . '
			SET style_active = 1
			WHERE style_id = ' . (int) $style_id;
		$this->db->sql_query($sql);
	}
}
