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
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

/**
 * Class to generate sort and order by parameters
 *
 * @package Titania
 */
class sort
{

	/**
	 * Sort key text shown to user, used for select box
	 *
	 * @var array
	 */
	protected $sort_key_text = array();

	/**
	 * Array of Sort keys for SQL, used in relation to sort_key_text
	 *
	 * @var array
	 */
	protected $sort_key_sql = array();

	/**
	 * User selected sort key
	 *
	 * @var string
	 */
	protected $sort_key = '';

	/**
	 * User selected sort direction
	 *
	 * @var string
	 */
	protected $sort_dir = '';

	/**
	 * Default sort key for the page
	 *
	 * @var string
	 */
	public $default_key = '';

	/**
	 * Default Sort direction for the page
	 *
	 * @var string
	 */
	public $default_dir = '';

	/**
	 * Sort direction text shown to user, used for select box
	 *
	 * @var unknown_type
	 */
	protected $sort_dir_text = array();

	/**
	 * Set some default values for the sort object
	 */
	public function __construct()
	{
		global $user;

		$this->sort_dir_text = array(
			'a' => $user->lang['ASCENDING'],
			'd' => $user->lang['DESCENDING'],
		);

		$this->default_dir = 'a';
	}

	/**
	 * Setup the sort key and direction -- calls request_var for sort key and sort dir.
	 *
	 * @param string $default_key set the default sort key (a, b, c, etc...)
	 * @param string $sk name of sort key _REQUEST field
	 * @param string $sd name of sort dir _REQUEST field
	 */
	public function sort_request($default_key, $sk = 'sk', $sd = 'sd')
	{
		// default_key may already be set, check to ensure we want to set it.
		if ($default_key)
		{
			$this->set_default_key($default_key);
		}

		$this->set_sort_key(request_var($sk, $this->default_key));
		$this->set_sort_dir(request_var($sd, $this->default_dir));

		return true;
	}

	/**
	 * Set the default sort key
	 *
	 * @param string $default_key
	 */
	public function set_default_key($default_key)
	{
		$this->default_key = $default_key;

		return true;
	}

	/**
	 * Set the user selected sort key
	 *
	 * @param string $sort_key
	 */
	public function set_sort_key($sort_key)
	{
		$this->sort_key = $sort_key;

		return true;
	}

	/**
	 * Get the set sort key
	 *
	 * @param bool $return_default if set to false, this method will not return the sort key if it is also the default key
	 * @return string sort_key
	 */
	public function get_sort_key($return_default = true)
	{
		if (!$return_default && $this->sort_key == $this->default_key)
		{
			return false;
		}

		return $this->sort_key;
	}

	/**
	 * Set the user selected sort direction
	 *
	 * @param string $sort_dir
	 */
	public function set_sort_dir($sort_dir)
	{
		$this->sort_dir = $sort_dir;

		return true;
	}

	/**
	 * Get the set sort direction
	 *
	 * @param bool $return_default if set to false, this method will not return the sort dir if it is also the default direction
	 * @return string sort_dir
	 */
	public function get_sort_dir($return_default = true)
	{
		if (!$return_default && $this->sort_dir == $this->default_dir)
		{
			return false;
		}

		return $this->sort_dir;
	}

	/**
	 * Setup the list of possible sort options using an array
	 *
	 * Example: Setting the sort keys, text, and default key
	 * <code>
	 * 	$sort->set_sort_keys(array(
	 * 		'a'	=> array('SORT_LANG_KEY1',	't.some_sql_field'),
	 * 		'b'	=> array('SORT_LANG_KEY2',	't.another_sql', 'default' => true),
	 * 		'c'	=> array('ANOTHER_LANG',	'a.sql_field'),
	 * ));
	 * </code>
	 *
	 * @param array $sort_keys
	 */
	public function set_sort_keys($sort_keys)
	{
		global $user;

		foreach ($sort_keys as $key => $option)
		{
			// text lang sort key
			$this->sort_key_text[$key] = $user->lang[$option[0]];

			// sql sort key
			$this->sort_key_sql[$key] = $option[1];

			// if the third array increment is set to true, this key is set to default
			if ((isset($option[2]) && $option[2]) || (isset($option['default']) && $option['default']))
			{
				$this->set_default_key($key);
			}
		}

		return true;
	}

	/**
	 * Get order string for ORDER BY sql statement
	 *
	 * @return string SQL ORDER BY
	 */
	public function get_order_by()
	{
		// Sorting and order
		if (!isset($this->sort_key_sql[$this->sort_key]))
		{
			$this->sort_key = $this->default_key;
		}

		return $this->sort_key_sql[$this->sort_key] . ' ' . (($this->sort_dir == 'a') ? 'ASC' : 'DESC');
	}

	/**
	 * Grab the sort key option list for usage within template
	 *
	 * @return string
	 */
	public function get_sort_key_list()
	{
		$s_sort_key = '';
		foreach ($this->sort_key_text as $key => $value)
		{
			$selected = ($this->sort_key == $key) ? ' selected="selected"' : '';
			$s_sort_key .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}

		return $s_sort_key;
	}

	/**
	 * Grab the sort direction option list for usage within template
	 *
	 * @return string
	 */
	public function get_sort_dir_list()
	{
		$s_sort_dir = '';
		foreach ($this->sort_dir_text as $key => $value)
		{
			$selected = ($this->sort_dir == $key) ? ' selected="selected"' : '';
			$s_sort_dir .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}

		return $s_sort_dir;
	}
}

