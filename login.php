<?php
include "functions/common.php";
initialize();

$requestMethod = $_SERVER['REQUEST_METHOD'];
if ($requestMethod == "GET") {
	echo renderView('user-login');
} else if ($requestMethod == "POST") {
	$action = filterPOST("action");
	if ($action == "login") {
		$result = authenticateUser($_POST['username'], $_POST['password']);
		if (false !== $result) {
			$id = intval($result);
			setLoggedInUser($id);
			redirect(getSettings('User Page'));
		} else {
			sleep(3);
			echo renderView('user-login', array('loginFailed' => true));
		}
	} else if ($action == "logout") {
		logoutUser();
		echo renderView('user-login', array('logout' => true));
	}
} 