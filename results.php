<?php
include 'common.php';
include 'question.php';

$action = filterPOST("action");
$category = filterPOST("category");
	
$score = checkAnswersToQuestions($category, $_POST);
$view = "results";

include '/views/indexView.php';
