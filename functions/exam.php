<?php

const EXAM_TABLE = "exam";

function addExam($data)
{
	$data = sanitizeExamData($data);
	return insertIntoTable(EXAM_TABLE, $data);
}

function updateExam($examId, $data)
{
	$data = sanitizeExamData($data);
	return updateTable(EXAM_TABLE, $data, "exam_id=:id", array(':id' => $examId));
}

function getExamData($id)
{
	$id = sanitizeExamData($id, 'exam_id');
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
	$examId = sanitizeExamData($examId, 'exam_id');
	$data = getExamData($examId);
	$category = $data['questions_category'];
	$sql = "SElECT * FROM questions WHERE category=:category";
	$parameters = array(':category' => $category);
	return queryDatabase($sql, $parameters);
}

function deleteExam($id)
{
	$id = sanitizeExamData($id, 'exam_id');
	$sql = "DELETE FROM exam WHERE exam_id = :id";
	$parameters = array(':id' => $id);
	return executeDatabase($sql, $parameters);
}

function getExamTableColumns()
{
	return array('name', 'start_date_time', 'end_date_time', 'time_limit', 
				'passing_score', 'questions_category');
}

function sanitizeExamData($rawData, $key = null)
{
	if (is_array($rawData)) {
		$sanitizedData = array();
		foreach ($rawData as $key => $value) {
			$sanitizedData[$key] = _sanitizeExamData($value, $key);
		}
		return $sanitizedData;
	} elseif (is_string($key)) {
		return _sanitizeExamData($rawData, $key);
	}
}

function _sanitizeExamData($rawData, $key)
{
	switch ($key) {
		case 'exam_id':
		case 'time_limit':
		case 'passing_score':
		case 'questions_category':
			return intval($rawData);
		case 'name':
		case 'start_date_time':
		case 'end_date_time':
			return trim($rawData);
		default:
			return $rawData;
	}
}