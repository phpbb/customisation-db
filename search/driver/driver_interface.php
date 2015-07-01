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

interface driver_interface
{
	/**
	 * Initialise search engine.
	 *
	 * @return $this
	 */
	public function initialise();

	/**
	 * Check whether searching all types concurrently is supported.
	 *
	 * @return bool
	 */
	public function search_all_supported();

	/**
	 * Execute search query.
	 *
	 * @return array Returns array in form of array(
	 * 			'user_ids'	=> array(),
	 * 			'documents'	=> array(),
	 * 			'total'		=> int,
	 * 		)
	 */
	public function search();

	/**
	 * Delete an object from the index
	 *
	 * @param int $object_type
	 * @param int $object_id
	 * @return mixed
	 */
	public function delete($object_type, $object_id);

	/**
	 * Index an object
	 *
	 * @param int $object_type
	 * @param int $object_id
	 * @param array $data
	 * @return mixed
	 */
	public function index($object_type, $object_id, $data);

	/**
	 * Index multiple objects
	 *
	 * @param array $data
	 * @return mixed
	 */
	public function mass_index($data);

	/**
	 * Truncate the search index.
	 *
	 * @param int|bool $object_type Optional object type to limit to
	 * @return mixed
	 */
	public function truncate($object_type = false);

	/**
	 * Clean string
	 *
	 * @param string $string
	 * @return string
	 */
	public function clean_string($string);

	/**
	 * Start a new search query.
	 *
	 * @return $this
	 */
	public function new_search_query();

	/**
	 * Filter results by a set of property values.
	 *
	 * @param string $property
	 * @param array $values
	 * @return $this
	 */
	public function where_in_set($property, array $values);

	/**
	 * Filter results by a property value.
	 *
	 * @param string $property
	 * @param int|string $value
	 * @return $this
	 */
	public function where_equals($property, $value);

	/**
	 * Set object type to limit search to
	 *
	 * @param int $type TITANIA_CONTRIB|TITANIA_FAQ|TITANIA_SUPPORT
	 * @return $this
	 */
	public function set_type($type);

	/**
	 * Set granular restrictions on the type of object and contribution type
	 *
	 * @param array $restrictions	Restrictions in form of array(
	 * 		(int) object_type => array((int) contrib_type)
	 * )
	 * @return $this
	 */
	public function set_granular_type_restrictions(array $restrictions);

	/**
	 * Set search keywords.
	 *
	 * @param string $keywords		Keywords
	 * @param bool $search_title	Whether to search the document title
	 * @param bool $search_text		Whether to search the document text
	 * @return $this
	 */
	public function set_keywords($keywords, $search_title, $search_text);

	/**
	 * Set sort limits
	 *
	 * @param int $start		Offset
	 * @param int $per_page		Max number of items to return
	 * @return $this
	 */
	public function set_limit($start, $per_page);
}
