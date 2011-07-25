<?php
include '/functions/common.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod == 'GET') {
	include '/functions/question.php';
	
	$view = "";
	$category = filterGet("category", "");
	$questions = array();
	if ("" != $category) {
		$category = intval($category);
		$subCategories = getSubCategories($category);
		$questions = getQuestions($category, "r");
		if (count($subCategories) > 0) {
			foreach ($subCategories as $value) {
				$questions += getQuestions($value, 'r');
			}
		}
		$view = "questions";
	}
	include '/views/indexView.php';
}