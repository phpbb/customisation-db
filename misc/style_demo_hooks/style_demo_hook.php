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

class titania_style_demo_hook
{
	/** @var string */
	protected $key;

	/** @var array */
	protected $config;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
	* Constructor.
	*
	* @param array $config
	* @param \phpbb\db\db_interface $db
	* @param \phpbb\user $user
	* @param string $root_path
	* @param string $php_ext
	*/
	public function __construct($config, $db, $user, $root_path, $php_ext)
	{
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->config = $config;

		$this->manager = new titania_demo_manager($config, $db, $user, $root_path, $php_ext);
	}

	/**
	* Run hook.
	*
	* @param string $key		Hook key.
	* @return mixed Returns error string or style id.
	*/
	public function run($key)
	{
		$this->key = "titania_key_$key";

		if (!$this->validate_key())
		{
			return $this->error('NO_AUTH');
		}

		$data = $this->get_data();

		$this->manager->configure($data['dir']);

		if ($data['action'] == 'install')
		{
			return $this->manager->install();
		}
		else if ($data['action'] == 'delete')
		{
			return $this->manager->delete();
		}
		return 'INVALID_ACTION';
	}

	/**
	* Validate hook key.
	*
	* @return bool Returns true if valid, false otherwise.
	*/
	protected function validate_key()
	{
		if (!$this->key || empty($this->config[$this->key]))
		{
			return false;
		}

		$data = $this->get_data();
		$time_diff = time() - $data['time'];

		return $time_diff <= 60 && $time_diff >= 0;
	}

	/**
	* Get action data from key.
	*
	* @return array
	*/
	protected function get_data()
	{
		return unserialize($this->config[$this->key]);
	}
}
