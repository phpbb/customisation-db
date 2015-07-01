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

namespace phpbb\titania\search\driver;

class solr extends zend
{
	/** @var \phpbb\titania\config\config */
	protected $ext_config;

	/**
	 * Constructor
	 *
	 * @param string $ext_root_path
	 * @param string $php_ext
	 * @param \phpbb\titania\config\config $ext_config
	 */
	public function __construct($ext_root_path, $php_ext, \phpbb\titania\config\config $ext_config)
	{
		parent::__construct($ext_root_path, $php_ext);
		$this->ext_config = $ext_config;
	}

	/**
	 * @{inheritDoc}
	 */
	public function initialise()
	{
		$this->load_search_component();

		$handler = new \ezcSearchSolrHandler(
			$this->ext_config->search_backend_ip,
			$this->ext_config->search_backend_port
		);

		// In case Solr would happen to go down..
		if (!$handler->connection)
		{
			throw new \Exception('Solr Server not responding');
		}

		$manager = new \ezcSearchEmbeddedManager;
		$this->client = new \ezcSearchSession($handler, $manager);

		return $this;
	}
}
