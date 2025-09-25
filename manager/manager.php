<?php
/**
*
* @package Customisation DB
* @copyright (c) 2025 phpBB
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace battye\custdb\manager;

/**
 * Customisation DB Manager
 */
class manager
{
    const TYPE_EXTENSIONS = 1;
    const TYPE_STYLES = 2;
    const TYPE_TRANSLATIONS = 3;
    const TYPE_BBCODES = 4;
    const TYPE_TOOLS = 5;
    const TYPE_ARCHIVE = 6;

    /* @var \phpbb\db\driver\driver_interface $db */
    protected $db;

	/* @var \phpbb\config\config $config */
	protected $config;

	/* @var \phpbb\user $user */
	protected $user;

    /** @var \phpbb\user_loader $user_loader */
	protected $user_loader;

    /** @var \phpbb\language\language $language */
    protected $language;

    /* @var array $tables */
    protected $tables;

	/**
	* Constructor
	*
    * @param \phpbb\db\driver\driver_interface  $db
	* @param \phpbb\config\config		        $config
	* @param \phpbb\user				        $user
    * @param \phpbb\user_loader                 $user_loader
    * @param \phpbb\language\language           $language
    * array                                     $tables
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\user $user, \phpbb\user_loader $user_loader, \phpbb\language\language $language, array $tables)
	{
        $this->db = $db;
		$this->config = $config;
		$this->user = $user;
        $this->user_loader = $user_loader;
        $this->language = $language;
        $this->tables = $tables;
	}

    // Queries
    public function get_contributions_for_index()
    {
        $sql = 'SELECT *
                FROM ' . $this->tables['contributions'] . '
                ORDER BY contribution_id ASC';

        $result = $this->db->sql_query($sql);
        $contributions = [];

        while ($row = $this->db->sql_fetchrow($result))
        {
            $contributions[] = [
                'contribution_id'           => $row['contribution_id'],
                'contribution_name'         => $row['contribution_name'],
                'contribution_description'  => $row['contribution_description'],
            ];
        }

        $this->db->sql_freeresult($result);

        return $contributions;   
    }
}