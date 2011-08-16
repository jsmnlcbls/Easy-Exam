<?php

function addExam($data)
{
	$sql = "INSERT INTO exam (name, start_date_time, end_date_time, time_limit, "
		 . "passing_score, questions_category) VALUES (:name, :startDateTime, "
		 . ":endDateTime, :timeLimit, :passingScore, :category)";

	$parameters = array(':name' => $data['name'], ':category' => $data['category'], 
						':startDateTime' => $data['startDateTime'], 
						':endDateTime' => $data['endDateTime'],
						':timeLimit' => $data['timeLimit'], 
						':passingScore' => $data['passingScore']);
	
	return executeDatabase($sql, $parameters);
}

function updateExam($examId, $data)
{
	$sql = "UPDATE exam SET name=:name, questions_category=:category, "
		 . "start_date_time=:startDateTime, end_date_time=:endDateTime, "
		 . "time_limit=:timeLimit, passing_score=:passingScore WHERE exam_id = :examId";
	
	$parameters = array(':name' => $data['name'], ':category' => $data['category'],
						':startDateTime' => $data['startDateTime'], 
						':endDateTime' => $data['endDateTime'],
						':timeLimit' => $data['timeLimit'],
						':passingScore' => $data['passingScore']);
	
	return executeDatabase($sql, $parameters);
}

function getExamData($id)
{
	$sql = "SELECT * FROM exam WHERE exam_id=:id";
	$parameters = array(':id' => $id);
	$result = queryDatabase($sql, $parameters);
	return array_shift($result);
}

function getAllExams()
{
	$sql = "SELECT * FROM exam";
	return queryDatabase($sql);
}

function getExamQuestions($examId)
{
	$data = getExamData($examId);
	$category = $data['questions_category'];
	$sql = "SElECT * FROM questions WHERE category=:category AND type='e'";
	$parameters = array(':category' => $category);
	return queryDatabase($sql, $parameters);
}

function deleteExam($id)
{
	$sql = "DELETE FROM exam WHERE exam_id = :id";
	$parameters = array(':id' => $id);
	return executeDatabase($sql, $parameters);
}