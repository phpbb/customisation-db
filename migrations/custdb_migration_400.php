<?php
namespace phpbb\oberon\migrations;

class custdb_migration_400 extends \phpbb\db\migration\migration
{
    const DEFAULT_ON = true;
    const PER_PAGE = 10;

    /**
     * So we know if it's installed
     */
	public function effectively_installed()
	{
		return isset($this->config['custdb_enabled']);
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
            ['config.add', ['custdb_per_page', self::PER_PAGE]]
        ];
	}

    /**
     * Create Customisation DB tables
     */
    public function update_schema()
    {
        return [
            'add_tables' => [
				$this->table_prefix . 'custdb_contributions' => [
					'COLUMNS' => [
						'contribution_id'                       => ['UINT', null, 'auto_increment'],
						'contribution_name'                     => ['VCHAR_UNI:255', ''],
                        'contribution_description'              => ['VCHAR_UNI:255', ''],
                        'contribution_type'                     => ['TINT', 0],
                        'user_id'                               => ['UINT', 0],
                        'submission_time'                       => ['TIMESTAMP', null],
                    ],

					'PRIMARY_KEY' => 'contribution_id',
				],

                $this->table_prefix . 'custdb_revisions' => [
					'COLUMNS' => [
						'revision_id'                           => ['UINT', null, 'auto_increment'],
                        'contribution_id'                       => ['UINT', 0],
						'revision_name'                         => ['VCHAR_UNI:255', ''],
                        'revision_description'                  => ['VCHAR_UNI:255', ''],
                        'user_id'                               => ['UINT', 0],
                        'submission_time'                       => ['TIMESTAMP', null],
                    ],

					'PRIMARY_KEY' => 'revision_id',
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
            ],
        ];
    }
}