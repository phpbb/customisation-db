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
 * Class to generate pagination
 *
 * @package Titania
 */
class pagination extends titania_object
{
	/**
	 * URL Params
	 *
	 * @var array
	 */
	private $params = array();

	/**
	 * Set some default variables, set template_vars default values
	 */
	public function __construct()
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'start'			=> array('default' => 0),
			'limit'			=> array('default' => DEFAULT_OFFSET_LIMIT),
			'default_limit'	=> array('default' => DEFAULT_OFFSET_LIMIT),
			'max_limit'		=> array('default' => MAX_OFFSET_LIMIT),
			'results'		=> array('default' => 0),
			'total_results'	=> array('default' => 0),
			'url'			=> array('default' => ''),
			'result_lang'	=> array('default' => 'RETURNED_RESULT'),
			'template_vars'	=> array(
				'default' => array(
					'TOTAL_ROWS'	=> 'TOTAL_ROWS',
					'PAGINATION'	=> 'PAGINATION',
					'PAGE_NUMBER'	=> 'PAGE_NUMBER',
					'S_MODE_ACTION'	=> 'S_MODE_ACTION',
				),
			),
		));
	}

	/**
	 * Set start variable for pagination
	 *
	 * @param string $start_name _REQUEST name used for start
	 * @return int	start
	 */
	public function set_start($start_name = 'start', $default = 0)
	{
		$this->start = request_var($start_name, (int) $default);

		return $this->start;
	}

	/**
	 * Set limit variable for pagination
	 *
	 * @param string $limit_name _REQUEST name used for limit
	 * @return int	$limit
	 */
	public function set_limit($limit_name = 'limit', $default = DEFAULT_OFFSET_LIMIT)
	{
		$limit = request_var($limit_name, (int) $default);
		$this->default_limit = $default;

		$this->limit = ($limit > $this->max_limit) ? $this->max_limit : $limit;
		return $this->limit;
	}

	/**
	 * Set URL parameters
	 *
	 * @param array $params
	 */
	public function set_params($params)
	{
		foreach ($params as $key => $value)
		{
			if ($value)
			{
				$key = (string) $key;
				$this->params[$key] = $key . '=' . (string) $value;
			}
		}
	}

	/**
	 * Set single URL parameter
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function set_param($key, $value)
	{
		$key = (string) $key;
		$this->params[$key] = $key . '=' . (string) $value;
	}

	/**
	 * set custom template variables
	 * options: TOTAL_ROWS, PAGINATION, PAGE_NUMBER and S_MODE_ACTION. Only specify those that need to be changed from default.
	 * Usage:
	 * <code>
	 * $pagination->set_template_vars(array(
	 * 		'TOTAL_ROWS'	=> 'TOTAL_STYLES',
	 * 		'PAGINATION'	=> 'U_PAGINATION',
	 * ));
	 * </code>
	 *
	 * @param unknown_type $template_vars
	 */
	public function set_template_vars($template_vars)
	{
		foreach ($template_vars as $key => $lang)
		{
			$this->template_vars[$key] = $lang;
		}
	}

	/**
	 * Set single template variable
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function set_template_var($key, $value)
	{
		$this->template_vars[$key] = $value;
	}

	/**
	 * Total result count based on sql_ary query
	 *
	 * @param array $sql_ary SQL array used for sql_build_query()
	 * @param string $field_name sql_field to count. i.e.: 'c.contrib_id'
	 */
	public function sql_total_count($sql_ary, $field_name, $results = 0)
	{
		global $db;

		// now count the number of results based on the perameters specified in sql_ary
		$sql_ary['SELECT'] = "COUNT($field_name) AS total_count";
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);
		$this->total_results = $db->sql_fetchfield('total_count');
		$db->sql_freeresult($result);

		if ($results)
		{
			$this->set_results($results);
		}

		return $this->total_results;
	}

	/**
	 * Build pagination and send to template
	 *
	 * @param string $page path/page to be used in pagination url
	 */
	public function build_pagination($page)
	{
		global $template, $user;

		$this->set_params(array(
			'limit'		=> ($this->limit == $this->default_limit) ? false : $this->limit,
		));

		$params = (sizeof($this->params)) ? implode('&amp;', $this->params) : '';

		$this->url = append_sid($page, $params);

		$results = ($this->results) ? $this->results : $this->total_results;
		$lang = ($this->total_results == 1) ? $user->lang[$this->result_lang] : $user->lang[$this->result_lang . 'S'];

		$template->assign_vars(array(
			$this->template_vars['TOTAL_ROWS']	=> sprintf($lang, $results, $this->total_results),
			$this->template_vars['PAGINATION']	=> generate_pagination($this->url, $this->total_results, $this->limit, $this->start),
			$this->template_vars['PAGE_NUMBER']	=> on_page($this->total_results, $this->limit, $this->start),

			$this->template_vars['S_MODE_ACTION']	=> $this->url,
		));

		return true;
	}
}
