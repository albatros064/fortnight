<?php

class Db_Helper extends FN_Helper {

	static private $_connection = null;
	static private $_config = null;

	public function __construct() {
		parent::__construct();

		if (is_null(self::$_config) ) {
			self::$_config = $this->_load_config_file('db');
		}

		$this->config['db'] = self::$_config;

		
		if (is_null(self::$_connection) ) {
			$conn = new mysqli(
				$this->config['db']['host'],
				$this->config['db']['user'],
				$this->config['db']['password'],
				$this->config['db']['database']
			);
			if ($conn->connect_errno) {
				throw new Exception("Could not connect to MySQL: ({$conn->connect_errno}) {$conn->connect_error}");
			}

			self::$_connection = $conn;
		}
	}

}

?>
