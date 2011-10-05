<?php
include "functions/common.php";

_allowOnlyIfNotYetInstalled();

$requestMethod = $_SERVER['REQUEST_METHOD'];
if ($requestMethod == "GET") {

	$mainPanel = renderView('admin-install');
	$args = array('mainPanel' => $mainPanel);
	echo renderView('admin-main', $args);
} elseif ($requestMethod == 'POST' && 'install' == getPost('action')) {
	$post = getPost();
	$configurationKeys = array('dsnPrefix', 'host', 'database', 'user', 'password');
	$credentialKeys = array('adminUsername', 'adminPassword', 'adminPasswordConfirmation');
	$configuration = getArrayValues($post, $configurationKeys);
	$credentials = getArrayValues($post, $credentialKeys);
	
	include "functions/install.php";
	$result = install($configuration, $credentials);
	$args = array();
	if (!isErrorMessage($result)) {
		$mainPanel = '<h2>Easy Exam Successfully Installed!</h2>';
		$args = array('mainPanel' => $mainPanel);
	} else {
		$notification = '<h2>Error</h2>';
		$notification .= nl2br(parseErrorMessage($result, 'text'));
		$args = array('mainPanel' => $notification);
	}
	echo renderView('admin-main', $args);
}

function _allowOnlyIfNotYetInstalled()
{
	if (isInstalled()) {
		redirect(getSettings('Admin Page'));
		die();
	}
}