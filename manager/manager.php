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

    // Revision status mapping
    const REVISION_STATUS_MAP = [
        self::INTERNAL_STATUS_UNVALIDATED => self::STATUS_UNVALIDATED,
        self::INTERNAL_STATUS_APPROVED    => self::STATUS_APPROVED,
        self::INTERNAL_STATUS_DENIED      => self::STATUS_DENIED,
    ];

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

    /* @var array $tables */
    protected $settings;

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
	public function __construct(string $root_path, string $php_ext, \phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\user $user, \phpbb\user_loader $user_loader, \phpbb\language\language $language, \phpbb\auth\auth $auth, \phpbb\extension\manager $ext_manager, array $tables, array $settings)
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
        $this->settings = $settings;

        // Ensure the language file is loaded
        $this->language->add_lang('common', 'phpbb/oberon');
	}

    // See if the user is a team member
    public function is_team_member()
    {
        $is_team_member = false;

        // Is the current user a member of a team group based on configuration?
        if ($this->is_registered())
        {
            // Get team member group IDs from settings
            $team_group_ids = $this->get_settings()['team.member.group.ids'] ?? [];

            if (!function_exists('group_memberships'))
            {
                include_once($this->root_path . 'includes/functions_user.' . $this->php_ext);
            }

            // Get group memberships for the current user
            $group_ids = array_column(group_memberships(false, $this->user->data['user_id']), 'group_id');

            // Are there any groups the user is in that match the team member groups that we have specified in the settings?
            $matches = array_intersect($group_ids, $team_group_ids);

            if (!empty($matches)) 
            {
                $is_team_member = true;
            }
        }

        return $is_team_member;
    }

    public function is_registered()
    {
        return !$this->is_guest();
    }

    public function is_guest()
    {
        return $this->user->data['user_id'] == ANONYMOUS;
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

    /**
     * Return settings for Oberon
     */
    public function get_settings()
    {
        return $this->settings;
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

    // Simple mapping for the language strings for each contribution type
    public function contribution_type_mapping()
    {
        return [
            self::TYPE_EXTENSIONS => $this->user->lang('CUSTDB_TYPE_EXTENSIONS'),
            self::TYPE_STYLES => $this->user->lang('CUSTDB_TYPE_STYLES'),
            self::TYPE_TRANSLATIONS => $this->user->lang('CUSTDB_TYPE_TRANSLATIONS'),
            self::TYPE_BBCODES => $this->user->lang('CUSTDB_TYPE_BBCODES'),
            self::TYPE_TOOLS => $this->user->lang('CUSTDB_TYPE_TOOLS'),
            self::TYPE_ARCHIVE => $this->user->lang('CUSTDB_TYPE_ARCHIVE'),
        ];
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
        // TODO: SQL for updating the internal status to go here. If we need it at all?
        //$revision_status_mapping = array_flip(self::REVISION_STATUS_MAP);
        //$internal_status = $revision_status_mapping[$contribution_status] ?? null;
    }

    /**
     * An internal status (queue status and potentially revision status) change
     * INTERNAL STATUS CHANGE
     */
    public function update_internal_queue_status(int $queue_id, int $queue_status)
    {
        $queue_item = $this->find_queue_item($queue_id);

        $sql = 'UPDATE ' . $this->tables['queue'] . ' SET queue_status = ' . (int) $queue_status . ' WHERE queue_id = ' . (int) $queue_id;
		$this->db->sql_query($sql);

        if (in_array($queue_status, [self::INTERNAL_STATUS_UNVALIDATED, self::INTERNAL_STATUS_APPROVED, self::INTERNAL_STATUS_DENIED]))
        {
            // For these major status updates, we also update the revision status. Because if/when the queue entry is removed,
            // we still want to know what happened to the revision. This is one of two places the revision status is changed.
            $sql = 'UPDATE ' . $this->tables['revisions'] . '
                    SET revision_status = ' . self::REVISION_STATUS_MAP[$queue_status] . '
                    WHERE revision_id = ' . (int) $queue_item['revision_id'];

            $this->db->sql_query($sql);
        }

        // Only if the private validation topic already exists, put a post in the private validation topic about the internal status change
        $contribution_data = $this->find_contribution_revision_for_queue_id($queue_id);

        if ($contribution_data && (int) $contribution_data['contribution']['contribution_validation_topic_id'] > 0)
        {
            $old_status_name = $this->get_internal_status((int) $queue_item['queue_status']);
            $new_status_name = $this->get_internal_status($queue_status);

            if ($old_status_name != $new_status_name)
            {
                // Create status change message
                $status_change_message = $this->language->lang(
                    'CUSTDB_STATUS_AUTOMATIC_CHANGE',
                    $old_status_name,
                    $new_status_name
                );

                $this->create_or_append_forum_comment(
                    $contribution_data['contribution']['contribution_id'], 
                    $this->get_settings()['private.contribution.validation.forum.id']['default'], 
                    $contribution_data['contribution']['contribution_validation_topic_id'], 
                    $contribution_data['contribution']['contribution_name'], // subject
                    $status_change_message // post text
                );
            }
        }
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
    public function get_contributions_for_index(int $type = 0, int $status = 0, int $sort = 0, string $search_query = '', int $start = 0, int $per_page = 50)
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

        // Get total count for pagination (TODO: check this... AI generated)
        $count_sql = 'SELECT COUNT(*) as total ' . substr($sql, strpos($sql, 'FROM'));
        $count_result = $this->db->sql_query($count_sql);
        $count_row = $this->db->sql_fetchrow($count_result);
        $this->db->sql_freeresult($count_result);
        $total = (int) $count_row['total'];

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

        $result = $this->db->sql_query_limit($sql, $per_page, $start);
        $contributions = [];

        while ($row = $this->db->sql_fetchrow($result))
        {
            // Get the status of the *latest* revision so we can colour code it on the page for team members to see
            // TODO: this is inefficient because it's getting the contribution for a second time in the function call below
            $latest_revision = $this->get_contribution_with_revision($row['contribution_id'], true);

            $contributions[] = [
                'contribution_id'           => $row['contribution_id'],
                'contribution_name'         => $row['contribution_name'],
                'contribution_description'  => $row['contribution_description'],
                'contribution_status'       => (int) $row['contribution_status'],
                'contribution_type'         => (int) $row['contribution_type'],

                'revision_status'           => (int) $latest_revision['revision_status'],
            ];
        }

        $this->db->sql_freeresult($result);

        return [
            'total' => $total,
            'contributions' => $contributions,
        ];   
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
            $sql = 'SELECT r.*, q.queue_status, q.queue_id
                    FROM ' . $this->tables['revisions'] . ' r
                    LEFT JOIN ' . $this->tables['queue'] . ' q
                        ON r.revision_id = q.revision_id
                    WHERE r.contribution_id = ' . $contribution_id . '
                    ORDER BY r.submission_time DESC';
        }

        else 
        {
            // Get specific revision
            $sql = 'SELECT r.*, q.queue_status, q.queue_id
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

        return [
            'contribution_id'                   => $contribution['contribution_id'],
            'contribution_name'                 => $contribution['contribution_name'],
            'contribution_description'          => $contribution['contribution_description'],
            'contribution_demo_link'            => $contribution['contribution_demo_link'],
            'contribution_type'                 => $contribution['contribution_type'],
            'contribution_status'               => $contribution['contribution_status'],
            'contribution_validation_topic_id'  => $contribution['contribution_validation_topic_id'],
            'contribution_release_topic_id'     => $contribution['contribution_release_topic_id'],
            'external_status_label'             => $this->get_external_status($contribution['contribution_status']),
            'internal_status_label'             => $this->is_team_member() ? $this->get_internal_status($revision['queue_status']) : '',
            'author_name'                       => $contribution['author_name'],

            // Revision info
            'revision_id'                       => $revision['revision_id'] ?? null,
            'revision_status'                   => $revision['revision_status'],
            'revision_status_label'             => $this->get_external_status($revision['revision_status']),
            'revision_name'                     => $revision['revision_name'] ?? '',
            'revision_version'                  => $revision['revision_version'] ?? '',
            'revision_phpbb_version'            => $revision['revision_phpbb_version'] ?? '',
            'revision_description'              => $revision['revision_description'] ?? '',
            'revision_attachment'               => $revision['revision_attachment'] ?? '',
            'submission_time'                   => $revision['submission_time'] ?? null,
            'screenshots'                       => $screenshots,

            // Queue info
            'queue_id'                          => $revision['queue_id'] ?? null,
            'queue_status'                      => $revision['queue_status'] ?? null,
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

        $sql = 'SELECT r.*, q.queue_status, q.queue_id
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
                'revision_id'           => $row['revision_id'],
                'revision_status'       => (int) $row['revision_status'],
                'revision_name'         => $row['revision_name'],
                'revision_version'      => $row['revision_version'],
                'revision_description'  => $row['revision_description'],
                'submission_time'       => $row['submission_time'],

                'queue_id'              => $row['queue_id'],
                'queue_status'          => $row['queue_status'],
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

    public function find_queue_item(int $queue_id)
    {
        $sql = 'SELECT * FROM ' . $this->tables['queue'] . ' WHERE queue_id = ' . (int) $queue_id;
        $result = $this->db->sql_query_limit($sql, 1);
        $queue_item = $this->db->sql_fetchrow($result);
        return $queue_item;
    }

    // Get the items awaiting processing
    public function find_queue_items_for_processing()
    {
        $sql = 'SELECT * FROM ' . $this->tables['queue'] . '
                WHERE queue_status NOT IN (' . self::INTERNAL_STATUS_DENIED . ', ' . self::INTERNAL_STATUS_APPROVED . ')
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

        $customisation_robot_user_id = $this->get_settings()['customisation.robot.user.id'];

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
            $data['message'],
            $data['bbcode_uid'],
            $data['bbcode_bitfield'],
            $data['enable_bbcode'],
            $data['enable_urls'],
            $data['enable_smilies']
        );

        // Submit post
        $poll = []; // no poll
        $result = submit_post('post', $data['topic_title'], $this->user->data['username'], POST_NORMAL, $poll, $data);

        // submit_post modifies $data by reference and will set topic_id
        return $data['topic_id'] ?? 0;
    }

    /**
     * [AI GENERATED]
     * Create a new post (reply) in an existing topic.
     */
    public function new_post(int $topic_id, string $post_text)
    {
        include_once($this->root_path . 'includes/functions_posting.' . $this->php_ext);

        $customisation_robot_user_id = $this->get_settings()['customisation.robot.user.id'];

        $this->user_loader->load_users([$customisation_robot_user_id]);
        $this->user->data = $this->user_loader->get_user($customisation_robot_user_id);
        $this->user->data['is_registered'] = true;
        $this->user->data['is_anonymous']  = false;
        $this->user->data['is_bot']        = false;

        // Prepare post data
        $data = [
            'forum_id'          => 0, // will be filled in by submit_post based on topic_id
            'topic_id'          => $topic_id,
            'force_approved_state' => true,

            // Topic and message content
            'topic_title'       => 'Validation Update',
            'post_text'         => $post_text,
            'message'           => $post_text,
            'message_md5'       => md5($post_text),

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
        ];

        // Build message parsing
        generate_text_for_storage(
            $data['message'],
            $data['bbcode_uid'],
            $data['bbcode_bitfield'],
            $data['enable_bbcode'],
            $data['enable_urls'],
            $data['enable_smilies']
        );

        // Submit reply post
        $poll = []; // no poll
        submit_post('reply', $data['topic_title'], $this->user->data['username'], POST_NORMAL, $poll, $data);

        return $data['topic_id'] ?? $topic_id;
    }

    /**
     * [AI GENERATED]
     * Store a validation report into the forum.
     *
     * If a validation topic already exists for the contribution, append as a new post.
     * Otherwise create a new topic and store its ID on the contribution.
     */
    public function store_validation_report(int $contribution_id, string $report, string $outcome, int $confidence): int
    {
        $contribution_id = (int) $contribution_id;

        // Load contribution row
        $sql = 'SELECT * FROM ' . $this->tables['contributions'] . ' WHERE contribution_id = ' . $contribution_id;
        $result = $this->db->sql_query_limit($sql, 1);
        $contribution = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if ($contribution && in_array((int) $contribution['contribution_type'], [self::TYPE_EXTENSIONS, self::TYPE_STYLES, self::TYPE_TRANSLATIONS]))
        {
            $topic_id = (int) ($contribution['contribution_validation_topic_id'] ?? 0);

            // Use the contribution name as the topic title
            $subject = $contribution['contribution_name'];

            // TODO: language entries here?
            $body = "Validation outcome: {$outcome}\n" .
                    "Confidence: {$confidence}/100\n\n" .
                    "Report:\n{$report}";

            // Ensure topic exists and store it in the private validation forum
            $topic_id = $this->create_or_append_forum_comment($contribution_id, $this->get_settings()['private.contribution.validation.forum.id']['default'], $topic_id, $subject, $body);

            if (isset($contribution['contribution_validation_topic_id']) && $contribution['contribution_validation_topic_id'] == 0 && $topic_id > 0)
            {
                $this->update_contribution_validation_topic_id($contribution_id, $topic_id);
            }

            return $topic_id;
        }
    }

    /**
     * If there's a topic, put it in that topic. If there's not, create a topic.
     */
    public function create_or_append_forum_comment($contribution_id, $forum_id, $topic_id, $subject = '', $post_body = ''): int
    {
        // Ensure topic exists and store it
        if ($topic_id <= 0)
        {
            $topic_id = $this->new_topic($forum_id, $subject, $post_body);
        }

        else
        {
            // Otherwise, add a new post to the existing topic.
            $this->new_post($topic_id, $post_body);
        }

        return $topic_id;
    }

    /**
     * Simple update to the validation topic id
     */
    public function update_contribution_validation_topic_id(int $contribution_id, int $topic_id)
    {
        $sql = 'UPDATE ' . $this->tables['contributions'] . ' SET contribution_validation_topic_id = ' . (int) $topic_id . ' WHERE contribution_id = ' . $contribution_id;
        $this->db->sql_query($sql);
    }

    /**
     * Simple update to the release topic id
     */
    public function update_contribution_release_topic_id(int $contribution_id, int $topic_id)
    {
        $sql = 'UPDATE ' . $this->tables['contributions'] . ' SET contribution_release_topic_id = ' . (int) $topic_id . ' WHERE contribution_id = ' . $contribution_id;
        $this->db->sql_query($sql);
    }

    /**
     * Remove queue entry. But we only do this if the internal status is approved or denied, otherwise there might
     * still be future processing to be done.
     */
    public function remove_queue_entries(int $queue_id = 0)
    {
        $sql = 'DELETE FROM ' . $this->tables['queue'] . '
                WHERE ' . $this->db->sql_in_set('queue_status', [
                    self::INTERNAL_STATUS_APPROVED,
                    self::INTERNAL_STATUS_DENIED,
                ], false); // true allows it to be an IN clause

        if ($queue_id > 0)
        {
            // Remove a specific queue item
            $sql .= ' AND queue_id = ' . (int) $queue_id;   
        }

        $this->db->sql_query($sql);
    }

    /**
     * Look up a user by username
     *
     * @param string $username The username to search for
     * @return array|null User data or null if not found
     */
    public function get_user_by_username(string $username)
    {
        $username = trim($username);
        if (empty($username))
        {
            return null;
        }

        $sql_array = [
            'SELECT' => 'user_id, username',
            'FROM'   => [USERS_TABLE => 'u'],
            'WHERE'  => 'u.username = \'' . $this->db->sql_escape($username) . '\' AND u.user_type <> ' . USER_IGNORE,
        ];

        $sql = $this->db->sql_build_query('SELECT', $sql_array);

        $result = $this->db->sql_query_limit($sql, 1);
        $user_data = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        return $user_data ?: null;
    }

    /**
     * Get revision data by revision_id
     *
     * @param int $revision_id
     * @return array|null Revision data or null if not found
     */
    public function get_revision_data(int $revision_id)
    {
        $sql = 'SELECT r.revision_attachment, r.revision_name, r.contribution_id
                FROM ' . $this->tables['revisions'] . ' r
                WHERE r.revision_id = ' . (int) $revision_id;

        $result = $this->db->sql_query_limit($sql, 1);
        $revision = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        return $revision ?: null;
    }

    public function get_contribution_type(int $contribution_id): int
    {
        $sql = 'SELECT contribution_type
                FROM ' . $this->tables['contributions'] . '
                WHERE contribution_id = ' . (int) $contribution_id;

        $result = $this->db->sql_query_limit($sql, 1);
        $contribution = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        return (int) $contribution['contribution_type'];
    }

    /**
     * Update an existing contribution
     *
     * @param int $contribution_id
     * @param array $contribution_array Data to update
     * @return void
     */
    public function update_contribution(int $contribution_id, array $contribution_array)
    {
        $sql = 'UPDATE ' . $this->tables['contributions'] . ' 
                SET ' . $this->db->sql_build_array('UPDATE', $contribution_array) . ' 
                WHERE contribution_id = ' . (int) $contribution_id;

        $this->db->sql_query($sql);
    }

    /**
     * Update an existing revision
     *
     * @param int $revision_id
     * @param array $revision_array Data to update
     * @return void
     */
    public function update_revision(int $revision_id, array $revision_array)
    {
        $sql = 'UPDATE ' . $this->tables['revisions'] . ' 
                SET ' . $this->db->sql_build_array('UPDATE', $revision_array) . ' 
                WHERE revision_id = ' . (int) $revision_id;

        $this->db->sql_query($sql);
    }
}
