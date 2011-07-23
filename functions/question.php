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

function getQuestions($category, $type)
{
	$database = getDatabase();
	$statement = $database->prepare("SELECT * FROM questions WHERE category = :category AND :type = :type");
	$statement->bindValue(':category', $category);
	$statement->bindValue(':type', $type);
	
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

function addQuestion($data)
{
	$question = $data['question'];
	$answer = $data['answer'];
	$category = $data['category'];
	$type = $data['type'];
	$choices = $data['choices'];
	
	$choiceValues = array_values($choices);
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
	
	
	$columns = "question, answer, category, type, " . implode(", ", $choiceColumns);
	$values = ":question, :answer, :category, :type, " . implode (", ", $parameterNames);
	
	$database = getDatabase();
	$statement = $database->prepare("INSERT INTO questions ($columns) VALUES ($values)");
	
	
	$statement->bindValue(":question", $question);
	$statement->bindValue(":answer", $answer);
	$statement->bindValue(":category", $category);
	$statement->bindValue(":type", $type);
	
	foreach ($parameterNames as $key => $name) {
		$statement->bindValue($name, $choiceValues[$key]);
	}
	
	$result = @$statement->execute();
	if ($result === false) {
		return false;
	}
	return true;
}

function searchQuestions($data)
{
	$category = $data['category'];
	$question = $data['question'];
	$choice = $data['choice'];
	$type = $data['type'];
	
	$database = getDatabase();
	
	$categoryCondition = "";
	$parameterBindings = array();
	if (is_array($category)) {
		$condition = array();
		foreach ($category as $key => $value) {
			$parameterName = ":category{$key}";
			$condition[] = "category=$parameterName";
			$parameterBindings[$parameterName] = $value;
		}
		$categoryCondition = implode(" OR ", $condition);
	} else if (is_string($category)) {
		$categoryCondition = "category=:category";
		$parameterBindings[':category'] = $category;
	}
	
	$questionCondition = "";
	if ($question != "") {
		$questionCondition = "question LIKE :question";
		$parameterBindings[":question"] = $question;
	}
	
	$choiceCondition = "";
	if ($choice != "") {
		$condition = array();
		foreach (range('A', 'E') as $letter) {
			$condition[] = "choice{$letter} LIKE :choice{$letter}";
			$parameterBindings[":choice{$letter}"] = $choice;
		}
		$choiceCondition = implode(" OR ", $condition);
	}
	
	$typeCondition = "";
	if ($type != "") {
		$typeCondition = "type = :type";
		$parameterBindings[":type"] = $type;
	}
	
	$sqlCondition = array();
	if ("" != $categoryCondition) {
		$sqlCondition[] = $categoryCondition;
	}
	if ("" != $questionCondition) {
		$sqlCondition[] = $questionCondition;
	}
	if ("" != $choiceCondition) {
		$sqlCondition[] = $choiceCondition;
	}
	if ("" != $typeCondition) {
		$sqlCondition[] = $typeCondition;
	}
	
	$sqlCondition = implode (" AND ", $sqlCondition);
	$statement = $database->prepare("SELECT question_id, question FROM questions WHERE $sqlCondition");
	
	foreach ($parameterBindings as $key => $value) {
		$statement->bindValue($key, $value);
	}
	
	$result = $statement->execute();

	$questions = array();
	if ($result !== false) {
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$questions[$row['question_id']] = $row;
		}
		return $questions;
	}
	return false;
}