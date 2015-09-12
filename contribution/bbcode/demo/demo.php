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

namespace phpbb\titania\contribution\bbcode\demo;

/**
 * BBCode demo class. Allows the rendering of BBCode without adding BBCodes to the db.
 */
class demo
{
	/** @var \phpbb\titania\cache\service */
	protected $cache;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var \acp_bbcodes */
	protected $acp_bbcode;

	/** @var \bbcode */
	protected $bbcode;

	/** @var  \parse_message */
	protected $message_parser;

	/** @var array */
	protected $demo_cache;

	/** @var string */
	public $usage;

	/** @var string */
	public $replacement;

	/** @var int */
	public $contrib_id;

	/** @var array */
	public $bbcode_data = array();

	/**
	 * Constructor
	 *
	 * @param \phpbb\titania\cache\service $cache
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 */
	public function __construct(\phpbb\titania\cache\service $cache, $phpbb_root_path, $php_ext)
	{
		$this->cache = $cache;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Constructor for bbcode demo class
	 *
	 * @param int $contrib_id
	 * @param string $usage BBCode usage
	 * @param string $replacement BBCode replacement
	 * @return $this
	 */
	public function configure($contrib_id, $usage = '', $replacement = '')
	{
		$this->demo_cache = $this->cache->get('_titania_bbcode_demo');
		$this->contrib_id = $contrib_id;
		$this->usage = $usage;
		$this->replacement = html_entity_decode($replacement);

		return $this;
	}

	/**
	 * Get regular expression and other data that's normally sent to db when adding a BBCode
	 */
	public function get_bbcode_data()
	{
		if (!$this->usage || !$this->replacement)
		{
			return;
		}

		if (!is_object($this->acp_bbcode))
		{
			require($this->phpbb_root_path . 'includes/acp/acp_bbcodes.' . $this->php_ext);
			$this->acp_bbcode = new \acp_bbcodes;
		}

		$this->bbcode_data = $this->acp_bbcode->build_regexp($this->usage, $this->replacement);
		$this->bbcode_data['bbcode_id'] = $this->contrib_id;
		$this->bbcode_data['bbcode_tpl'] = $this->replacement;
	}

	/**
	 * Parse BBCode in a message
	 *
	 * @param string $message Message to parse
	 * @return string Parsed message
	 */
	public function parse_message($message)
	{
		if (empty($this->bbcode_data))
		{
			return '';
		}

		if (!is_object($this->message_parser))
		{
			if (!class_exists('\bbcode'))
			{
				require($this->phpbb_root_path . 'includes/bbcode.' . $this->php_ext);
			}
			if (!class_exists('\parse_message'))
			{
				require($this->phpbb_root_path . 'includes/message_parser.' . $this->php_ext);
			}
			$this->message_parser = new \parse_message;
		}

		$this->message_parser->parse_message($message);
		$this->bbcode_data['regexp'] = array(
			$this->bbcode_data['first_pass_match'] => str_replace(
				'$uid',
				$this->message_parser->bbcode_uid,
				$this->bbcode_data['first_pass_replace']
			)
		);
		$this->message_parser->bbcodes = array($this->bbcode_data['bbcode_tag'] => $this->bbcode_data);
		$this->message_parser->parse_bbcode();

		return $this->message_parser->message;
	}

	/**
	 * Render BBCode in a message
	 *
	 * @param string $message Message to render -- should already be parsed using message parser. Using some modified code from $bbcode->bbcode_cache_init() in phpBB's bbcode.php.
	 * @param string $uid BBCode uid
	 * @param string $bitfield BBCode bitfield
	 * @return string Demo HTML
	 */
	public function render_message($message, $uid, $bitfield)
	{
		if (empty($this->bbcode_data))
		{
			return $message;
		}

		if (!is_object($this->bbcode))
		{
			if (!class_exists('\bbcode'))
			{
				require($this->phpbb_root_path . 'includes/bbcode.' . $this->php_ext);
			}
			$this->bbcode = new \bbcode;
		}

		// We define bbcode_bitfield here instead of when instantiating the class to prevent bbcode_cache_init() from running
		$this->bbcode->bbcode_bitfield = $bitfield;

		$bbcode_tpl = (!empty($this->bbcode_data['second_pass_replace'])) ? $this->bbcode_data['second_pass_replace'] : $this->bbcode_data['bbcode_tpl'];
		// Handle language variables
		$bbcode_tpl = preg_replace('/{L_([A-Z_]+)}/e', "(!empty(phpbb::\$user->lang['\$1'])) ? phpbb::\$user->lang['\$1'] : ucwords(strtolower(str_replace('_', ' ', '\$1')))", $bbcode_tpl);

		if ($this->bbcode_data['second_pass_replace'])
		{
			$this->bbcode->bbcode_cache[$this->contrib_id] = array(
				'preg' => array(
					$this->bbcode_data['second_pass_match'] => $bbcode_tpl
				)
			);
		}
		else
		{
			$this->bbcode->bbcode_cache[$this->contrib_id] = array(
				'str' => array(
					$this->bbcode_data['second_pass_match'] => $bbcode_tpl
				)
			);
		}
		$this->bbcode->bbcode_uid = $uid;
		$this->bbcode->bbcode_second_pass($message);

		return bbcode_nl2br($message);
	}

	/**
	 * Get BBCode demo HTML
	 *
	 * @param string $message Message to be used as demo
	 * @param bool $refresh Force demo to be regenerated if already stored in cache
	 * @return string Demo HTML
	 */
	public function get_demo($message, $refresh = false)
	{
		if (!$refresh && !empty($this->demo_cache[$this->contrib_id]))
		{
			return $this->demo_cache[$this->contrib_id];
		}

		$this->get_bbcode_data();
		$message = $this->parse_message($message);
		$message = $this->render_message(
			$message,
			$this->message_parser->bbcode_uid,
			$this->message_parser->bbcode_bitfield
		);
		$this->store_cache($message);

		return $message;
	}

	/**
	 * Store demo HTML in cache
	 *
	 * @param string $message Demo HTML to be stored
	 */
	public function store_cache($message)
	{
		$this->demo_cache[$this->contrib_id] = $message;
		$this->cache->put('_titania_bbcode_demo', $this->demo_cache);
	}

	/**
	 * Unset stored demo in cache
	 */
	public function clear_cache()
	{
		unset($this->demo_cache[$this->contrib_id]);
		$this->cache->put('_titania_bbcode_demo', $this->demo_cache);
	}
}
