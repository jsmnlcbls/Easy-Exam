<?php
include 'common.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod == 'GET') {
	include '/views/indexView.php';
} else if ($requestMethod == 'POST') {
	
	
	echo checkAnswersToQuestions($category, $_POST);
}
 