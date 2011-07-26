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