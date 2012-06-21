<?php

class Db_Helper extends FN_Helper {

	static private $_instance = null;
	private $connection = null;
	private $result = null;

	public static function &Instance() {
		if (is_null(self::$_instance) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		parent::__construct();

		$this->config['db'] = $this->_load_config_file('db');
		
		if (is_null($this->connection) ) {
			$this->connection = new mysqli(
				$this->config['db']['host'],
				$this->config['db']['user'],
				$this->config['db']['password'],
				$this->config['db']['database']
			);
			if ($this->connection->connect_errno) {
				throw new Exception("Could not connect to MySQL: ({$this->connection->connect_errno}) {$this->connection->connect_error}");
			}
		}
	}

	public function get_row($fetch_array = TRUE) {
		if (is_null($this->result) ) {
			return FALSE;
		}

		if ($fetch_array) {
			$return = $this->result->fetch_assoc();
		}
		else {
			$return = $this->result->fetch_object();
		}

		if ($return === NULL) {
			return FALSE;
		}
		return $return;
	}

	
	public function query($query_str) {
		$this->result = $this->connection->query($query_str);
		if ($this->result === FALSE) {
			return FALSE;
		}
		if ($this->result === TRUE) {
			return TRUE;
		}

		return $this->result->num_rows;
	}

	/**
	 * 
	 * @param string $table_name
	 * @param array $where
	 * @param bool $return_all (
	 * @return mixed (integer number of rows returned, or FALSE if failed)
	 */
	public function get($table_name, $where, $return_all = FALSE) {
		
	}

	public function insert($table_name, $data) {
	}

	public function update($table_name, $data, $where) {
	}


	/**
	 * Transaction functions
	 * @return: bool
	 */
	public function begin() {
		return self::$_connection->query("START TRANSACTION;");
	}
	public function commit() {
		return self::$_connection->query("COMMIT;");
	}
	public function rollback() {
		return self::$_connection->query("ROLLBACK;");
	}

	protected function _get_table_columns($table_name) {
		
	}

	protected function _generate_where($table_name, $where_array, $table_columns) {
		$where_string = 'TRUE';

		$column_operators = array('=', '!', '<', '<=', '>', '>=', '#', '#<', '#>');#, '!#', '!#<', '!#>');

		foreach ($where_array as $column => $value) {

			$column_split = $this->_where_column_split($column, $column_operators);

			$column   = $column_split['column'  ]; // TODO: db-escape this value
			$operator = $column_split['operator'];

			if (in_array($column, $table_columns) ) {
				$where_string .= " AND `table_name`.`{$column}` ";

				if ($operator == '=') {
					if ($value === NULL) {
						$where_string .= "IS NULL";
					}
					else {
						$where_string .= "= '{$column}'";
					}
				}
				else if ($operator == '!') {
					if ($value === NULL) {
						$where_string .= "IS NOT NULL";
					}
					else {
						$where_string .= "!= '{$column}'";
					}
				}
				else if ($operator == '#') {
					$where_string .= "LIKE '%{$column}%'";
				}
				else if ($operator == '#<') {
					$where_string .= "LIKE '{$column}%'";
				}
				else if ($operator == '#>') {
					$where_string .= "LIKE '%{$column}'";
				}
				else {
					$where_string .= "{$operator} '{$column}'";
				}
			}
		}

		return $where_string;
	}
	protected function _where_column_split($column_string, $column_operators) {

		$last_one = substr($column_string, -1);
		$last_two = substr($column_string, -2);

		$operator = '=';
		$column = $column_string;

		if ($last_two !== FALSE && in_array($last_two, $column_operators) ) {
			$operator = $last_two;
			$column   = trim(substr($column, 0, -2) );
		}
		else if ($last_one !== FALSE && in_array($last_one, $column_operators) ) {
			$operator = $last_one;
			$column   = trim(substr($column, 0, -1) );
		}

		return array('column' => $column, 'operator' => $operator);
	}
}

?>
