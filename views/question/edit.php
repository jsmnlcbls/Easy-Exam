<?php
include "functions/question.php";
$questionId = getUrlQuery('question-id');
$type = getUrlQuery('type');
$view = '';
$data = escapeOutput(getQuestionData($questionId, $type));
if ($type == MULTIPLE_CHOICE_QUESTION) {
	$view = 'question-multiple-choice-edit';
} elseif ($type == ESSAY_QUESTION && $data['type'] == ESSAY_QUESTION) {
	$view = 'question-essay-edit';
} elseif ($type == TRUE_OR_FALSE_QUESTION) {
	$view = 'question-true-or-false-edit';
} elseif ($type == OBJECTIVE_QUESTION) {
	$view = 'question-objective-edit';
}

if (!empty($view)) {
	echo renderView($view, array('data' => $data));
}