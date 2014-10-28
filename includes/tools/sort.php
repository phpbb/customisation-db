<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
 * Class to generate pagination
 *
 * @package Titania
 */
class titania_sort extends titania_object
{
	/**
	 * Constants
	 */
	const OFFSET_LIMIT_DEFAULT = 25;
	const OFFSET_LIMIT_MAX = 100;

	/**
	* URL Location/Parameters
	* Setting these will over-ride the settings sent in build_pagination
	*
	* @var mixed
	*/
	public $url_location = false;
	public $url_parameters = false;

	/**
	* Have we requested the sort data already?
	*
	* @var bool
	*/
	public $request_completed = false;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/**
	 * Set some default variables, set template_vars default values
	 */
	public function __construct()
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'start'			=> array('default' => 0),
			'start_name'	=> array('default' => 'start'),
			'limit'			=> array('default' => self::OFFSET_LIMIT_DEFAULT),
			'limit_name'	=> array('default' => 'limit'),
			'default_limit'	=> array('default' => self::OFFSET_LIMIT_DEFAULT),
			'max_limit'		=> array('default' => self::OFFSET_LIMIT_MAX),

			'sort_key_ary'		=> array('default' => array()),
			'sort_key'			=> array('default' => ''),
			'default_sort_key'	=> array('default' => ''),
			'sort_key_name'		=> array('default' => 'sk'),
			'sort_dir'			=> array('default' => 'a'),
			'default_sort_dir'	=> array('default' => 'a'),
			'sort_dir_name'		=> array('default' => 'sd'),

