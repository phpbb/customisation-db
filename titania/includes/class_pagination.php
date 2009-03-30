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
	 * Constants
	 */
	const OFFSET_LIMIT_DEFAULT = 25;
	const OFFSET_LIMIT_MAX = 100;

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
			'limit'			=> array('default' => self::OFFSET_LIMIT_DEFAULT),
			'limit_name'	=> array('default' => 'limit'),
			'default_limit'	=> array('default' => self::OFFSET_LIMIT_DEFAULT),
			'max_limit'		=> array('default' => self::OFFSET_LIMIT_MAX),
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
	 * @param int $default custom start param
	 *
	 * @return int	start
	 */
	public function get_start($default = 0)
	{
		$this->start = request_var('start', (int) $default);

		return $this->start;
	}

	/**
	 * Set limit variable for pagination
	 *
	 * @param int $default default Offset/Limit -- uses the constant if unset.
	 * @param string $limit_name set a custom 'limit' param key name
	 *
	 * @return int	$limit
	 */
	public function get_limit($default = self::OFFSET_LIMIT_DEFAULT, $limit_name = 'limit')
	{
		$limit = request_var($limit_name, (int) $default);

		$this->default_limit = $default;
		$this->limit_name = $limit_name;

		// Don't allow limits of 0 which is unlimited results. Instead use the max limit.
		$limit = ($limit == 0) ? $this->max_limit : $limit;

		// We don't allow the user to specify a limit higher than the maximum.
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
				$this->params[(string) $key] = (string) $value;
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
		$this->params[(string) $key] = (string) $value;
	}

	/**
	 * The phpBB generate_pagination function always appends the start parameter to the URL.
	 * Therefore we ensure that we don't pass this param in those functions if pagination_url is set to false
	 *
	 * @param string $page
	 * @param bool $pagination_url set to true if being passed to the generate_pagination function
	 *
	 * @return string
	 */
	private function get_url($page, $pagination_url = false)
	{
		$params = $this->params;

		if ($pagination_url)
		{
			unset($params['start']);
		}

		return (sizeof($params)) ? append_sid($page, $params) : append_sid($page);
	}

	/**
	 * Set custom template variables
	 *
	 * Options: TOTAL_ROWS, PAGINATION, PAGE_NUMBER and S_MODE_ACTION.
	 *	Only specify those that need to be changed from default.
	 *
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
			$this->limit_name	=> ($this->limit == $this->default_limit) ? false : $this->limit,
			'start'				=> ($this->start == 0) ? false : $this->start,
		));

		$this->url = $this->get_url($page);
		$pagination_url = $this->get_url($page, true);

		$results = ($this->results) ? $this->results : $this->total_results;
		$lang = ($this->total_results == 1) ? $user->lang[$this->result_lang] : $user->lang[$this->result_lang . 'S'];

		$template->assign_vars(array(
			$this->template_vars['TOTAL_ROWS']	=> sprintf($lang, $results, $this->total_results),
			$this->template_vars['PAGINATION']	=> generate_pagination($pagination_url, $this->total_results, $this->limit, $this->start),
			$this->template_vars['PAGE_NUMBER']	=> on_page($this->total_results, $this->limit, $this->start),

			$this->template_vars['S_MODE_ACTION']	=> $this->url,
		));

		return true;
	}
}
