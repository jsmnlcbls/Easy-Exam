<?php
include "common.php";

$requestMethod = $_SERVER['REQUEST_METHOD'];


if ($requestMethod == "GET") {
	$view = filterGET('view', "");
	include "/views/adminView.php";
} else if ($requestMethod == "POST") {
	$action = filterPOST('action');
	if ($action == "addCategory") {
		$name = filterPOST("categoryName", "");
		$parent = filterPOST("parentCategory", "0");
		
		include 'category.php';
		$result = addCategory($name, $parent);
		displayResultNotification($result);
	}
}


