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

namespace phpbb\titania\search;

use \ezcSearchDocumentDefinition as definition;
use \ezcSearchDefinitionDocumentField as field;

class document implements \ezcBasePersistable, \ezcSearchDefinitionProvider
{
	public $id;
	public $parent_id;
	public $title;
	public $text;
	public $text_uid;
	public $text_bitfield;
	public $text_options;
	public $date;
	public $author;
	public $url;
	public $type;
	public $access_level;
	public $approved;
	public $reported;
	public $categories;
	public $phpbb_versions;
	public $parent_contrib_type;

	public function __construct() {}

	public function getState()
	{
		$state = array(
			'id'				=> $this->id,
			'parent_id'			=> (int) $this->parent_id,
			'title'				=> $this->title,
			'text'				=> $this->text,
			'text_uid'			=> $this->text_uid,
			'text_bitfield'		=> $this->text_bitfield,
			'text_options'		=> (int) $this->text_options,
			'author'			=> (int) $this->author,
			'date'				=> (int) $this->date,
			'url'				=> $this->url,
			'type'				=> (int) $this->type,
			'access_level'		=> (int) $this->access_level,
			'approved'			=> ($this->approved) ? 1 : 0,
			'reported'			=> ($this->reported) ? 1 : 0,
			'categories'		=> ($this->categories) ? $this->categories: array(),
			'phpbb_versions' 	=> ($this->phpbb_versions) ? $this->phpbb_versions : array(),
			'parent_contrib_type' => ($this->parent_contrib_type) ? $this->parent_contrib_type : 0,
		);
		return $state;
	}

	public function setState(array $state)
	{
		foreach ($state as $key => $value)
		{
			$this->$key = $value;
		}
	}

	static public function getDefinition()
	{
		$doc = new definition( __CLASS__ );

		$doc->idProperty = 'id';

		$doc->fields['id']				= new field('id', definition::TEXT);
		$doc->fields['parent_id']		= new field('parent_id', definition::INT);
		$doc->fields['type']			= new field('type', definition::INT);

		$doc->fields['title']			= new field('title', definition::TEXT, 2, true, false, true);
		$doc->fields['text']			= new field('text', definition::TEXT, 1, true, false, true);
		$doc->fields['text_uid']		= new field('text_uid', definition::STRING, 0);
		$doc->fields['text_bitfield']	= new field('text_bitfield', definition::STRING, 0);
		$doc->fields['text_options']	= new field('text_options', definition::INT, 0);

		$doc->fields['author']			= new field('author', definition::INT);
		$doc->fields['date']			= new field('date', definition::INT);
		$doc->fields['url']				= new field('url', definition::STRING, 0);

		$doc->fields['access_level']	= new field('access_level', definition::INT);
		$doc->fields['approved']		= new field('approved', definition::INT);
		$doc->fields['reported']		= new field('reported', definition::INT);

		$doc->fields['categories']		= new field('categories', definition::INT, 0, false, true);
		$doc->fields['phpbb_versions']	= new field('phpbb_versions', definition::STRING, 0, false, true);
		$doc->fields['parent_contrib_type'] = new field('parent_contrib_type', definition::INT);

		return $doc;
	}
}
