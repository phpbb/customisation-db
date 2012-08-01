<?php
/**
*
* @package Titania
* @copyright (c) 2012 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

/**
 * BBCode demo class. Allows the rendering of BBCode without adding BBCodes to the db.
 *
 * @package Titania
 */
class titania_bbcode_demo
{
	private $acp_bbcode;
	private $bbcode;
	private $cache;
	public $message_parser;
	public $usage;
	public $replacement;
	public $contrib_id;
	public $bbcode_data = array();

	/**
	 * Constructor for bbcode demo class
	 *
	 * @param int $contrib_id
	 * @param string $usage BBCode usage
	 * @param string $replacement BBCode replacement
	 */
	public function __construct($contrib_id, $usage = '', $replacement = '')
	{
		$this->cache = titania::$cache->get('_titania_bbcode_demo');
		$this->contrib_id = $contrib_id;
		$this->usage = html_entity_decode($usage);
		$this->replacement = html_entity_decode($replacement);
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
			phpbb::_include('acp/acp_bbcodes', false, 'acp_bbcodes');
			$this->acp_bbcode = new acp_bbcodes();
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
		if (!sizeof($this->bbcode_data))
		{
			return;
		}

		if (!is_object($this->message_parser))
		{
			phpbb::_include('message_parser', false, 'parse_message');
			$this->message_parser = new parse_message();
		}

		$this->message_parser->parse_message($message);
		$this->bbcode_data['regexp'] = array($this->bbcode_data['first_pass_match'] => str_replace('$uid', $this->message_parser->bbcode_uid, $this->bbcode_data['first_pass_replace']));
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
		if (!sizeof($this->bbcode_data))
		{
			return $message;
		}

		if (!is_object($this->bbcode))
		{
			phpbb::_include('bbcode', false, 'bbcode');
			$this->bbcode = new bbcode();
		}

		// We define bbcode_bitfield here instead of when instantiating the class to prevent bbcode_cache_init() from running
		$this->bbcode->bbcode_bitfield = $bitfield;

		$bbcode_tpl = (!empty($this->bbcode_data['second_pass_replace'])) ? $this->bbcode_data['second_pass_replace'] : $this->bbcode_data['bbcode_tpl'];
		// Handle language variables
		$bbcode_tpl = preg_replace('/{L_([A-Z_]+)}/e', "(!empty(phpbb::\$user->lang['\$1'])) ? phpbb::\$user->lang['\$1'] : ucwords(strtolower(str_replace('_', ' ', '\$1')))", $bbcode_tpl);

		if ($this->bbcode_data['second_pass_replace'])
		{
			$this->bbcode->bbcode_cache[$this->contrib_id] = array('preg' => array($this->bbcode_data['second_pass_match'] => $bbcode_tpl));
		}
		else
		{
			$this->bbcode->bbcode_cache[$this->contrib_id] = array('str' => array($this->bbcode_data['second_pass_match'] => $bbcode_tpl));
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
		if (!$refresh && !empty($this->cache[$this->contrib_id]))
		{
			return $this->cache[$this->contrib_id];
		}

		$this->get_bbcode_data();
		$message = $this->parse_message($message);
		$message = $this->render_message($message, $this->message_parser->bbcode_uid, $this->message_parser->bbcode_bitfield);
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
		$this->cache[$this->contrib_id] = $message;
		titania::$cache->put('_titania_bbcode_demo', $this->cache); 
	}

	/**
	 * Unset stored demo in cache
	 */
	public function clear_cache()
	{
		unset($this->cache[$this->contrib_id]);
		titania::$cache->put('_titania_bbcode_demo', $this->cache);
	}
}
