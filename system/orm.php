<?php

abstract class FN_Orm extends FN_Base {

	protected $_table    = NULL;
	protected $_prefix   = NULL;
	protected $_has_one  = array();
	protected $_has_many = array();

	protected $_data     = NULL;


	public function __construct() {
		parent::__construct();
		$this->load_helper("Db");
	}

	public function load($object_id, $_internal_override = NULL) {
		// Handle the internal loading override.
		if ($_internal_override !== NULL) {
			// Verify the calling function (and limit it to a select few.)
			$backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);

			$permitted_callers = array("get_all");

			$caller = $backtrace[1];

			if ($caller['class'] === 'FN_Orm' && in_array($caller['function'], $permitted_callers) ) {
				$this->_data = $_internal_override;
				return $this;
			}
		}

		if (!$object_id) {
			throw new Exception("Object ID expected.");
		}

		$object_id = intval($object_id);

		if ($this->get(array($this->primary_id() => $object_id), true) !== FALSE) {
			return $this;
		}

		throw new Exception("Invalid object ID.");
	}

	public function get($where, $perform_load = FALSE) {
		$count = $this->Db->get($this->table_name(true), $where);
		if ($count === 1) {
			$row = $this->Db->get_row(true);
			if ($perform_load) {
				$this->_data = $row;
				return $this;
			}
			
			return $row;
		}
			
		return FALSE;
	}
	public function get_all($where, $perform_load = FALSE) {
		$count = $this->Db->get($this->table_name(true), $where);
		$return = array();

		if ($count > 0) {
			while ($next = $this->Db->get_row() ) {
				if ($perform_load) {
					$new_item = clone $this;
					$new_item->load(FALSE, $next);
				}
				else {
					$new_item = $next;
				}

				$return[] = $new_item;
			}
		}

		return $return;
	}

	protected function assert_load($throw_exception = TRUE) {
		if ($this->_data === NULL) {
			if ($throw_exception) {
				throw new Exception("ORM not loaded.");
			}
			return FALSE;
		}

		return TRUE;
	}

	public function table_name($include_prefix = FALSE) {
		if ($this->_table) {
			$table_name = $this->_table;
		}
		else {
			$table_name = strtolower(substr(get_class($this), 0, -6) );
		}

		if ($include_prefix && $this->_prefix) {
			$table_name = $this->_prefix . $table_name;
		}

		return $table_name;
	}
	public function primary_id() {
		return $this->Db->primary_id($this->table_name(true) );
	}

	public function __call($call_name, $call_arguments) {
		$this->assert_load(); // TODO: Use this? Or just return NULL if not loaded?

		// Access by full name
		if (isset($this->_data[$call_name]) ) {
			return $this->_data[$call_name];
		}

		$table_name = $this->table_name();

		// Access by partial name (extended by the table name)
		$property_name = "{$table_name}_{$call_name}";
		if (isset($this->_data[$property_name]) ) {
			return $this->_data[$property_name];
		}

		// Has one


		if (isset($this->_has_one[$call_name]) ) {
			$other_object = $this->load_model($call_name);
			#$other_field  = "{$call_name}_id";
			$other_field = $other_object->primary_id();

			// Is the link in our table, or is it in the other table?
			if (isset($this->_data[$other_field]) ) {
				return $other_object->load($this->$other_field() );
			}
			else {
				return $other_object->get(array($this->primary_id() => $this->id() ) );
			}
		}

		// Has many
		if (isset($this->_has_many[$call_name]) ) {
			$mapping_table = $this->_has_many[$call_name];

			$other_object = $this->load_model($call_name);

			return $other_object->get_all(array($this->primary_id() => $this->id() ), TRUE);
			
			if ($mapping_table) {
			}
		}

		throw new Exception("Invalid property.");
		return NULL;
	}

	protected function has_one($link_name, $mapping_table = FALSE) {
		if (!isset($this->_has_one[$link_name]) ) {
			$this->_has_one[$link_name] = $mapping_table;
			return TRUE;
		}
		return FALSE;
	}
	protected function has_many($link_name, $mapping_table = FALSE) {
		if (!isset($this->_has_many[$link_name]) ) {
			$this->_has_many[$link_name] = $mapping_table;
		}
		return FALSE;
	}
}

?>
