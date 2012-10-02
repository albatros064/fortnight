<?php

class Db_Helper extends FN_Helper {

	static private $_instance = null;
	
	private $connection = null;
	private $result = null;
	private $describe = array();

	public static function &Instance() {
		if (is_null(self::$_instance) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		parent::__construct();

		if (is_null($this->connection) ) {
			$this->connection = new mysqli(
				$this->config['global']['db']['host'],
				$this->config['global']['db']['user'],
				$this->config['global']['db']['password'],
				$this->config['global']['db']['database']
			);
			if ($this->connection->connect_errno) {
				throw new Exception("Could not connect to MySQL: ({$this->connection->connect_errno}) {$this->connection->connect_error}");
			}
		}
		else {
			throw new Exception("Db_Helper is a singleton. Use ::Instance().");
		}
	}

	/**
	 * Fetch the next result from the last query.
	 *
	 * @param bool $fetch_array (whether to fetch as an associative array or as a mysqli_result object)
	 * @param mixed (array, mysqli_result Object, FALSE if no more results or no query.)
	 */
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
			$this->result = null;
			return FALSE;
		}
		return $return;
	}

	/**
	 * Perform the specified database query.
	 *
	 * @param string $query_string (The query to execute.)
	 * @return mixed (int number of rows returned, or bool if no rows can be returned from the query)
	 */
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
		$this->assert_table($table_name);

		$where = $this->_generate_where($table_name, $where);
		$query = "SELECT * FROM `{$table_name}` WHERE {$where}";

		return $this->query($query);
	}

	public function insert($table_name, $data) {
		$this->assert_table($table_name);

		$set = $this->_generate_set($table_name, $data);
		$query = "INSERT INTO `{$table_name}` SET {$set}";

		if ($this->query($query) ) {
			return $this->connection->insert_id;
		}

		return FALSE;
	}

	public function update($table_name, $data, $where) {
		$this->assert_table($table_name);

		$set = $this->_generate_set($table_name, $data);
		$where = $this->_generate_where($table_name, $where);
		$query = "UPDATE `{$table_name}` SET {$set} WHERE {$where}";

		if ($this->query($query) ) {
			$new_id = $this->connection->insert_id;
			if ($new_id) {
				return $new_id;
			}
			return TRUE;
		}

		return FALSE;
	}


	/**
	 * Begin a database transaction
	 * @return: bool
	 */
	public function begin() {
		return $this->connection->query("START TRANSACTION;");
	}
	/**
	 * End the current database transaction and commit changes
	 * @return: bool
	 */
	public function commit() {
		return $this->connection->query("COMMIT;");
	}
	/**
	 * End the current database transaction and do not save changes
	 * @return: bool
	 */
	public function rollback() {
		return $this->connection->query("ROLLBACK;");
	}

	/**
	 * Get the list of columns in the requested table, as well as the columns' attributes.
	 *  First checks the description cache. If not found there, loads the description from the database.
	 *
	 * @param string $table_name
	 * @return mixed (array if table exists, false otherwise)
	 */
	public function table_columns($table_name) {
		if (isset($this->describe[$table_name]) ) {
			return $this->describe[$table_name]['fields'];
		}

		if ($this->table_exists($table_name) ) {
			$rows = $this->query("DESCRIBE `{$table_name}`;");
			$describe = array();
			$primary_id = "{$table_name}_id";

			while ( ($field = $this->get_row(true) ) ) {
				$describe[$field['Field'] ] = $field;
				if ($field['Key'] == "PRI") {
					$primary_id = $field['Field'];
				}
			}

			$describe = array(
				'primary_id' => $primary_id,
				'fields' => $describe
			);

			$this->describe[$table_name] = $describe;

			return $describe['fields'];
		}

		return FALSE; // TODO: Throw excetpion instead?
	}

	/**
	 * Get the description data for a particular field in a table.
	 *
	 * @param string $table_name
	 * @param string $column_name
	 * @return mixed (array containing description data for the column, or FALSE if table or column are not present.
	 */
	public function column_type($table_name, $column_name) {
		$columns = $this->table_columns($table_name);
		if ($columns) {
			if (isset($columns[$column_name]) ) {
				return $columns[$column_name]['Type'];
			}
		}

		return FALSE;
	}

	/**
	 * Get the name of the field that holds the table's primary ID.
	 *
	 * @param string $table_name
	 * @return string
	 */
	public function primary_id($table_name) {
		$this->table_columns($table_name);

		return $this->describe[$table_name]['primary_id'];
	}

	/**
	 * Checks that the given table exists in the current database.
	 *
	 * @param string $table_name
	 * @return bool
	 */
	public function table_exists($table_name) {
		if (isset($this->describe[$table_name]) ) {
			return TRUE;
		}
		return ($this->query("SHOW TABLES WHERE `Tables_in_{$this->config['global']['db']['database']}` = '{$table_name}';") === 1);
	}
	protected function assert_table($table_name) {
		if (!$this->table_exists($table_name) ) {
			throw new Exception("Table does not exist.");
		}
	}

	public function escape($to_escape) {
		return $this->connection->real_escape_string($to_escape);
	}

	/**
	 * Generate the WHERE portion for internal queries.
	 *
	 * @param string $table_name
	 * @param array $where_array
	 * @param array $table_columns
	 * @return string (The generated WHERE expression.)
	 */
	protected function _generate_where($table_name, $where_array) {
		$where_string = 'TRUE';

		$table_columns = $this->table_columns($table_name);

		$column_operators = array('=', '!', '<', '<=', '>', '>=', '#', '#<', '#>');#, '!#', '!#<', '!#>');

		foreach ($where_array as $column => $value) {

			$column_split = $this->_where_column_split($column, $column_operators);

			$column   = $column_split['column'  ];
			$operator = $column_split['operator'];

			if (isset($table_columns[$column]) ) {
				$where_string .= " AND `{$table_name}`.`{$column}` ";

				$value_is_null = false;

				if ($value === NULL) {
					$value_is_null = true;
				}

				$value = $this->escape($value);

				if ($operator == '=') {
					if ($value_is_null) {
						$where_string .= "IS NULL";
					}
					else {
						$where_string .= "= '{$value}'";
					}
				}
				else if ($operator == '!') {
					if ($value_is_null) {
						$where_string .= "IS NOT NULL";
					}
					else {
						$where_string .= "!= '{$value}'";
					}
				}
				else if ($operator == '#') {
					$where_string .= "LIKE '%{$value}%'";
				}
				else if ($operator == '#<') {
					$where_string .= "LIKE '{$value}%'";
				}
				else if ($operator == '#>') {
					$where_string .= "LIKE '%{$value}'";
				}
				else {
					$where_string .= "{$operator} '{$value}'";
				}
			}
		}

		return $where_string;
	}
	/**
	 * Internal helper for _generate_where()
	 *
	 * @param string $column_string
	 * @param array $column_operators
	 * @return array
	 */
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

	protected function _generate_set($table_name, $data) {
		$columns = $this->table_columns($table_name);

		$set = " ";

		foreach ($data as $field => $value) {
			if (isset($columns[$field]) && $columns[$field]['Key'] !== "PRI") {
				if ($value === NULL) {
					$set .= "`{$field}` = NULL, ";
				}
				else {
					$set .= "`{$field}` = '{$this->escape($value)}', ";
				}
			}
		}

		return trim(", ", $set);
	}
}

?>
