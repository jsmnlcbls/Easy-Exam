<?php

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