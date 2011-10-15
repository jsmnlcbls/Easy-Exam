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
		$view = renderView('user-questions', array('examData' => $examData));
		$viewArgs = array('innerView' => $view);
	}
	output(renderView('user-index', $viewArgs));
} else if ($requestMethod == "POST") {
	$data = getPost();
	$examId = $data['examId'];
	$revision = $data['revision'];
	$result = gradeExamAnswers($data, $examId, $revision);
	
	$view = '<h2>Exam Results</h2>';
	$view .= "<h4>Correct Answers: {$result['correct_answers']}</h4>";
	$view .= "<h4>Total Points: {$result['total_points']}</h4>";
	$viewArgs = array('innerView' => $view);
	output(renderView('user-index', $viewArgs));
}