			'total'			=> array('default' => 0),
			'result_lang'	=> array('default' => 'TOTAL_RESULTS'), // sprintf'd into 'TOTAL_RESULTS' output;  Should have TOTAL_RESULTS and TOTAL_RESULTS_ONE strings
			'template_block'	=> array('default' => 'pagination'),
			'template_vars'		=> array(
				'default' => array(
					'S_PAGINATION_ACTION'	=> 'S_PAGINATION_ACTION',
					'S_SORT_ACTION'			=> 'S_SORT_ACTION',
					'S_NUM_POSTS'			=> 'S_NUM_POSTS',
					'S_SELECT_SORT_KEY'		=> 'S_SELECT_SORT_KEY',
					'S_SELECT_SORT_DIR'		=> 'S_SELECT_SORT_DIR',
					'SORT_KEYS_NAME'		=> 'SORT_KEYS_NAME',
					'SORT_DIR_NAME'			=> 'SORT_DIR_NAME',

					'PER_PAGE'				=> 'PER_PAGE',
					'ON_PAGE'				=> 'ON_PAGE',
					'PREVIOUS_PAGE'			=> 'PREVIOUS_PAGE',
					'NEXT_PAGE'				=> 'NEXT_PAGE',
					'TOTAL_PAGES'			=> 'TOTAL_PAGES',
					'TOTAL_ITEMS'			=> 'TOTAL_ITEMS',
					'TOTAL_RESULTS'			=> 'TOTAL_RESULTS',
				),
			),
		));

		$this->path_helper = phpbb::$container->get('path_helper');
	}

	/**
	* Set some defaults
	*
	* @param int $limit Default limit, false to not change
	* @param string $sort_key Default sort key, false to not change
	* @param string $sort_dir (a|d) for ascending/descending, false to not change
	*/
	public function set_defaults($limit, $sort_key = false, $sort_dir = false)
	{
		if ($limit !== false)
		{
			$this->default_limit = (int) $limit;
		}

		if ($sort_key !== false)
		{
			$this->default_sort_key = $sort_key;
		}

		if ($sort_dir !== false)
		{
			$this->default_sort_dir = ($sort_dir == 'a') ? 'a' : 'd';
		}
	}

	/**
	 * Request function to run the start and limit grabbing functions
	 */
	public function request()
	{
		if ($this->request_completed)
		{
			return;
		}

		$this->get_start();
		$this->get_limit();

		$this->get_sort_key();
		$this->get_sort_dir();

		$this->request_completed = true;
	}

	/**
	 * Set start variable for pagination
	 *
	 * @param int $default custom start param
	 *
	 * @return int	start
	 */
	public function get_start($default = 0)
	{
		$this->start = phpbb::$request->variable($this->start_name, (int) $default);

		return $this->start;
	}

	/**
	 * Set limit variable for pagination
	 *
	 * @param int $default default Offset/Limit -- uses the constant if unset.
	 *
	 * @return int	$limit
	 */
	public function get_limit($default = false)
	{
		if ($default !== false)
		{
			$this->default_limit = $default;
		}

		$limit = phpbb::$request->variable($this->limit_name, (int) $this->default_limit);

		// Don't allow limits of 0 which is unlimited results. Instead use the max limit.
		$limit = ($limit == 0) ? $this->max_limit : $limit;

		// We don't allow the user to specify a limit higher than the maximum.
		$this->limit = ($limit > $this->max_limit) ? $this->max_limit : $limit;

		return $this->limit;
	}

	/**
	 * Set sort key
	 */
	public function get_sort_key()
	{
		$this->sort_key = phpbb::$request->variable($this->sort_key_name, (string) $this->default_sort_key);

		if (!isset($this->sort_key_ary[$this->sort_key]))
		{
			$this->sort_key = $this->default_sort_key;
		}

		return $this->sort_key;
	}

	/**
	 * Set sort direction
	 */
	public function get_sort_dir()
	{
		$this->sort_dir = (phpbb::$request->variable($this->sort_dir_name, (string) $this->default_sort_dir) == $this->default_sort_dir) ? $this->default_sort_dir : (($this->default_sort_dir == 'a') ? 'd' : 'a');

		return $this->sort_dir;
	}

	/**
	* Count the number of results
	*
	* @param mixed $sql_ary
	* @param mixed $field
	*/
	public function sql_count($sql_ary, $field)
	{
		$sql_ary['SELECT'] = "COUNT($field) AS cnt";
		unset($sql_ary['ORDER_BY']);

		$count_sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
		phpbb::$db->sql_query($count_sql);
		$this->total = phpbb::$db->sql_fetchfield('cnt');
		phpbb::$db->sql_freeresult();

		return $this->total;
	}

	/**
	 * Setup the list of possible sort options using an array
	 *
	 * Example: Setting the sort keys, text, and default key
	 * <code>
	 * 	$sort->set_sort_keys(array(
	 * 		'a'	=> array('SORT_LANG_KEY1',	't.some_sql_field'),
	 * 		'b'	=> array('SORT_LANG_KEY2',	't.another_sql', true), // this is the default
	 * 		'c'	=> array('ANOTHER_LANG',	'a.sql_field'),
	 * ));
	 * </code>
	 *
	 * @param array $sort_keys
	 */
	public function set_sort_keys($sort_keys)
	{
		foreach ($sort_keys as $key => $options)
		{
			if (!isset($options[0]) || !isset($options[1]))
			{
				unset($sort_keys[$key]);
				continue;
			}

			// if the third array increment is set to true, this key is set to default
			if ((isset($options[2]) && $options[2]))
			{
				$this->default_sort_key = $key;
			}
		}

		$this->sort_key_ary = $sort_keys;
	}

	/**
	* Set the URL info
	*
	* @param string $location
	* @param array $params
	*/
	public function set_url($location, $params = array())
	{
		$this->url_location = $location;

		if (is_array($params))
		{
			$this->url_parameters = $params;
		}
	}

	/**
	 * Grab the sort key option list for usage within template
	 *
	 * @return string
	 */
	public function get_sort_key_list()
	{
		$s_sort_key = '';
		foreach ($this->sort_key_ary as $key => $options)
		{
			$selected = ($this->sort_key == $key) ? ' selected="selected"' : '';
			$value = $options[0];

			$s_sort_key .= '<option value="' . $key . '"' . $selected . '>' . ((isset(phpbb::$user->lang[$value])) ? phpbb::$user->lang[$value] : $value) . '</option>';
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
		$sort_dir = array(
			'a'		=> 'ASCENDING',
			'd'		=> 'DESCENDING',
		);

		$s_sort_dir = '';
		foreach ($sort_dir as $key => $value)
		{
			$selected = ($this->sort_dir == $key) ? ' selected="selected"' : '';
			$s_sort_dir .= '<option value="' . $key . '"' . $selected . '>' . ((isset(phpbb::$user->lang[$value])) ? phpbb::$user->lang[$value] : $value) . '</option>';
		}

		return $s_sort_dir;
	}

	/**
	 * Get order string for ORDER BY sql statement
	 *
	 * @return string SQL ORDER BY
	 */
	public function get_order_by()
	{
		// If the sort_request function was not run yet we shall run it
		if (!$this->sort_key || !$this->sort_dir)
		{
			$this->request();
		}

		return $this->sort_key_ary[$this->sort_key][1] . ' ' . (($this->sort_dir == 'a') ? 'ASC' : 'DESC');
	}

	/**
	* Build a canonical URL
	*/
	public function build_canonical()
	{
		$params = $this->url_parameters;

		if ($this->start)
		{
			$params[$this->start_name] = $this->start;
		}
		if ($this->limit != $this->default_limit)
		{
			$params[$this->limit_name] = $this->limit;
		}
		if ($this->sort_key != $this->default_sort_key)
		{
			$params[$this->sort_key_name] = $this->sort_key;
		}
		if ($this->sort_dir != $this->default_sort_dir)
		{
			$params[$this->sort_dir_name] = $this->sort_dir;
		}

		return $this->path_helper->append_url_params($this->url_location, $params);
	}

	/**
	 * Build pagination and send to template
	 * $this->url_location and $this->url_parameters will over-ride the settings given here for $page, $params.
	 * The reason is that the place that calls build_pagination is typically in a completely different area, in an area that can't say for certain the correct URL (other than the current page)
	 *
	 * @param string $page path/page to be used in pagination url
	 * @param array $params to be used in pagination url
	 */
	public function build_pagination($page, $params = array())
	{
		if ($this->url_location)
		{
			$page = $this->url_location;
		}
		if ($this->url_parameters)
		{
			$params = $this->url_parameters;
		}

		// Spring cleaning
		unset($params[$this->start_name], $params[$this->limit_name], $params[$this->sort_key_name], $params[$this->sort_dir_name]);

		// Add the limit to the URL if required
		if ($this->limit != $this->default_limit)
		{
			$params[$this->limit_name] = $this->limit;
		}

		// Don't include the sort key/dir in the sort action url
		$sort_url = $this->path_helper->append_url_params($page, $params);

		// Add the sort key to the URL if required
		if ($this->sort_key != $this->default_sort_key)
		{
			$params[$this->sort_key_name] = $this->sort_key;
		}

		// Add the sort dir to the URL if required
		if ($this->sort_dir != $this->default_sort_dir)
		{
			$params[$this->sort_dir_name] = $this->sort_dir;
		}

		$pagination_url = $this->path_helper->append_url_params($page, $params);

		$pagination = phpbb::$container->get('pagination');

		$pagination->generate_template_pagination($pagination_url, $this->template_block, 'start', $this->total, $this->limit, $this->start);

		if ($this->template_block == 'pagination')
		{
			phpbb::$template->assign_vars(array(
				$this->template_vars['S_SORT_ACTION']		=> $sort_url,
				$this->template_vars['S_PAGINATION_ACTION']	=> $pagination_url,
				$this->template_vars['S_NUM_POSTS']			=> $this->total,

				$this->template_vars['S_SELECT_SORT_KEY']	=> $this->get_sort_key_list(),
				$this->template_vars['S_SELECT_SORT_DIR']	=> $this->get_sort_dir_list(),
				$this->template_vars['SORT_KEYS_NAME']		=> $this->sort_key_name,
				$this->template_vars['SORT_DIR_NAME']		=> $this->sort_dir_name,

				$this->template_vars['TOTAL_ITEMS']			=> $this->total,
				$this->template_vars['TOTAL_RESULTS']		=> phpbb::$user->lang($this->result_lang, $this->total),
			));
		}

		return true;
	}
}
