<?php
namespace phpbb\oberon\migrations;

class oberon_migration_400 extends \phpbb\db\migration\migration
{
    const DEFAULT_ON = true;
    const PER_PAGE = 10;

    const PRIVATE_VALIDATION_FORUM = 2;
    const PUBLIC_RELEASE_FORUM = 3;

    /**
     * So we know if it's installed
     */
	public function effectively_installed()
	{
		return false;
        // TODO: uncomment this when no longer in dev
        // return isset($this->config['custdb_enabled']);
	}

    /**
     * First migration file, no dependencies except phpBB4
     */
    static public function depends_on()
    {
        return [
            '\phpbb\db\migration\data\v400\dev',
        ];
    }

    /**
     * Update config table and add to ACP
     */
    public function update_data()
	{                
		return [            
            // Config settings
			['config.add', ['custdb_enabled', self::DEFAULT_ON]],
            ['config.add', ['custdb_per_page', self::PER_PAGE]],
            ['config.add', ['custdb_private_validation_forum_id', self::PRIVATE_VALIDATION_FORUM]],
            ['config.add', ['custdb_public_release_forum_id', self::PUBLIC_RELEASE_FORUM]],
        ];
	}

    /**
     * Create Customisation DB tables
     */
    public function update_schema()
    {
        return [
            'add_tables' => [
                // Contributions
				$this->table_prefix . 'custdb_contributions' => [
					'COLUMNS' => [
						'contribution_id'                       => ['UINT', null, 'auto_increment'],
                        'contribution_status'                   => ['TINT', 0],
						'contribution_name'                     => ['VCHAR_UNI:255', ''],
                        'contribution_description'              => ['MTEXT_UNI', ''],
                        'contribution_type'                     => ['TINT', 0],
                        'contribution_demo_link'                => ['VCHAR_UNI:255', ''],
                        'contribution_validation_topic_id'      => ['UINT', 0],
                        'contribution_release_topic_id'         => ['UINT', 0],// TODO: This may need to be in the revisions table, if we have different release topics for major changes?
                        'contribution_authors'                  => ['VCHAR_UNI:255', ''],
                        'user_id'                               => ['UINT', 0],
                        'submission_time'                       => ['TIMESTAMP', null],
                    ],

					'PRIMARY_KEY' => 'contribution_id',
				],

                // Revisions
                $this->table_prefix . 'custdb_revisions' => [
					'COLUMNS' => [
						'revision_id'                           => ['UINT', null, 'auto_increment'],
                        'contribution_id'                       => ['UINT', 0],
                        'revision_status'                       => ['TINT', 0],
						'revision_name'                         => ['VCHAR_UNI:255', ''],
                        'revision_version'                      => ['VCHAR_UNI:255', ''],
                        'revision_phpbb_version'                => ['VCHAR_UNI:255', ''],
                        'revision_description'                  => ['MTEXT_UNI', ''],
                        'revision_attachment'                   => ['VCHAR_UNI:255', ''],
                        'revision_screenshots'                  => ['VCHAR_UNI:255', ''],
                        'user_id'                               => ['UINT', 0],
                        'submission_time'                       => ['TIMESTAMP', null],
                    ],

					'PRIMARY_KEY' => 'revision_id',
				],

                // Queue
                $this->table_prefix . 'custdb_queue' => [
					'COLUMNS' => [
						'queue_id'                              => ['UINT', null, 'auto_increment'],
						'revision_id'                           => ['UINT', 0],
                        'queue_added_time'                      => ['UINT', 0],
                        'queue_status'                          => ['TINT', 0],
                        'queue_codespace_url'                   => ['VCHAR_UNI:255', ''],
                    ],

					'PRIMARY_KEY' => 'queue_id',
				],
			],
        ];
    }

    /**
     * Remove Customisation DB tables
     */
    public function revert_schema()
    {
        return [
			'drop_tables' => [
				$this->table_prefix . 'custdb_contributions',
                $this->table_prefix . 'custdb_revisions',
                $this->table_prefix . 'custdb_queue',
            ],
        ];
    }

    /** 
     * Remove config settings
     */
    public function revert_data()
    {
        return [
            ['config.remove', ['custdb_enabled']],
            ['config.remove', ['custdb_per_page']],
            ['config.remove', ['custdb_private_validation_forum_id']],
            ['config.remove', ['custdb_public_release_forum_id']],
        ];
    }
}