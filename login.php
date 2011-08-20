<?php
include "functions/common.php";

$requestMethod = $_SERVER['REQUEST_METHOD'];
if ($requestMethod == "GET") {
	echo renderView("views/loginView.php");
} else if ($requestMethod == "POST") {
	$action = filterPOST("action");
	
	if ($action == "login") {
		$result = authenticateUser($_POST['username'], $_POST['password']);
		if (false !== $result) {
			$id = intval($result);
			session_start();
			_setLoggedInUser($id);
			global $_SETTINGS;
			redirect($_SETTINGS['User Page']);
		} else {
			sleep(3);
			echo renderView("views/loginView.php", array('loginFailed' => true));
		}
	} else if ($action == "logout") {
		session_start();
		_logoutUser();
		echo renderView("views/loginView.php", array('logout' => true));
	}
} 