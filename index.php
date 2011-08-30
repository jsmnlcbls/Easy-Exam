<?php
include '/functions/common.php';
initialize();
allowLoggedInUserOnly();

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod == "GET") {
	$viewArgs = array();
	if (($reviewCategory = filterGet("reviewCategory", null)) != null) {
		$questionTypeId = getQuestionTypeId('Review Question');
		$viewArgs = array('innerView' => 'reviewQuestions', 
							'reviewCategory' => $reviewCategory,
							'questionTypeId' => $questionTypeId);
	} elseif (($examId = filterGet("exam", null)) != null) {
		include "functions/exam.php";
		$questionTypeId = getQuestionTypeId('Exam Question');
		$examData = getExamData($examId);
		$viewArgs = array('innerView' => 'examQuestions',
							'examData' => $examData,
							'questionTypeId' => $questionTypeId);
	}
	echo renderView(getViewFile('indexView'), $viewArgs);
} else if ($requestMethod == "POST") {
	$action = filterPOST("action");
	$viewArgs = array();
	include '/functions/question.php';
	$category = filterPOST("category");
	$score = 0;
	if ($action == "checkReviewAnswers") {
		$score = checkAnswersToQuestions($category, $_POST, getQuestionTypeId('Review Question'));
	} elseif ($action == "checkExamAnswers") {
		$score = checkAnswersToQuestions($category, $_POST, getQuestionTypeId('Exam Question'));
	}
	$score = round($score, 2);
	$viewArgs = array('innerView' => 'results', 'score' => $score);
	echo renderView(getViewFile('indexView'), $viewArgs);
}