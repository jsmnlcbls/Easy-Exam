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
		$type = substr(filterPOST("questionType"), 0, 1);
		
		include '/functions/question.php';
		$data = array('question' => $question, 'answer' => $answer, 
					  'choices' => $choices, 'category' => $category, 
					  'type' => $type);
		$result = addQuestion($data);
		displayResultNotification($result);
	} else if ($action == "editCategory") {
		$categoryId = intval(filterPOST("categoryId"));
		$categoryName = filterPOST("categoryName");
		$parentCategory = intval(filterPOST("parentCategory"));
		
		include '/functions/category.php';
		$result = editCategory($categoryId, $categoryName, $parentCategory);
		displayResultNotification($result);
	}
}


