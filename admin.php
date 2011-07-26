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
		$menuVisibility = intval(getPOST("menuVisibility"));
		
		$data = array('name' => $name, 'parent' => $parent, 'showOnMenu' => $menuVisibility);
		include '/functions/category.php';
		$result = addCategory($data);
		displayResultNotification($result);
	} else if ($action == "addQuestion") {
		$question = getPOST("question", "");
		$answer = substr(filterPOST("answer", ""), 0, 1);
		$choices = getPOST("choices");
		$category = intval(getPOST("category"));
		$type = substr(filterPOST("questionType"), 0, 1);
		
		include '/functions/question.php';
		$data = array('question' => $question, 'answer' => $answer, 
					  'choices' => $choices, 'category' => $category, 
					  'type' => $type);
		$result = addQuestion($data);
		displayResultNotification($result);
	}else if ($action == "editCategory") {
		$categoryId = intval(filterPOST("categoryId"));
		$categoryName = filterPOST("categoryName");
		$parentCategory = intval(filterPOST("parentCategory"));
		$menuVisibility = intval(getPOST("menuVisibility"));
		
		$data = array('name' => $categoryName, 'parent' => $parentCategory, 'menuVisibility' => $menuVisibility);
		include '/functions/category.php';
		$result = editCategory($categoryId, $data);
		displayResultNotification($result);
	} else if ($action == "editQuestion") {
		$questionId = intval(filterPOST("questionId"));
		$type = substr(filterPOST("questionType"), 0, 1);
		$category = intval(filterPOST("category"));
		$question = getPOST("question");
		$choices = getPOST("choices");
		$answer = substr(filterPOST("answer"), 0, 1);
		
		include '/functions/question.php';
		$data = array('question' => $question, 'type' => $type, 'choices' => $choices,
					  'answer' => $answer, 'category' => $category);
		$result = updateQuestion($questionId, $data);
		displayResultNotification($result);
	} else if ($action == "addExam") {
		
		$name = filterPOST("examName");
		$category = intval(getPOST("category"));
		$startDateTime = filterPOST("startDate") . " " . filterPOST("startTime");
		$endDateTime = filterPOST("endDate") . " " . filterPOST("endTime");
		$timeLimit = filterPOST("timeLimit");
		$passingScore = filterPOST("passingScore");
		
		$data = array('name' => $name, 'category' => $category, 
					  'startDateTime' => $startDateTime, 
					  'endDateTime' => $endDateTime, 'timeLimit' => $timeLimit, 
					  'passingScore' => $passingScore);
		
		include "functions/exam.php";
		$result = addExam($data);
		displayResultNotification($result);
	}
}


