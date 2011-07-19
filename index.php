<?php
include 'common.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod == 'GET') {
	$view = "questions";
	include '/views/indexView.php';
}
 