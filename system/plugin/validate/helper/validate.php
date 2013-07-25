<?php

class Validate_Helper extends FN_Helper {

	protected $_show_labels = true;
	protected $_show_errors = true;
	
	protected $_data = array();
	protected $_errors = array();
	
	protected $_gerrors = array();
	
	
	public function __construct() {
		parent::__construct();
	}
	
	public function show_labels($show) {
		$this->_show_labels = !!$show;
		return $this;
	}
	public function show_errors($show) {
		$this->_show_erros = !!$show;
		return $this;
	}
	public function add_error($message) {
		$this->_gerrors[] = $message;
	}
	public function global_errors() {
		$return = "";
		if ($this->_gerrors) {
			$return = "<div class=\"global-validate-errors\">";
			foreach ($this->_gerrors as $gerror) {
				$return .= "<p class=\"global-validate-error\">{$gerror}</p>";
			}
			$return .= "</div>";
		}
		
		return $return;
	}
	
	public function run($data, $all_rules = NULL) {
		$this->_data   = $data;
		$this->_errors = array();
		
		if ($all_rules) {
			foreach ($all_rules as $field => $field_rules) {
				$errors = array();
				foreach ($field_rules as $rule => $message) {
					try {
						// Check all "required" rules
						if ($rule === 'reqd') {
							if (!isset($data[$field]) || trim($data[$field]) == '') {
								throw new ValidateFailedException;
							}
							throw new ValidateException;
						}
						// Only check the rest if the field exists
						if (isset($data[$field]) ) {
							if ($rule === 'email') {
								if (!preg_match('/^[A-z0-9._%+-]+@[A-z0-9.-]+\.[A-z]{2,4}$/', trim($data[$field]) ) ) {
									throw new ValidateFailedException;
								}
								throw new ValidateException;
							}
							if (preg_match('/^match\((.*)\)$/', $rule, $matches) ) {
								if (!isset($data[$matches[1] ]) || $data[$field] !== $data[$matches[1] ]) {
									throw new ValidateFailedException;
								}
								throw new ValidateException;
							}
							if (preg_match('/^length\(([1-9]+)(,([1-9]+)?)?\)$/', $rule, $matches) ) {
								$length = strlen($data[$field]);
								if ($length < $matches[1] || (isset($matches[4]) && $length > $matches[4]) ) {
									throw new ValidateFailedException;
								}
								throw new ValidateException;
							}
							if ($rule == 'email-unique') {
								// TODO: remove this from the stock validate class
								$this->load_helper('Db');
								if ($this->Db->get('fn_user', array('user_email' => trim($data[$field]) ) ) ) {
									throw new ValidateFailedException;
								}
								throw new ValidateException;
							}
						}
					}
					catch (ValidateFailedException $e) {
						$errors[] = $message;
					}
					catch (ValidateException $e) {
					}
				}
				
				if ($errors) {
					$this->_errors[$field] = $errors;
				}
			}
			
			return empty($this->_errors);
		}
		return true;
	}
	
	public function print_field($field_type, $field_name, $field_label) {
	
		$error_hilight = '';
		$error_title   = '';
		
		if ($this->_show_errors && isset($this->_errors[$field_name]) ) {
			$error_hilight = 'field-has-error';
			$error_title = implode(' ', $this->_errors[$field_name]);
		}
		
		$return = $this->_field_head($field_name, $field_label, $error_hilight);
		
		switch ($field_type) {
			case 'text':
			case 'password':
				$value = isset($this->_data[$field_name]) && $field_type != 'password' ? $this->_data[$field_name] : '';
				$return .= "<input type=\"{$field_type}\" name=\"{$field_name}\" id=\"{$field_name}\" value=\"{$value}\" title=\"{$error_title}\">";
			default:
				break;
		}
		
		$return .= $this->_field_foot();
		
		return $return;
	}
	
	protected function _field_head($field_name, $field_label, $hilight = '') {
		$return = "<div class=\"field-container {$hilight}\">";
		if ($this->_show_labels) {
			$return .= "<p class=\"field-label\">{$field_label}</p>";
		}
		$return .= '<div class="field-wrapper">';
		
		return $return;
	}
	protected function _field_foot() {
		$return = '</div></div>';
		
		return $return;
	}
}

class ValidateException extends Exception {}
class ValidateFailedException extends ValidateException {}
class ValidatePassedException extends ValidateException {}

?>
