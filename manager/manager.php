<?php
/**
*
* @package Customisation DB
* @copyright (c) 2025 phpBB
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\oberon\manager;

/**
 * Customisation DB Manager
 */
class manager
{
    // TODO: Hard-coded, fix this later
    const FILE_UPLOAD_LOCATION = '/workspaces/phpbb/phpBB/files/contributions/';

    const TYPE_EXTENSIONS = 1;
    const TYPE_STYLES = 2;
    const TYPE_TRANSLATIONS = 3;
    const TYPE_BBCODES = 4;
    const TYPE_TOOLS = 5;
    const TYPE_ARCHIVE = 6;

    // Filter
    const STATUS_UNVALIDATED = 1;
    const STATUS_APPROVED = 2;
    const STATUS_DENIED = 3;

    // Internal Customisation Team statuses
    const INTERNAL_STATUS_UNVALIDATED = 1;
    const INTERNAL_STATUS_AWAITING_AI_VALIDATION = 2;
    const INTERNAL_STATUS_COMPLETED_AI_VALIDATION = 3;
    const INTERNAL_STATUS_AWAITING_TESTING = 4;
    const INTERNAL_STATUS_COMPLETED_TESTING = 5;
    const INTERNAL_STATUS_DENIED = 6;
    const INTERNAL_STATUS_APPROVED = 7;

    // Sort
    const SORT_DATE = 1;
    const SORT_NAME = 2;

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

