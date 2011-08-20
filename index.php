<?php
include '/functions/common.php';
allowLoggedInUserOnly();

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod == "GET") {
	$reviewCategory = filterGet("reviewCategory", null);
	$viewArgs = array();
	if (null != $reviewCategory) {
		$viewArgs = array('innerView' => 'reviewQuestions', 
							'reviewCategory' => $reviewCategory);
	}
	echo renderView("/views/indexView.php", $viewArgs);
}