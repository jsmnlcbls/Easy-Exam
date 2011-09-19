<?php
include '/functions/common.php';
initialize();
allowLoggedInUserOnly();

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod == "GET") {
	include "functions/views.php";
	$viewArgs = array();
	if (($examId = filterGet("exam", null)) != null) {
		include "functions/exam.php";
		$examData = getExamData($examId);
		$viewArgs = array('innerView' => 'examQuestions',
							'examData' => $examData);
	}
	echo renderView('user-index', $viewArgs);
} else if ($requestMethod == "POST") {
	$action = filterPOST("action");
	$viewArgs = array();
	include '/functions/question.php';
	$category = filterPOST("category");
	$score = 0;
	$score = checkAnswersToQuestions($category, $_POST);
	$score = round($score, 2);
	$viewArgs = array('innerView' => 'results', 'score' => $score);
	echo renderView('user-index', $viewArgs);
}