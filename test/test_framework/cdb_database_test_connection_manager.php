<?php

class cdb_database_test_connection_manager extends phpbb_database_test_connection_manager
{
	public function __construct($config)
	{
		parent::__construct($config);
	}

	public function load_schema()
	{
	   parent::load_schema();

	   // We'll add our stuff here later
	}
}
