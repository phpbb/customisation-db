<?php

abstract class cdb_test_case extends phpbb_test_case
{
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
	}
}
