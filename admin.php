<?php
include "/functions/common.php";

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod == "GET") {
	$view = filterGET('view', "");
	include "/views/adminView.php";
} else if ($requestMethod == "POST") {
	$action = filterPOST('action');
	if ($action == "addCategory") {
		$name = filterPOST("categoryName", "");
		$parent = intval(getPOST("parentCategory", 0));
		
		include '/functions/category.php';
		$result = addCategory($name, $parent);
		displayResultNotification($result);
	} else if ($action == "addQuestion") {
		$question = getPOST("question", "");
		$answer = filterPOST("answer", "");
		$choices = getPOST("choices");
		$category = intval(getPOST("category"));
		
		include '/functions/question.php';
		$result = addQuestion($question, $answer, $choices, $category);
		displayResultNotification($result);
	}
}


