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
class titania_pagination extends titania_object
{
	/**
	 * Constants
	 */
	const OFFSET_LIMIT_DEFAULT = 25;
	const OFFSET_LIMIT_MAX = 100;

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
			'total'			=> array('default' => 0),
			'result_lang'	=> array('default' => 'TOTAL_RESULTS'), // sprintf'd into 'TOTAL_RESULTS' output;  Should have TOTAL_RESULTS and TOTAL_RESULTS_ONE strings
			'template_vars'	=> array(
				'default' => array(
					'PAGINATION'	=> 'PAGINATION',
					'PAGE_NUMBER'	=> 'PAGE_NUMBER',
					'S_MODE_ACTION'	=> 'S_MODE_ACTION',
					'S_NUM_POSTS'	=> 'S_NUM_POSTS',
				),
			),
		));
	}

	/**
	 * Request function to run the start and limit grabbing functions
	 */
	public function request()
	{
		$this->get_start();
		$this->get_limit();
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
		$this->start = request_var($this->start_name, (int) $default);

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

		$limit = request_var($this->limit_name, (int) $this->default_limit);

		// Don't allow limits of 0 which is unlimited results. Instead use the max limit.
		$limit = ($limit == 0) ? $this->max_limit : $limit;

		// We don't allow the user to specify a limit higher than the maximum.
		$this->limit = ($limit > $this->max_limit) ? $this->max_limit : $limit;

		return $this->limit;
	}

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
	 * Build pagination and send to template
	 *
	 * @param string $page path/page to be used in pagination url
	 * @param string $tpl_prefix The tpl_prefix if you need to use one (in the generate_pagination function)
	 */
	public function build_pagination($page, $tpl_prefix = '')
	{
		$params = array();
		if ($this->limit != $this->default_limit)
		{
			$params[$this->limit_name] = $this->limit;
		}

		$pagination_url = titania_url::build_url($page, $params);

		phpbb::$template->assign_vars(array(
			$this->template_vars['PAGINATION']		=> $this->generate_pagination($pagination_url, false, false, false, true, $tpl_prefix),
			$this->template_vars['PAGE_NUMBER']		=> on_page($this->total, $this->limit, $this->start),

			$this->template_vars['S_MODE_ACTION']	=> $pagination_url,
			$this->template_vars['S_NUM_POSTS']		=> $this->total,
		));

		return true;
	}

	/**
	 * Generate pagination (similar to phpBB's generate_pagination function, only with some minor tweaks to work in this class better and use proper URLs)
	 *
	 * @param <string> $base_url
	 * @param <int|bool> $num_items Bool false to use $this->total
	 * @param <int|bool> $per_page Bool false to use $this->limit
	 * @param <int|bool> $start_item Bool false to use $this->start
	 * @param <bool> $add_prevnext_text
	 * @param <string|bool> $tpl_prefix
	 * @return <string>
	 */
	public function generate_pagination($base_url, $num_items = false, $per_page = false, $start_item = false, $add_prevnext_text = true, $tpl_prefix = '')
	{
		$num_items = ($num_items === false) ? $this->total : $num_items;
		$per_page = ($per_page === false) ? $this->limit : $per_page;
		$start_item = ($start_item === false) ? $this->start : $start_item;

		$seperator = '<span class="page-sep">' . phpbb::$user->lang['COMMA_SEPARATOR'] . '</span>';
		$total_pages = ceil($num_items / $per_page);
		$on_page = floor($start_item / $per_page) + 1;
		$page_string = '';

		if (!$num_items)
		{
			return false;
		}

		if ($total_pages > 1)
		{
			$page_string = ($on_page == 1) ? '<strong>1</strong>' : '<a href="' . $base_url . '">1</a>';

			if ($total_pages > 5)
			{
				$start_cnt = min(max(1, $on_page - 4), $total_pages - 5);
				$end_cnt = max(min($total_pages, $on_page + 4), 6);

				$page_string .= ($start_cnt > 1) ? ' ... ' : $seperator;

				for ($i = $start_cnt + 1; $i < $end_cnt; $i++)
				{
					$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . titania_url::append_url($base_url, array($this->start_name => (($i - 1) * $per_page))) . '">' . $i . '</a>';
					if ($i < $end_cnt - 1)
					{
						$page_string .= $seperator;
					}
				}

				$page_string .= ($end_cnt < $total_pages) ? ' ... ' : $seperator;
			}
			else
			{
				$page_string .= $seperator;

				for ($i = 2; $i < $total_pages; $i++)
				{
					$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . titania_url::append_url($base_url, array($this->start_name => (($i - 1) * $per_page))) . '">' . $i . '</a>';
					if ($i < $total_pages)
					{
						$page_string .= $seperator;
					}
				}
			}

			$page_string .= ($on_page == $total_pages) ? '<strong>' . $total_pages . '</strong>' : '<a href="' . titania_url::append_url($base_url, array($this->start_name => (($total_pages - 1) * $per_page))) . '">' . $total_pages . '</a>';

			if ($add_prevnext_text)
			{
				if ($on_page == 2)
				{
					$page_string = '<a href="' . $base_url . '">' . phpbb::$user->lang['PREVIOUS'] . '</a>&nbsp;&nbsp;' . $page_string;
				}
				else if ($on_page != 1)
				{
					$page_string = '<a href="' . titania_url::append_url($base_url, array($this->start_name => (($on_page - 2) * $per_page))) . '">' . phpbb::$user->lang['PREVIOUS'] . '</a>&nbsp;&nbsp;' . $page_string;
				}

				if ($on_page != $total_pages)
				{
					$page_string .= '&nbsp;&nbsp;<a href="' . titania_url::append_url($base_url, array($this->start_name => ($on_page * $per_page))) . '">' . phpbb::$user->lang['NEXT'] . '</a>';
				}
			}
		}

		if ($num_items == 1)
		{
			$total_results = (isset(phpbb::$user->lang[$this->result_lang . '_ONE'])) ? phpbb::$user->lang[$this->result_lang . '_ONE'] : phpbb::$user->lang['TOTAL_RESULTS_ONE'];
		}
		else
		{
			$total_results = (isset(phpbb::$user->lang[$this->result_lang])) ? sprintf(phpbb::$user->lang[$this->result_lang], $num_items) : sprintf(phpbb::$user->lang['TOTAL_RESULTS'], $num_items);
		}

		phpbb::$template->assign_vars(array(
			$tpl_prefix . 'BASE_URL'		=> $base_url,
			'A_' . $tpl_prefix . 'BASE_URL'	=> addslashes($base_url),
			$tpl_prefix . 'PER_PAGE'		=> $per_page,
			$tpl_prefix . 'ON_PAGE'			=> $on_page,

			$tpl_prefix . 'PREVIOUS_PAGE'	=> ($on_page == 2) ? $base_url : (($on_page == 1) ? '' : titania_url::append_url($base_url, array($this->start_name => (($on_page - 2) * $per_page)))),
			$tpl_prefix . 'NEXT_PAGE'		=> ($on_page == $total_pages) ? '' : titania_url::append_url($base_url, array($this->start_name => ($on_page * $per_page))),
			$tpl_prefix . 'TOTAL_PAGES'		=> $total_pages,
			$tpl_prefix . 'TOTAL_ITEMS'		=> $num_items,
			$tpl_prefix . 'TOTAL_RESULTS'	=> $total_results,
		));

		return $page_string;
	}
}
