<?php
include '/functions/common.php';
include '/functions/question.php';

$action = filterPOST("action");
$category = filterPOST("category");
	
$score = checkAnswersToQuestions($category, $_POST);
$score = round($score, 2);
$view = "results";

include '/views/indexView.php';
