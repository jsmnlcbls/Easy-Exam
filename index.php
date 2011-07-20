<?php
include '/functions/common.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod == 'GET') {
	include '/functions/question.php';
	
	$view = "";
	if ("" != filterGET("category")) {
		$view = "questions";
	}
	include '/views/indexView.php';
}