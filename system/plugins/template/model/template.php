<?php

class Template_Model extends FN_Model {
	protected static $_instance = NULL;

	public static function Instance($preload = NULL) {
		if (self::$_instance === NULL) {
			self::$_instance = new self();
			if ($preload !== NULL && $preload > 0) {
				self::$_instance->load($preload);
			}
		}
		
		return self::$_instance;
	}

	public function __construct() {
		parent::__construct();
		$this->_prefix = "fn_";

		$this->has_one('user');
		$this->has_many('revision');
	}
}

?>
