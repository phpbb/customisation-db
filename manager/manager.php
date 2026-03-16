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
    // TODO: Hard-coded, fix this later!!!!
    const FILE_UPLOAD_LOCATION = '/workspaces/phpbb/phpBB/files/contributions/';
    const SUPPORTED_PHPBB_VERSIONS = ['4.0.0', '3.3.15'];

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

	/* @var string $root_path */
	protected $root_path;

	/* @var string $php_ext */
	protected $php_ext;

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

	/** @var \phpbb\auth\auth */
	protected $auth;

    /** @var \phpbb\extension\manager $ext_manager */
    protected $ext_manager;

    /* @var array $tables */
    protected $tables;

	/**
	* Constructor
	*
    * @param string                             $root_path,
    * @param string                             $php_ext,    
    * @param \phpbb\db\driver\driver_interface  $db
	* @param \phpbb\config\config		        $config
	* @param \phpbb\user				        $user
    * @param \phpbb\user_loader                 $user_loader
    * @param \phpbb\language\language           $language
    * @param \phpbb\auth\auth                   $auth,
    * @param \phpbb\extension\manager           $ext_manager
    * array                                     $tables
	*/
	public function __construct(string $root_path, string $php_ext, \phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\user $user, \phpbb\user_loader $user_loader, \phpbb\language\language $language, \phpbb\auth\auth $auth, \phpbb\extension\manager $ext_manager, array $tables)
	{
        $this->root_path = $root_path;
        $this->php_ext = $php_ext;
        $this->db = $db;
		$this->config = $config;
		$this->user = $user;
        $this->user_loader = $user_loader;
        $this->language = $language;
        $this->auth = $auth;
        $this->ext_manager = $ext_manager;
        $this->tables = $tables;
	}

    public function is_team_member()
    {
        // Is the current user a phpBB team member?

        return true; // TODO: add logic here
    }

    public function is_customisation_author(int $contribution_id)
    {
        // Is the current user the author of the customisation?

        return true; // TODO: add logic here
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

    public function get_external_status(int $external_status)
    {
        $language_string = '';

        switch ($external_status)
        {
            case self::STATUS_UNVALIDATED:
                $language_string = $this->user->lang('CUSTDB_STATUS_UNVALIDATED');
                break;
            case self::STATUS_APPROVED:
                $language_string = $this->user->lang('CUSTDB_STATUS_APPROVED');
                break;
            case self::STATUS_DENIED:
                $language_string = $this->user->lang('CUSTDB_STATUS_DENIED');
                break;
        }

        return $language_string;
    }

    public function get_internal_status(?int $internal_status)
    {
        switch ($internal_status)
        {
            case self::INTERNAL_STATUS_UNVALIDATED:
                $language_string = $this->user->lang('CUSTDB_INTERNAL_STATUS_UNVALIDATED');
                break;
            case self::INTERNAL_STATUS_AWAITING_AI_VALIDATION:
                $language_string = $this->user->lang('CUSTDB_INTERNAL_STATUS_AWAITING_AI_VALIDATION');
                break;
            case self::INTERNAL_STATUS_COMPLETED_AI_VALIDATION:
                $language_string = $this->user->lang('CUSTDB_INTERNAL_STATUS_COMPLETED_AI_VALIDATION');
                break;
            case self::INTERNAL_STATUS_AWAITING_TESTING:
                $language_string = $this->user->lang('CUSTDB_INTERNAL_STATUS_AWAITING_TESTING');
                break;
            case self::INTERNAL_STATUS_COMPLETED_TESTING:
                $language_string = $this->user->lang('CUSTDB_INTERNAL_STATUS_COMPLETED_TESTING');
                break;
            case self::INTERNAL_STATUS_DENIED:
                $language_string = $this->user->lang('CUSTDB_INTERNAL_STATUS_DENIED');
                break;
            case self::INTERNAL_STATUS_APPROVED:
                $language_string = $this->user->lang('CUSTDB_INTERNAL_STATUS_APPROVED');
                break;
            default:
                $language_string = '';
        }

        return $language_string;
    }

    /*
        *** UI Queries ***
    */
    // Update the external validation status of a contribution
    public function update_external_validation_status(int $contribution_id, int $contribution_status)
    {
        // Build the update status data array
        $sql_array = [
            'contribution_status' => $contribution_status,
        ];

        $sql = 'UPDATE ' . $this->tables['contributions'] . ' 
                SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' 
                WHERE contribution_id = ' . (int) $contribution_id;

        $this->db->sql_query($sql);

        // Update the internal status accordingly.
        $internal_status = null;
        switch ($contribution_status)
        {
            case self::STATUS_UNVALIDATED:
                $internal_status = self::INTERNAL_STATUS_UNVALIDATED;
                break;
            case self::STATUS_APPROVED:
                $internal_status = self::INTERNAL_STATUS_APPROVED;
                break;
            case self::STATUS_DENIED:
                $internal_status = self::INTERNAL_STATUS_DENIED;
                break;
        }

        // TODO: SQL for updating the internal status to go here
    }

    public function update_internal_queue_status(int $queue_id, int $queue_status)
    {
        /* INTERNAL STATUS CHANGE */
        $sql = 'UPDATE ' . $this->tables['queue'] . ' SET queue_status = ' . (int) $queue_status . ' WHERE queue_id = ' . (int) $queue_id;
		$this->db->sql_query($sql);
    }

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
    public function get_contributions_for_index(int $type = 0, int $status = 0, int $sort = 0, string $search_query = '')
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

        if ($search_query !== '')
        {
            // Check name and description for a match: https://area51.phpbb.com/docs/dev/master/db/dbal.html#sql-like-expression
            $escaped_search = $this->db->sql_like_expression($this->db->get_any_char() . $this->db->sql_escape($search_query) . $this->db->get_any_char());
            $sql .= ' AND (contribution_name ' . $escaped_search . ' OR contribution_description ' . $escaped_search . ')';  
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
            // Get the status of the latest revision so we can colour code it on the page for team members to see
            // TODO: this is inefficient because it's getting the contribution for a second time in the function call below
            $latest_revision = $this->get_contribution_with_revision($row['contribution_id'], true);

            $contributions[] = [
                'contribution_id'           => $row['contribution_id'],
                'contribution_name'         => $row['contribution_name'],
                'contribution_description'  => $row['contribution_description'],

                'revision_status'           => $latest_revision['queue_status'],
            ];
        }

        $this->db->sql_freeresult($result);

        return $contributions;   
    }

    /**
    * Fetch a single contribution and its latest revision (or a specific revision)
    *
    * @param int $contribution_id
    * @param bool $latest_revision
    * @param int $revision_id
    * @return array|null
    */
    public function get_contribution_with_revision(int $contribution_id, bool $latest_revision = true, int $revision_id = 0)
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

        if ($latest_revision)
        {
            // Get newest revision
            $sql = 'SELECT r.*, q.queue_status
                    FROM ' . $this->tables['revisions'] . ' r
                    LEFT JOIN ' . $this->tables['queue'] . ' q
                        ON r.revision_id = q.revision_id
                    WHERE r.contribution_id = ' . $contribution_id . '
                    ORDER BY r.submission_time DESC';
        }

        else 
        {
            // Get specific revision
            $sql = 'SELECT r.*, q.queue_status
                    FROM ' . $this->tables['revisions'] . ' r
                    LEFT JOIN ' . $this->tables['queue'] . ' q
                        ON r.revision_id = q.revision_id
                    WHERE r.contribution_id = ' . $contribution_id . '
                        AND r.revision_id = ' . $revision_id . '
                    ORDER BY r.revision_id = ' . (int) $revision_id;
        }

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
        $internal_status_label = $this->is_team_member() ? $this->get_internal_status($revision['queue_status']) : '';
        $external_status_label = $this->get_external_status($contribution['contribution_status']);

        return [
            'contribution_id'           => $contribution['contribution_id'],
            'contribution_name'         => $contribution['contribution_name'],
            'contribution_description'  => $contribution['contribution_description'],
            'contribution_demo_link'    => $contribution['contribution_demo_link'],
            'contribution_type'         => $contribution['contribution_type'],
            'contribution_status'       => $contribution['contribution_status'],
            'external_status_label'     => $external_status_label,
            'internal_status_label'     => $internal_status_label,
            'author_name'               => $contribution['author_name'],

            // Revision info
            'revision_id'               => $revision['revision_id'] ?? null,
            'revision_name'             => $revision['revision_name'] ?? '',
            'revision_version'          => $revision['revision_version'] ?? '',
            'revision_description'      => $revision['revision_description'] ?? '',
            'revision_attachment'       => $revision['revision_attachment'] ?? '',
            'queue_status'              => $revision['queue_status'],
            'screenshots'               => $screenshots,
        ];
    }

    /**
     * Get all revisions for a specific contribution.
     *
     * @param int $contribution_id
     * @return array
     */
    public function get_revisions_for_contribution(int $contribution_id)
    {
        $contribution_id = (int) $contribution_id;

        if ($contribution_id <= 0)
        {
            return [];
        }

        $sql = 'SELECT r.*, q.queue_status
                FROM ' . $this->tables['revisions'] . ' r
                LEFT JOIN ' . $this->tables['queue'] . ' q
                    ON r.revision_id = q.revision_id
                WHERE r.contribution_id = ' . $contribution_id . '
                ORDER BY r.submission_time DESC';

        $result = $this->db->sql_query($sql);
        $revisions = [];

        while ($row = $this->db->sql_fetchrow($result))
        {
            $revisions[] = [
                'revision_id'       => $row['revision_id'],
                'revision_name'     => $row['revision_name'],
                'revision_version'  => $row['revision_version'],
                'revision_description' => $row['revision_description'],
                'queue_status'      => $row['queue_status'],
                'submission_time'   => $row['submission_time'],
            ];
        }

        $this->db->sql_freeresult($result);

        return $revisions;
    }

    /*
        *** CLI Queries ***
    */
    public function find_contribution_revision_for_queue_id(int $queue_id)
    {
        //TODO: inefficient sql below here, fix this later!!!
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




    /**
     *  New posts and topics 
     **/
    public function new_topic(int $forum_id, string $topic_subject, string $topic_text)
    {
        include_once($this->root_path . 'includes/functions_posting.' . $this->php_ext);

        // TODO: User ID (Customisations Robot?)
        $customisation_robot_user_id = 2;

        $this->user_loader->load_users([$customisation_robot_user_id]);
        $this->user->data = $this->user_loader->get_user($customisation_robot_user_id);
        $this->user->data['is_registered'] = true;
        $this->user->data['is_anonymous']  = false;
        $this->user->data['is_bot']        = false;

        $forum_sql = 'SELECT forum_name, forum_desc, forum_type
            FROM ' . FORUMS_TABLE . '
            WHERE forum_id = ' . (int) $forum_id;
        $forum_result = $this->db->sql_query($forum_sql);
        $forum_data = $this->db->sql_fetchrow($forum_result);
        $this->db->sql_freeresult($forum_result);

        // Prepare post data
        $data = [
            'forum_id'          => $forum_id,
            'topic_id'          => 0, // 0 = new topic
            'force_approved_state' => true,

            // Topic and message content
            'topic_title'       => $topic_subject,
            'post_text'         => $topic_text,
            'message'           => $topic_text,
            'message_md5'       => md5($topic_text),

            // BBCode and formatting
            'bbcode_uid'        => '',
            'bbcode_bitfield'   => '',
            'enable_bbcode'     => true,
            'enable_smilies'    => true,
            'enable_urls'       => true,
            'enable_sig'        => true,

            // System flags
            'post_checksum'     => '',
            'post_edit_locked'  => 0,
            'post_edit_reason'  => '',
            'post_time'         => time(),

            // Posting type
            'topic_type'        => POST_NORMAL,
            'post_attachment'   => 0,
            'icon_id'           => 0,
            'topic_time_limit'  => 0,

            // User and notifications
            'poster_id'         => $this->user->data['user_id'],
            'notify_set'        => 0,
            'notify'            => 0,

            // Search indexing
            'enable_indexing'   => true,

            // Forum context (important for notifications)
            'forum_name'        => $forum_data['forum_name'],
            'forum_desc'        => $forum_data['forum_desc'],
            'forum_type'        => $forum_data['forum_type'],
        ];

        // Build message parsing
        generate_text_for_storage(
            $data['post_text'],
            $data['bbcode_uid'],
            $data['bbcode_bitfield'],
            $data['enable_bbcode'],
            $data['enable_urls'],
            $data['enable_smilies']
        );

        // Submit post
        $poll = []; // no poll
        $result = submit_post('post', $data['topic_title'], $this->user->data['username'], POST_NORMAL, $poll, $data);

        return $result;
    }
}