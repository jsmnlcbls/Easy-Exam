<?php
include '/functions/common.php';
	
initialize();
allowLoggedInUserOnly();

$requestMethod = $_SERVER['REQUEST_METHOD'];
include "functions/exam.php";
if ($requestMethod == "GET") {
	include "functions/views.php";
	$viewArgs = array();
	if (($examId = getUrlQuery("exam", null)) != null) {
		$examData = getExamData($examId);
		$viewArgs = array('innerView' => 'examQuestions',
							'examData' => $examData);
	}
	echo renderView('user-index', $viewArgs);
} else if ($requestMethod == "POST") {
	/*
	$viewArgs = array();
	include '/functions/question.php';
	$category = getPost("category");
	$score = 0;
	print_r($_POST);
	$score = checkAnswersToQuestions($category, $_POST);
	$score = round($score, 2);
	$viewArgs = array('innerView' => 'results', 'score' => $score);
	echo renderView('user-index', $viewArgs);
	*/
}