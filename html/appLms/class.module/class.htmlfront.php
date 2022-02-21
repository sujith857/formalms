<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class Module_Htmlfront extends LmsModule {
	
	function loadBody() {
		
		require_once($GLOBALS['where_lms'].'/modules/htmlfront/htmlfront.php');
		htmlfrontDispatch($GLOBALS['op']);
	}
	
	function getAllToken() {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'],
			'mod' => ['code' => 'mod',
								'name' => '_MOD',
								'image' => 'standard/edit.png']
        ];
	}

	function getPermissionsForMenu($op) {
		return [
			1 => $this->selectPerm($op, 'view'),
			2 => $this->selectPerm($op, 'view'),
			3 => $this->selectPerm($op, 'view'),
			4 => $this->selectPerm($op, 'view'),
			5 => $this->selectPerm($op, 'view,mod'),
			6 => $this->selectPerm($op, 'view,mod'),
			7 => $this->selectPerm($op, 'view,mod')
        ];
	}
	
}

?>