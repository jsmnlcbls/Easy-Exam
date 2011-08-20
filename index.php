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
} else if ($requestMethod == "POST") {
	$action = filterPOST("action");
	$viewArgs = array();
	if ($action == "checkReviewAnswers") {
		include '/functions/question.php';
		$category = filterPOST("category");
		$score = checkAnswersToQuestions($category, $_POST, "r");
		$score = round($score, 2);
		$viewArgs = array('innerView' => 'results', 'score' => $score);
	}
	echo renderView("/views/indexView.php", $viewArgs);
}