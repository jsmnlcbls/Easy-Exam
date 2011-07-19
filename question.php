<?php

function checkAnswersToQuestions($category, $userAnswers)
{
	$answers = getAnswersToQuestions($category);
	$total = count($answers);
	$correctAnswers = 0;
	foreach ($userAnswers as $key => $value) {
		if (isset($answers[$key]) && $answers[$key] == $value) {
			$correctAnswers++;
		}
	}
	
	return (float) ($correctAnswers/$total) * 100;
}

function getAnswersToQuestions($category)
{
	$database = getDatabase();
	$statement = $database->prepare("SELECT question_id, answer FROM questions WHERE category = :category");
	$statement->bindValue(':category', $category);
	
	$result = @$statement->execute();
	$answers = array();
	if ($result !== false) {
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$answers[$row['question_id']] = $row['answer'];
		}
		return $answers;
	}
	return false;
}

function getQuestions($category, $count)
{
	$database = getDatabase();
	$statement = $database->prepare("SELECT * FROM questions WHERE category = :category");
	$statement->bindValue(':category', $category);
	
	$result = @$statement->execute();
	$questions = array();
	if ($result !== false) {
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$questions[$row['question_id']] = $row;
		}
		return $questions;
	}
	return false;
}

function addQuestion($question, $answer, $options, $category)
{
	$choiceValues = array_values($options);
	$choiceColumns = array("choiceA", "choiceB", "choiceC", "choiceD", "choiceE");
	
	$valuesLength = count($choiceValues);
	$columnLength = 5; //count($choiceColumns);
	//remove excess columns to be inserted upon if options are less than the
	//maximum number of columns
	for ($a = 0; $a < ($columnLength - $valuesLength); $a++) {
		array_pop($choiceColumns);
	}
	
	//build parameterNames out of the remaining column names
	$parameterNames = array();
	foreach($choiceColumns as $value) {
		$parameterNames[] = ":" . $value;
	}
	
	
	$columns = "question, answer, category, " . implode(", ", $choiceColumns);
	$values = ":question, :answer, :category, " . implode (", ", $parameterNames);
	
	$database = getDatabase();
	$statement = $database->prepare("INSERT INTO questions ($columns) VALUES ($values)");
	
	
	$statement->bindValue(":question", $question);
	$statement->bindValue(":answer", $answer);
	$statement->bindValue(":category", $category);
	
	foreach ($parameterNames as $key => $name) {
		$statement->bindValue($name, $choiceValues[$key]);
	}
	
	$result = @$statement->execute();
	if ($result === false) {
		return false;
	}
	return true;
}