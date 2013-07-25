<?php

class Auth_Helper extends FN_Helper {
	public function __construct() {
		parent::__construct();
	}
	
	public function logged_in() {
		return isset($_SESSION['login']);
	}
	public function current_id() {
		if ($this->logged_in() ) {
			return $_SESSION['login']['user_id'];
		}
		return 0;
	}
	public function id() {
		return $this->current_id();
	}
	public function current_privilege() {
		if ($this->logged_in() ) {
			return $_SESSION['login']['user_privilege'];
		}
		return 0;
	}
	public function current_name() {
		if ($this->logged_in() ) {
			return "{$_SESSION['login']['user_first_name']} {$_SESSION['login']['user_last_name']}";
		}
		return '';
	}
	
	public function login($user_email, $user_password) {
		$this->load_helper('Db');
		
		$user_password = sha1(trim($user_password) );
		
		$where = array(
			'user_email' => $user_email,
			'user_password' => $user_password
		);
		
		if ($this->Db->get('fn_user', $where) ) {
			$_SESSION['login'] = $this->Db->get_row(true);
			return true;
		}
		
		return false;
	}

	public function logout() {
		unset($_SESSION['login']);
		return true;
	}
}

?>
