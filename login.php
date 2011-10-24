<?php
include "functions/common.php";
initialize();

$requestMethod = $_SERVER['REQUEST_METHOD'];
if ($requestMethod == "GET") {
	echo renderView('user-login');
} else if ($requestMethod == "POST") {
	$action = getPost("action");
	if ($action == "login") {
		$result = authenticateUser($_POST['username'], $_POST['password']);
		if (false !== $result) {
			include "functions/user.php";
			$id = $result;
			$user = getUserData($id, array('role', 'name'));
			setLoggedInUser($id, $user['role'], $user['name']);
			if ($user['role'] == EXAMINEE_ROLE) {
				redirect(getSettings('User Page'));
			} elseif ($user['role'] == EXAMINER_ROLE || $user['role'] == ADMINISTRATOR_ROLE) {
				redirect(getSettings('Admin Page'));
			}
		} else {
			sleep(3);
			echo renderView('user-login', array('loginFailed' => true));
		}
	} else if ($action == "logout") {
		logoutUser();
		echo renderView('user-login', array('logout' => true));
	}
}