    /** @var \phpbb\extension\manager $ext_manager */
    protected $ext_manager;

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
    * @param \phpbb\extension\manager           $ext_manager
    * array                                     $tables
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\user $user, \phpbb\user_loader $user_loader, \phpbb\language\language $language, \phpbb\extension\manager $ext_manager, array $tables)
	{
        $this->db = $db;
		$this->config = $config;
		$this->user = $user;
        $this->user_loader = $user_loader;
        $this->language = $language;
        $this->ext_manager = $ext_manager;
        $this->tables = $tables;
	}

    /**
     * Return tables for Oberon
     */
    public function get_tables()
    {
        return $this->tables;
    }

    public function get_ext_manager()
    {
        return $this->ext_manager;
    }

    /*
        *** UI Queries ***
    */

    // Submit a new contribution
    public function add_contribution(array $contribution_array)
    {
        $sql = 'INSERT INTO ' . $this->tables['contributions'] . ' ' . $this->db->sql_build_array('INSERT', $contribution_array);
		$this->db->sql_query($sql);

		$contribution_id = (int) $this->db->sql_nextid();
        return $contribution_id;
    }

    // Submit a new revision
    public function add_revision(array $revision_array)
    {
        $sql = 'INSERT INTO ' . $this->tables['revisions'] . ' ' . $this->db->sql_build_array('INSERT', $revision_array);
		$this->db->sql_query($sql);

        $revision_id = (int) $this->db->sql_nextid();
        return $revision_id;
    }

    // Add revision to queue
    public function add_revision_to_queue(int $revision_id)
    {
        // Build the insert data array
        $sql_ary = [
            'revision_id'           => $revision_id,
            'queue_added_time'      => time(),
            'queue_status'          => self::INTERNAL_STATUS_UNVALIDATED, /* INTERNAL STATUS CHANGE */
            'queue_codespace_url'   => '',
        ];

        // Insert into the database
        $sql = 'INSERT INTO ' . $this->tables['queue'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
        $this->db->sql_query($sql);
    }

    // List the contributions on the index
    public function get_contributions_for_index(int $type = 0, int $status = 0, int $sort = 0)
    {
        $sql = 'SELECT *
                FROM ' . $this->tables['contributions'] . '
                WHERE contribution_id > 0';

        if ($status)
        {
            $sql .= ' AND contribution_status = ' . (int) $status;
        }

        if ($type)
        {
            $sql .= ' AND contribution_type = ' . (int) $type;  
        }

        switch ($sort)
        {
            case self::SORT_DATE:
                $sql .= ' ORDER BY submission_time DESC';
                break;
            case self::SORT_NAME:
                $sql .= ' ORDER BY contribution_name ASC';
                break;
            default:
                $sql .= ' ORDER BY contribution_id DESC';
                break;
        }

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

    /**
    * Fetch a single contribution and its latest revision
    *
    * @param int $contribution_id
    * @return array|null
    */
    public function get_contribution_with_latest_revision(int $contribution_id)
    {
        $contribution_id = (int) $contribution_id;

        if ($contribution_id <= 0)
        {
            return null;
        }

        // Get contribution
        $sql = 'SELECT c.*, u.username AS author_name
                FROM ' . $this->tables['contributions'] . ' c
                LEFT JOIN ' . USERS_TABLE . ' u
                    ON c.user_id = u.user_id
                WHERE c.contribution_id = ' . $contribution_id;

        $result = $this->db->sql_query($sql);
        $contribution = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if (!$contribution)
        {
            return null;
        }

        // Get newest revision
        $sql = 'SELECT *
                FROM ' . $this->tables['revisions'] . '
                WHERE contribution_id = ' . $contribution_id . '
                ORDER BY submission_time DESC';

        $result = $this->db->sql_query_limit($sql, 1);
        $revision = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        // Screenshots
        $screenshots = [];

        if (!empty($revision['revision_screenshots']))
        {
            $screenshots = explode(',', $revision['revision_screenshots']);
        }

        // Map status code to readable label
        $status_labels = [
            self::STATUS_UNVALIDATED => $this->user->lang('CUSTDB_STATUS_UNVALIDATED'),
            self::STATUS_APPROVED => $this->user->lang('CUSTDB_STATUS_APPROVED'),
            self::STATUS_DENIED => $this->user->lang('CUSTDB_STATUS_DENIED'),
        ];

        $status_label = $status_labels[$contribution['contribution_status']] ?? '';

        return [
            'contribution_id'          => $contribution['contribution_id'],
            'contribution_name'        => $contribution['contribution_name'],
            'contribution_description' => $contribution['contribution_description'],
            'contribution_demo_link'   => $contribution['contribution_demo_link'],
            'contribution_type'        => $contribution['contribution_type'],
            'contribution_status'      => $contribution['contribution_status'],
            'status_label'             => $status_label,
            'author_name'              => $contribution['author_name'],

            // Revision info
            'revision_id'              => $revision['revision_id'] ?? null,
            'revision_name'            => $revision['revision_name'] ?? '',
            'revision_version'         => $revision['revision_version'] ?? '',
            'revision_description'     => $revision['revision_description'] ?? '',
            'revision_attachment'      => $revision['revision_attachment'] ?? '',
            'screenshots'              => $screenshots,
        ];
    }

    /*
        *** CLI Queries ***
    */
    public function find_contribution_revision_for_queue_id(int $queue_id)
    {
        //TODO: inefficient sql below here, fix this later
        $queue_sql = 'SELECT revision_id FROM ' . $this->tables['queue'] . ' WHERE queue_id = ' . (int) $queue_id;
        $queue_result = $this->db->sql_query_limit($queue_sql, 1);
        $queue_row = $this->db->sql_fetchrow($queue_result);

        $revision_sql = 'SELECT * FROM ' . $this->tables['revisions'] . ' WHERE revision_id = ' . $queue_row['revision_id'];
        $revision_query = $this->db->sql_query_limit($revision_sql, 1);
        $revision_row = $this->db->sql_fetchrow($revision_query);

        $contribution_sql = 'SELECT * FROM ' . $this->tables['contributions'] . ' WHERE contribution_id = ' . $revision_row['contribution_id'];
        $contribution_query = $this->db->sql_query_limit($contribution_sql, 1);
        $contribution_row = $this->db->sql_fetchrow($contribution_query);

        return ['revision' => $revision_row, 'contribution' => $contribution_row];
    }

    public function find_queue_items_for_processing()
    {
        $sql = 'SELECT * FROM ' . $this->tables['queue'] . '
                ORDER BY queue_added_time ASC';

        $result = $this->db->sql_query($sql);

        $results = [];
        
        while ($row = $this->db->sql_fetchrow($result))
        {
            // Get the revision and contribution record
            $results[$row['queue_id']] = $this->find_contribution_revision_for_queue_id($row['queue_id']);
        }
        
        $this->db->sql_freeresult($result);

        return $results;
    }
}