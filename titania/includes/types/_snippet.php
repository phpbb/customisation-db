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

class titania_type_snippet
{
	/**
	 * The type id
	 *
	 * @var int type id (for custom types not specified in titania to start, please start with 10 in case we add any extra later)
	 */
	public $id = 3;

	/**
	 * For the url slug
	 *
	 * @var string portion to be used in the URL slug
	 */
	public $url = 'snippet';

	/**
	 * The name of the field used to hold the number of this item in the authors table
	 *
	 * @var string author count
	 */
	public $author_count = 'author_snippets';

	/**
	 * The language key, initialize in constructor
	 *
	 * @var string Language key
	 */
	public $lang = '';

	public function __construct()
	{
		$this->lang = phpbb::$user->lang['SNIPPET'];
	}

	/**
	* Automatically install the type if required
	*
	* For adding type specific permissions, etc.  For now ignore
	*/
	public function auto_install()
	{
		return;
	}
}