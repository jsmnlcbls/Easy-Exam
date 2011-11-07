<?php
include '/functions/common.php';
	
initialize();
allowLoggedInUserOnly();

$requestMethod = $_SERVER['REQUEST_METHOD'];
include "functions/exam.php";
if ($requestMethod == "GET") {
	$display = '';
	if (($examId = getUrlQuery("exam", null)) != null) {
		$examData = getExamData($examId);
		if (!empty($examData)) {
			$examGroup = $examData['group'];
			$userGroup = getLoggedInUser('group');
			$intersection = array_intersect($examGroup, $userGroup);
			if (!empty($intersection)) {
				$display = renderView('user-exam-start', array('data' => $examData));
			}
		}
	} elseif (($view = getUrlQuery('view', null)) != null) {
		if ($view == 'exam-results') {
			$display = renderView('user-exam-results');
		}
	}
	$viewArgs = array('innerView' => $display);
	output(renderView('user-index', $viewArgs));
} else if ($requestMethod == "POST") {
	include "functions/views.php";
	$data = getPost();
	
	$action = $data['action'];
	$data['account_id'] = getLoggedInUser('id');
	$data['account_group'] = getLoggedInUser('group');
	$result = '';
	if ($action == 'startExam') {
		$result = _startExamAction($data);
	} elseif ($action == 'endExam') {
		$result = _endExamAction($data);
	}
	_displayResult($result);
}

function _displayResult($output)
{
	$view = $output;
	if (isErrorMessage($output)) {
		$view = '<h2>Error</h2>';
		$view .= parseErrorMessage($output, 'text');
	}
	
	$viewArgs = array('innerView' => $view);
	output(renderView('user-index', $viewArgs));	
}

function _startExamAction($data)
{
	$examData = getExamData($data['exam_id']);
	if (!_isInExamGroup($examData['group'])) {
		return errorMessage(AUTHORIZATION_ERROR, 'Not allowed.');
	}
	
	if ($examData['recorded']) {
		$result = startRecordedExam($data);
		if (!isErrorMessage($result)) {
			return renderView('user-questions', array('examData' => $examData));
		}
		return $result;
	} else {
		return renderView('user-questions', array('examData' => $examData));
	}
}

function _endExamAction($data)
{
	$examData = getExamData($data['exam_id']);
	if (!_isInExamGroup($examData['group'])) {
		return errorMessage(AUTHORIZATION_ERROR, 'Not allowed.');
	}
	
	if ($examData['recorded']) {
		$result = endRecordedExam($data);
		if (!isErrorMessage($result)) {
			return 'You have successfully submitted the exam.';
		}
		return $result;
	} else {
		return 'Exam results: ';
	}
}

function _gradeExamAction($data)
{
	$examId = $data['examId'];
	$revision = $data['revision'];
	$result = gradeExamAnswers($data, $examId, $revision);
	
	$view = '<h2>Exam Results</h2>';
	$view .= "<h4>Correct Answers: {$result['correct_answers']}</h4>";
	$view .= "<h4>Total Points: {$result['total_points']}</h4>";
}

function _isInExamGroup($examGroup)
{
	$userGroup = getLoggedInUser('group');
	$intersection = array_intersect($examGroup, $userGroup);
	if (!empty($intersection)) {
		return true;
	}
	return false;
}