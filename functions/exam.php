<?php

function addExam($data)
{
	$name = $data['name'];
	$category = $data['category'];
	$startDateTime = $data['startDateTime'];
	$endDateTime = $data['endDateTime'];
	$timeLimit = $data['timeLimit'];
	$passingScore = $data['passingScore'];
	
	$database = getDatabase();
	
	$sql = "INSERT INTO exam (name, start_date_time, end_date_time, time_limit, "
		 . "passing_score, questions_category) VALUES (:name, :startDateTime, "
		 . ":endDateTime, :timeLimit, :passingScore, :category)";
	$statement = $database->prepare($sql);
	$statement->bindValue(":name", $name);
	$statement->bindValue(":category", $category);
	$statement->bindValue(":startDateTime", $startDateTime);
	$statement->bindValue(":endDateTime", $endDateTime);
	$statement->bindValue(":timeLimit", $timeLimit);
	$statement->bindValue(":passingScore", $passingScore);
	
	$result = @$statement->execute();
	if ($result === false) {
		return false;
	}
	return true;
}

function updateExam($examId, $data)
{
	$name = $data['name'];
	$category = $data['category'];
	$startDateTime = $data['startDateTime'];
	$endDateTime = $data['endDateTime'];
	$timeLimit = $data['timeLimit'];
	$passingScore = $data['passingScore'];
	
	$database = getDatabase();
	$sql = "UPDATE exam SET name=:name, questions_category=:category, "
		 . "start_date_time=:startDateTime, end_date_time=:endDateTime, "
		 . "time_limit=:timeLimit, passing_score=:passingScore, questions=:questions";
	$statement = $database->prepare($sql);
	$statement->bindValue(":name", $name);
	$statement->bindValue(":category", $category);
	$statement->bindValue(":startDateTime", $startDateTime);
	$statement->bindValue(":endDateTime", $endDateTime);
	$statement->bindValue(":timeLimit", $timeLimit);
	$statement->bindValue(":passingScore", $passingScore);
	
	$result = $statement->execute();
	if ($result === false) {
		return false;
	}
	return true;
}

function getExamQuestions($examId)
{
	$data = getExamData($examId);
	$questions = array();
	$database = getDatabase();
	if ($data['questions'] == "") {
		$category = $data['questions_category'];
		$sql = "SElECT * FROM questions WHERE category=:category AND type='e'";
		$statement = $database->prepare($sql);
		$statement->bindValue(":category", $category);
		$result = $statement->execute();
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$questions[] = $row;
		}
		return $questions;
	} else {
		
	}
	$database = getDatabase();
}
