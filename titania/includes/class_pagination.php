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
class pagination
{
	/**
	 * start
	 *
	 * @var int
	 */
	protected $start = 0;

	/**
	 * limit number of results to display on page
	 *
	 * @var int
	 */
	protected $limit = 20;

	/**
	 * total results/rows/count
	 *
	 * @var int
	 */
	protected $total_results = 0;

	/**
	 * pagination url
	 *
	 * @var string
	 */
	protected $url = '';

	/**
	 * result language var, e.g.: TOTAL_ROW, plural language var appended automatically
	 *
	 * @var string
	 */
	protected $result_lang = '';

	/**
	 * Template vars, set automatically but may be changed if necessary.
	 *
	 * @var array
	 */
	protected $template_vars = array();

	/**
	 * default results/rows to display
	 *
	 * @var int
	 */
	protected $default_limit = 20;

	/**
	 * Maximimum definable limit allowed
	 *
	 * @var int
	 */
	protected $max_limit = 100;

	/**
	 * params array
	 *
	 * @var array
	 */
	protected $params = array();

	/**
	 * Set some default variables, set template_vars default values
	 */
	public function __construct()
	{
		$this->template_vars = array(
			'TOTAL_ROWS'	=> 'TOTAL_ROWS',
			'PAGINATION'	=> 'PAGINATION',
			'PAGE_NUMBER'	=> 'PAGE_NUMBER',
			'S_MODE_ACTION'	=> 'S_MODE_ACTION',
		);
	}

	/**
	 * Set start variable for pagination
	 *
	 * @param string $start_name _REQUEST name used for start
	 * @return int	start
	 */
	public function set_start($start_name = 'start')
	{
		$this->start = request_var($start_name, 0);

		return $this->start;
	}

	/**
	 * Set limit variable for pagination
	 *
	 * @param string $limit_name _REQUEST name used for limit
	 * @return int	$limit
	 */
	public function set_limit($limit_name = 'limit')
	{
		$limit = request_var($limit_name = 'limit');

		$this->limit = ($limit > $this->max_limit) ? $this->max_limit : $limit;
		return $this->limit;
	}

	public function set_params($params)
	{
		foreach ($params as $key => $value)
		{
			$this->params[$key] = $value;
		}

		return true;
	}

	/**
	 * Set total_results, generally from config or SQL COUNT() Query
	 *
	 * @param int $total_results
	 */
	public function set_total_results($total_results)
	{
		$this->total_results = $total_results;

		return true;
	}

	/**
	 * set result language var, e.g.: TOTAL_ROW, plural language var appended automatically
	 *
	 * @param string $lang_var
	 */
	public function set_result_lang($lang_var)
	{
		$this->result_lang = $lang_var;

		return true;
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

		return true;
	}

	/**
	 * Total result count based on sql_ary query
	 *
	 * @param array $sql_ary SQL array used for sql_build_query()
	 * @param string $field_name sql_field to count. i.e.: 'c.contrib_id'
	 */
	public function sql_total_count($sql_ary, $field_name)
	{
		global $db;

		// now count the number of results based on the perameters specified in sql_ary
		$sql_ary['SELECT'] = "COUNT($field_name) AS total_count";
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);
		$this->total_results = $db->sql_fetchfield('total_count');
		$db->sql_freeresult($result);

		return $this->total_results;
	}

	/**
	 * Build pagination and send to template
	 *
	 * @param string $page path/page to be used in pagination url
	 */
	public function build_pagination($page)
	{
		global $template;

		$params = (sizeof($this->params)) ? implode('&amp;', $this->params) : '';

		$this->url = append_sid($page, $params);

		$template->assign_vars(array(
			$this->template_vars['TOTAL_ROWS']	=> ($this->total_results == 1) ? $user->lang[$this->result_lang] : sprintf($user->lang[$this->result_lang . 'S'], $this->total_results),
			$this->template_vars['PAGINATION']	=> generate_pagination($this->url, $this->total_results, $this->limit, $this->start),
			$this->template_vars['PAGE_NUMBER']	=> on_page($this->total_results, $this->limit, $this->start),

			$this->template_vars['S_MODE_ACTION']	=> $this->url,
		));

		return true;
	}
}
