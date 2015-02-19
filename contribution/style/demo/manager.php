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

namespace phpbb\titania\contribution\style\demo;

class manager
{
	/** @var \phpbb\user */
	protected $user;

	/** @var */
	protected $container;

	/** @var \phpbb\titania\config\config */
	protected $ext_config;

	/** @var string */
	protected $php_ext;

	/** @var \phpbb\db\db_interface */
	protected $db;

	/** @var int */
	protected $branch;

	/** @var string */
	protected $board_root_path;

	/** @var \phpbb\titania\entity\package */
	protected $package;

	/** @var \titania_contribution */
	protected $contrib;

	/**
	* Constructor.
	*
	* @param \phpbb\user $user
	* @param $container
	* @param \phpbb\titania\config\config $ext_config
	* @param string $php_ext
	*/
	public function __construct(\phpbb\user $user, $container, $ext_config, $php_ext)
	{
		$this->user = $user;
		$this->ext_config = $ext_config;
		$this->php_ext = $php_ext;
		$this->container = $container;

		$this->user->add_lang('acp/styles');
	}

	/**
	* Configure manager.
	*
	* @param int $branch
	* @param \titania_contribution $contrib
	* @param \phpbb\titania\entity\package $package
	*
	* @return bool Returns false if an error occurred.
	*/
	public function configure($branch, $contrib, $package)
	{
		$this->branch = $branch;
		$this->contrib = $contrib;
		$this->package = $package;

		if (empty($this->ext_config->demo_style_path[$this->branch]))
		{
			return false;
		}
		$this->board_root_path = $this->ext_config->demo_style_path[$this->branch];

		if ($this->board_root_path[strlen($this->board_root_path) - 1] != '/')
		{
			$this->board_root_path .= '/';
		}

		if (!is_dir($this->board_root_path) || !file_exists($this->board_root_path . 'config.' . $this->php_ext))
		{
			return false;
		}

		return $this->db_connect();
	}

	/**
	* Get result.
	*
	* @param mixed $result			Result returned by demo hook. Either the style id
	*	or an error.
	* @return array Returns array in form of array(id => (int), error => (string))
	*/
	protected function get_result($result)
	{
		$result = json_decode($result);

		if (is_int($result) || ctype_digit($result))
		{
			return array(
				'id'	=> (int) $result,
				'error'	=> '',
			);
		}

		$valid_errors = array(
			'INVALID_DIR',
		);

		if (!in_array($result, $valid_errors))
		{
			$result = 'UNKNOWN_ERROR';
		}

		return array(
			'id'	=> 0,
			'error'	=> $this->user->lang($result),
		);
	}

	/**
	* Perform action.
	*
	* @param string $action
	* @return string
	*/
	protected function perform_action($action)
	{
		$key = $this->generate_auth_key($action);
		$data = http_build_query(array('key' => $key));

		$options = array(
			'http' => array(
				'method'	=> 'POST',
				'header'	=> "Content-type: application/x-www-form-urlencoded",
				'content'	=> $data,
			),
		);

		$hook_url = $this->ext_config->demo_style_hook[$this->branch];
		$context = stream_context_create($options);
		$result = file_get_contents($hook_url, false, $context, -1, 50);
		$this->delete_auth_key($key);

		return $this->get_result($result);
	}

	/**
	* Generate authentication key.
	*
	* @param string $action
	* @return string
	*/
	protected function generate_auth_key($action)
	{
		$key = gen_rand_string(64);

		$insert_ary = array(
			'config_name'	=> 'titania_key_' . $key,
			'config_value'	=> serialize(array(
				'action'	=> $action,
				'dir'		=> $this->get_style_dir(true),
				'time'		=> time(),
			)),
			'is_dynamic'	=> 1,
		);

		$sql = 'INSERT INTO ' . $this->table_prefix . 'config ' .
			$this->db->sql_build_array('INSERT', $insert_ary);
		$this->db->sql_query($sql);

		return $key;
	}

	/**
	* Delete authentication key.
	*
	* @param string $key
	* @return null
	*/
	protected function delete_auth_key($key)
	{
		$config_name = 'titania_key_' . $key;

		$sql = 'DELETE FROM ' .
			$this->table_prefix . 'config
			WHERE config_name = "' . $this->db->sql_escape($config_name) . '"';
		$this->db->sql_query($sql);
	}

	/**
	* Install style.
	*
	* @return string
	*/
	public function install()
	{
		$this->extract_package();

		return $this->perform_action('install');
	}

	/**
	* Delete style.
	*
	* @return null
	*/
	public function delete()
	{
		return $this->perform_action('delete');
	}

	/**
	* Connect to demo board database.
	*
	* @return bool Returns false if connection failed.
	*/
	protected function db_connect()
	{
		$config = new \phpbb\config_php_file($this->board_root_path, $this->php_ext);
		$db_driver = $config->convert_30_dbms_to_31($config->get('dbms'));

		$db_driver = new $db_driver();
		$connection = $db_driver->sql_connect(
			$config->get('dbhost'),
			$config->get('dbuser'),
			$config->get('dbpasswd'),
			$config->get('dbname'),
			$config->get('dbport')
		);

		if (is_string($connection))
		{
			return false;
		}

		$this->db = new \phpbb\db\driver\factory($this->container);
		$this->db->set_driver($db_driver);
		$this->table_prefix = $config->get('table_prefix');	

		return true;
	}

	/**
	* Get style directory name.
	*
	* @param bool $name_only		If false, returns the full path to the directory,
	*	otherwise just the name.
	* @retun string
	*/
	public function get_style_dir($name_only = false)
	{
		$dir_name = $this->contrib->contrib_name_clean . '_' . $this->contrib->contrib_id;

		if ($name_only)
		{
			return $dir_name;
		}
		return $this->board_root_path . 'styles/' . $dir_name . '/';
	}

	/**
	* Extract style package to demo board.
	*
	* @return null
	*/
	public function extract_package()
	{
		$this->package->ensure_extracted();
		$package_root = $this->package->find_directory(array('files' => array('required' => 'style.cfg')));
		$style_root = $this->get_style_dir();
		$filesystem = new \Symfony\Component\Filesystem\Filesystem;

		$filesystem->remove($style_root);
		$filesystem->rename($this->package->get_temp_path() . '/' . $package_root, $style_root);
	}

	/**
	* Get demo URL.
	*
	* @param int $branch			Branch: 30, 31, etc.
	* @param int $style_id
	*
	* @return string
	*/
	public function get_demo_url($branch, $style_id)
	{
		return sprintf($this->ext_config->demo_style_url[$branch], $style_id);
	}
}
