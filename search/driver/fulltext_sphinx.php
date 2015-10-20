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

use phpbb\exception\http_exception;
use phpbb\titania\access;

class fulltext_sphinx extends base
{
	/** @var string */
	protected $search_query_prefix = '';

	/** @var string */
	protected $indexes;

	/** @var string */
	protected $keywords;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $posts_table;

	/** @var string */
	protected $topics_table;

	/** @var string */
	protected $faq_table;

	/** @var string */
	protected $contribs_table;

	/** @var string */
	protected $contrib_in_categories_table;

	/** @var \SphinxClient */
	protected $client;

	/** @var bool */
	protected $search_all_supported = false;

	/** @var array */
	protected $types = array(
		TITANIA_CONTRIB		=> 'contrib',
		TITANIA_FAQ			=> 'faq',
		TITANIA_SUPPORT		=> 'post',
	);

	const MAX_MATCHES = 20000;
	const CONNECT_RETRIES = 3;
	const CONNECT_WAIT_TIME = 300;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config $config
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\user $user
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\auth\auth $auth, \phpbb\user $user, $config_table, $phpbb_root_path, $php_ext)
	{
		$this->config = $config;
		$this->auth = $auth;
		$this->user = $user;
		$this->config_table = $config_table;
		$this->posts_table = TITANIA_POSTS_TABLE;
		$this->topics_table = TITANIA_TOPICS_TABLE;
		$this->faq_table = TITANIA_CONTRIB_FAQ_TABLE;
		$this->contribs_table = TITANIA_CONTRIBS_TABLE;
		$this->contrib_in_categories_table = TITANIA_CONTRIB_IN_CATEGORIES_TABLE;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @{inheritDoc}
	 */
	public function initialise()
	{
		if (!class_exists('SphinxClient'))
		{
			require($this->phpbb_root_path . 'includes/sphinxapi.' . $this->php_ext);
		}
		$this->client = new \SphinxClient;
		$this->client->SetServer(
			($this->config['fulltext_sphinx_host'] ? $this->config['fulltext_sphinx_host'] : 'localhost'),
			($this->config['fulltext_sphinx_port'] ? (int) $this->config['fulltext_sphinx_port'] : 9312)
		);

		$id = $this->get_id();
		$indexes = '';

		foreach ($this->types as $type)
		{
			$indexes .= "
				index_cdb_{$type}_{$id}_main;
				index_cdb_{$type}_{$id}_delta;
			";
		}
		$this->indexes = $indexes;

		return $this;
	}

	/**
	 * Get unique id for all indexes.
	 *
	 * @return string
	 */
	protected function get_id()
	{
		if (!$this->config['fulltext_sphinx_id'])
		{
			$this->config->set('fulltext_sphinx_id', unique_id());
		}
		return $this->config['fulltext_sphinx_id'];
	}

	/**
	 * @{inheritDoc}
	 */
	public function search()
	{
		$result = $this->get_result();

		// Could be connection to localhost:9312 failed (errno=111,
		// msg=Connection refused) during rotate, retry if so
		$retries = self::CONNECT_RETRIES;
		while (!$result && (strpos($this->client->GetLastError(), "errno=111,") !== false) && $retries--)
		{
			usleep(self::CONNECT_WAIT_TIME);
			$result = $this->get_result();
		}

		if ($this->client->GetLastError())
		{
			if ($this->auth->acl_get('a_'))
			{
				throw new http_exception(
					500,
					'SPHINX_SEARCH_FAILED',
					array($this->client->GetLastError())
				);
			}
			else
			{
				throw new http_exception(
					500,
					'SPHINX_SEARCH_FAILED_LOG'
				);
			}
		}

		$_result = array(
			'documents'	=> array(),
			'user_ids'	=> array(),
			'total'		=> 0,
		);

		if (!empty($result['matches']))
		{
			foreach ($result['matches'] as $data)
			{
				$attrs = $data['attrs'];
				$attrs['id'] = $attrs['real_id'];
				unset($attrs['real_id']);
				$_result['documents'][$attrs['type'] . '_' . $attrs['id']] = $attrs;
				$_result['user_ids'][] = $attrs['author'];
			}
			$_result['total'] = $result['total_found'];
		}
		return $_result;
	}

	/**
	 * Get query result
	 *
	 * @return array
	 */
	protected function get_result()
	{
		return $this->client->Query(
			$this->search_query_prefix . str_replace('&quot;', '"', $this->keywords),
			$this->indexes
		);
	}

	/**
	 * @{inheritDoc}
	 */
	public function delete($object_type, $object_id)
	{
	}

	/**
	 * @{inheritDoc}
	 */
	public function index($object_type, $object_id, $data)
	{
	}

	/**
	 * @{inheritDoc}
	 */
	public function mass_index($data)
	{
	}

	/**
	 * @{inheritDoc}
	 */
	public function truncate($object_type = false)
	{
		throw new http_exception(
			200,
			'<pre>' . $this->get_config() . '</pre>'
		);
	}

	/**
	 * @{inheritDoc}
	 */
	public function clean_string($string)
	{
		return $this->client->EscapeString($string);
	}

	/**
	 * @{inheritDoc}
	 */
	public function new_search_query()
	{
		$this->client->ResetFilters();
		return $this;
	}

	/**
	 * @{inheritDoc}
	 */
	public function where_in_set($property, array $values)
	{
		if (!empty($values))
		{
			$test = array_values($values)[0];

			if (is_int($test))
			{
				$this->client->setFilter($property, array_map('intval', $values));
			}
		}
		return $this;
	}

	/**
	 * @{inheritDoc}
	 */
	public function where_equals($property, $value)
	{
		if (is_int($value))
		{
			$this->client->setFilter($property, array($value));
		}

		return $this;
	}

	/**
	 * @{inheritDoc}
	 */
	public function set_keywords($keywords, $search_title, $search_text)
	{
		$this->keywords = $this->clean_string($keywords);

		if (!$search_title && $search_text)
		{
			$this->client->SetFieldWeights(array('title' => 1, 'data' => 5));
			$this->search_query_prefix = '@data ';
		}
		else if ($search_title && !$search_text)
		{
			$this->client->SetFieldWeights(array('title' => 5, 'data' => 1));
			$this->search_query_prefix = '@title ';
		}

		return $this;
	}

	/**
	 * @{inheritDoc}
	 */
	public function set_type($type)
	{
		if (isset($this->types[$type]))
		{
			$id = $this->get_id();
			$this->indexes = "
				index_cdb_{$this->types[$type]}_{$id}_main;
				index_cdb_{$this->types[$type]}_{$id}_delta
			";
			$this->where_equals('type', $type);
		}

		return $this;
	}

	/**
	 * {inheritDoc}
	 */
	public function set_granular_type_restrictions(array $restrictions)
	{
		$values = array();

		foreach ($restrictions as $search_type => $contrib_types)
		{
			foreach ($contrib_types as $type)
			{
				$values[] = (int) $search_type . 0 . (int) $type;
			}
		}

		return $this->where_in_set('type_ptype', $values);
	}

	/**
	 * @{inheritDoc}
	 */
	public function set_limit($start, $per_page)
	{
		$this->client->SetLimits((int) $start, (int) $per_page, self::MAX_MATCHES);
		return $this;
	}

	/**
	 * Get configuration for indexer.
	 *
	 * @return string
	 */
	public function get_config()
	{
		$post_query = '
			SELECT
				p.post_id + 20000000 AS id,
				p.post_id AS real_id,
				t.parent_id,
				p.post_user_id AS author,
				p.post_type AS type,
				p.post_access AS access_level,
				p.post_approved AS approved,
				p.post_time AS date,
				p.post_subject AS title,
				p.post_text AS data,
				CONCAT(CONCAT(p.post_type, "0"), t.topic_category) AS type_ptype
			FROM ' . $this->posts_table . ' p, ' . $this->topics_table . ' t
			WHERE p.topic_id = t.topic_id AND ';
		$contrib_query = '
			SELECT
				contrib_id AS id,
				contrib_id AS real_id,
				0 AS parent_id,
				contrib_user_id AS author,' .
				TITANIA_CONTRIB . ' AS type,' .
				access::PUBLIC_LEVEL . ' AS access_level,
				CASE contrib_status
					WHEN ' . TITANIA_CONTRIB_NEW . '
						OR ' . TITANIA_CONTRIB_HIDDEN . '
						OR ' . TITANIA_CONTRIB_DISABLED . '
					THEN 0
					ELSE 1
				END AS approved,
				contrib_last_update AS date,
				contrib_name AS title,
				contrib_desc AS data,
				CONCAT("' . TITANIA_CONTRIB . '0", contrib_type) AS type_ptype
			FROM ' . $this->contribs_table . '
			WHERE ';
		$categories_sql = '
			SELECT contrib_id AS id, category_id AS categories
			FROM ' . $this->contrib_in_categories_table . '
			WHERE ';
		$contrib_main_attributes = array(array(
			'sql_attr_multi',
			'uint categories from ranged-query;' .
				$categories_sql . 'contrib_id >= $start AND contrib_id <= $end;
				SELECT MIN(contrib_id), MAX(contrib_id) FROM '. $this->contrib_in_categories_table
		));
		$contrib_delta_attributes = array(array(
			'sql_attr_multi',
			'uint categories from query;' .
			$categories_sql . 'contrib_id >= ( ' . $this->get_config_select_sql('titania_max_contrib_id') . ' )'
		));

		$faq_query = '
			SELECT
				f.faq_id + 10000000 AS id,
				f.faq_id AS real_id,
				f.contrib_id AS parent_id,
				c.contrib_user_id AS author,' .
				TITANIA_FAQ . ' AS type,
				f.faq_access AS access_level,
				CASE c.contrib_status
					WHEN (' . TITANIA_CONTRIB_NEW . '
						OR ' . TITANIA_CONTRIB_HIDDEN . '
						OR ' . TITANIA_CONTRIB_DISABLED . ')
					THEN 0
					ELSE 1
				END AS approved,
				0 AS date,
				f.faq_subject AS title,
				f.faq_text AS data,
				CONCAT("' . TITANIA_FAQ . '0", c.contrib_type) AS type_ptype
			FROM ' . $this->faq_table . ' f, ' . $this->contribs_table . ' c
			WHERE f.contrib_id = c.contrib_id AND ';

		$post_config = $this->get_type_config(
			'post',
			$post_query,
			'p.post_id',
			$this->posts_table
		);
		$contrib_config = $this->get_type_config(
			'contrib',
			$contrib_query,
			'contrib_id',
			$this->contribs_table,
			$contrib_main_attributes,
			$contrib_delta_attributes
		);
		$faq_config = $this->get_type_config(
			'faq',
			$faq_query,
			'f.faq_id',
			$this->faq_table
		);

		$indexer = array('indexer' => array(
			array('mem_limit',					$this->config['fulltext_sphinx_indexer_mem_limit'] . 'M'),
		));

		$searchd = array('searchd' => array(
			array('compat_sphinxql_magics'	,	'0'),
			array('listen'	,					($this->config['fulltext_sphinx_host'] ? $this->config['fulltext_sphinx_host'] : 'localhost') . ':' . ($this->config['fulltext_sphinx_port'] ? $this->config['fulltext_sphinx_port'] : '9312')),
			array('log',						$this->config['fulltext_sphinx_data_path'] . 'log/searchd.log'),
			array('query_log',					$this->config['fulltext_sphinx_data_path'] . 'log/sphinx-query.log'),
			array('read_timeout',				'5'),
			array('max_children',				'30'),
			array('pid_file',					$this->config['fulltext_sphinx_data_path'] . 'searchd.pid'),
			array('max_matches',				(string) self::MAX_MATCHES),
			array('binlog_path',				$this->config['fulltext_sphinx_data_path']),
		));
		$data = array_merge(
			$post_config,
			$contrib_config,
			$faq_config,
			$indexer,
			$searchd
		);

		return $this->generate_config($data);
	}

	protected function get_config_select_sql($name)
	{
		return 'SELECT config_value
			FROM ' . $this->config_table . '
			WHERE config_name = "' . $name . '"';
	}

	protected function get_config_update_sql($name, $value)
	{
		return 'UPDATE ' . $this->config_table . '
			SET config_value = ' . $value . '
			WHERE config_name = "' . $name . '"';
	}
	/**
	 * Get configuration for main index.
	 *
	 * @param string $name
	 * @return array
	 */
	protected function get_config_index_main($name)
	{
		$name = 'cdb_' . $name . '_' . $this->get_id();
		return array("index index_{$name}_main" => array(
			array('path',	$this->config['fulltext_sphinx_data_path'] . "index_{$name}_main"),
			array('source', "source_{$name}_main"),
			array('docinfo', 'extern'),
			array('morphology', 'none'),
			array('stopwords',	''),
			array('min_word_len', 2),
			array('charset_type', 'utf-8'),
			array('charset_table', 'U+FF10..U+FF19->0..9, 0..9, U+FF41..U+FF5A->a..z, U+FF21..U+FF3A->a..z, A..Z->a..z, a..z, U+0149, U+017F, U+0138, U+00DF, U+00FF, U+00C0..U+00D6->U+00E0..U+00F6, U+00E0..U+00F6, U+00D8..U+00DE->U+00F8..U+00FE, U+00F8..U+00FE, U+0100->U+0101, U+0101, U+0102->U+0103, U+0103, U+0104->U+0105, U+0105, U+0106->U+0107, U+0107, U+0108->U+0109, U+0109, U+010A->U+010B, U+010B, U+010C->U+010D, U+010D, U+010E->U+010F, U+010F, U+0110->U+0111, U+0111, U+0112->U+0113, U+0113, U+0114->U+0115, U+0115, U+0116->U+0117, U+0117, U+0118->U+0119, U+0119, U+011A->U+011B, U+011B, U+011C->U+011D, U+011D, U+011E->U+011F, U+011F, U+0130->U+0131, U+0131, U+0132->U+0133, U+0133, U+0134->U+0135, U+0135, U+0136->U+0137, U+0137, U+0139->U+013A, U+013A, U+013B->U+013C, U+013C, U+013D->U+013E, U+013E, U+013F->U+0140, U+0140, U+0141->U+0142, U+0142, U+0143->U+0144, U+0144, U+0145->U+0146, U+0146, U+0147->U+0148, U+0148, U+014A->U+014B, U+014B, U+014C->U+014D, U+014D, U+014E->U+014F, U+014F, U+0150->U+0151, U+0151, U+0152->U+0153, U+0153, U+0154->U+0155, U+0155, U+0156->U+0157, U+0157, U+0158->U+0159, U+0159, U+015A->U+015B, U+015B, U+015C->U+015D, U+015D, U+015E->U+015F, U+015F, U+0160->U+0161, U+0161, U+0162->U+0163, U+0163, U+0164->U+0165, U+0165, U+0166->U+0167, U+0167, U+0168->U+0169, U+0169, U+016A->U+016B, U+016B, U+016C->U+016D, U+016D, U+016E->U+016F, U+016F, U+0170->U+0171, U+0171, U+0172->U+0173, U+0173, U+0174->U+0175, U+0175, U+0176->U+0177, U+0177, U+0178->U+00FF, U+00FF, U+0179->U+017A, U+017A, U+017B->U+017C, U+017C, U+017D->U+017E, U+017E, U+0410..U+042F->U+0430..U+044F, U+0430..U+044F, U+4E00..U+9FFF'),
			array('min_prefix_len', 0),
			array('min_infix_len', 0),
		));
	}

	/**
	 * Get configuration for delta index.
	 *
	 * @param string $name
	 * @return array
	 */
	protected function get_config_index_delta($name)
	{
		$name = 'cdb_' . $name . '_' . $this->get_id();
		return array("index index_{$name}_delta : index_{$name}_main" => array(
			array('path',	$this->config['fulltext_sphinx_data_path'] . "index_{$name}_delta"),
			array('source',	"source_{$name}_delta"),
		));
	}

	/**
	 * Get configuration for main source.
	 *
	 * @param string $name
	 * @param string $query
	 * @param string $field
	 * @param string $table
	 * @param array $extra_attributes
	 * @return array
	 */
	protected function get_config_source_main($name, $query, $field, $table, $extra_attributes = array())
	{
		return array('source source_cdb_' . $name . '_' . $this->get_id() . '_main' => array_merge(array(
			array('type', 				'mysql # mysql or pgsql'),
			array('sql_host',	 		'# SQL server host sphinx connects to'),
			array('sql_user',	 		'[SQL User]'),
			array('sql_pass',			'[SQL Pass]'),
			array('sql_db',				'[SQL DB]'),
			array('sql_port',			'# optional, default is 3306 for mysql and 5432 for pgsql'),
			array('sql_query_pre',		"SET NAMES 'utf8'"),
			array('sql_range_step', 	5000),
			array('sql_query_pre',		$this->get_config_update_sql("titania_max_{$name}_id",
				'(SELECT MAX(' . $name . '_id) FROM ' . $table . ')')
			),
			array('sql_query_range',	'SELECT MIN(' . $name . '_id), MAX(' . $name . '_id) FROM ' . $table),
			array('sql_query', $query .
						$field . ' >= $start AND ' . $field . ' <= $end
			'),
			array('sql_query_post',			''),
			array('sql_query_post_index',	$this->get_config_update_sql("titania_max_{$name}_id", '$maxid')),
			array('sql_query_info',			'SELECT * FROM ' . $table . ' WHERE ' . $name . '_id = $id'),
		), $this->get_attributes(), $extra_attributes));
	}

	/**
	 * Get configuration for delta source.
	 *
	 * @param string $name
	 * @param string $query
	 * @param string $field
	 * @param array $extra_attributes
	 * @return array
	 */
	protected function get_config_source_delta($name, $query, $field, $extra_attributes)
	{
		$prefix = 'source_cdb_' . $name . '_' . $this->get_id() . '_';
		return array("source {$prefix}delta : {$prefix}main" => array_merge(array(
			array('sql_query_pre',		''),
			array('sql_query_range',	''),
			array('sql_range_step',		''),
			array('sql_query', $query . "
						$field >=  ( " . $this->get_config_select_sql("titania_max_{$name}_id") . " )
			"),
		), $this->get_attributes(), $extra_attributes));
	}

	/**
	 * Get source attributes.
	 *
	 * @return array
	 */
	protected function get_attributes()
	{
		return array(
			array('sql_attr_uint',			'real_id'),
			array('sql_attr_uint',			'parent_id'),
			array('sql_attr_uint',			'author'),
			array('sql_attr_uint',			'type'),
			array('sql_attr_uint',			'access_level'),
			array('sql_attr_bool',			'approved'),
			array('sql_attr_timestamp',		'date'),
			array('sql_attr_uint',			'type_ptype'),
		);
	}

	/**
	 * Get configuration for object type.
	 *
	 * @param string $type
	 * @param string $query
	 * @param string $field
	 * @param string $table
	 * @param array $main_attributes
	 * @param array $delta_attributes
	 * @return array
	 */
	protected function get_type_config($type, $query, $field, $table, $main_attributes = array(), $delta_attributes = array())
	{
		return array_merge(
			$this->get_config_source_main($type, $query, $field, $table, $main_attributes),
			$this->get_config_source_delta($type, $query, $field, $delta_attributes),
			$this->get_config_index_main($type),
			$this->get_config_index_delta($type)
		);
	}

	/**
	 * Generate indexer configuration.
	 *
	 * @param array $data
	 * @return string
	 */
	protected function generate_config($data)
	{
		$config_object = new \phpbb\search\sphinx\config('');
		$non_unique = array('sql_query_pre' => true, 'sql_attr_uint' => true, 'sql_attr_timestamp' => true, 'sql_attr_str2ordinal' => true, 'sql_attr_bool' => true);
		$delete = array('sql_group_column' => true, 'sql_date_column' => true, 'sql_str2ordinal_column' => true);
		foreach ($data as $section_name => $section_data)
		{
			$section = $config_object->get_section_by_name($section_name);
			if (!$section)
			{
				$section = $config_object->add_section($section_name);
			}

			foreach ($delete as $key => $void)
			{
				$section->delete_variables_by_name($key);
			}

			foreach ($non_unique as $key => $void)
			{
				$section->delete_variables_by_name($key);
			}

			foreach ($section_data as $entry)
			{
				$key = $entry[0];
				$value = $entry[1];

				if (!isset($non_unique[$key]))
				{
					$variable = $section->get_variable_by_name($key);
					if (!$variable)
					{
						$variable = $section->create_variable($key, $value);
					}
					else
					{
						$variable->set_value($value);
					}
				}
				else
				{
					$variable = $section->create_variable($key, $value);
				}
			}
		}
		return $config_object->get_data();
	}
}
