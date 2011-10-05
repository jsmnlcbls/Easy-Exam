<?php

const EXAM_TABLE = "exam";

function addExam($data)
{
	$result = _validateExamData($data);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$data = _sanitizeExamData($data);
	return insertIntoTable(EXAM_TABLE, $data);
}

function updateExam($examId, $data)
{
	$result = array();
	$result[] = _validateExamData($examId, 'exam_id');
	$result[] = _validateExamData($data);
	foreach ($result as $value) {
		if (isErrorMessage($value)) {
			return $value;
		}
	}
	
	$data = _sanitizeExamData($data);
	return updateTable(EXAM_TABLE, $data, "exam_id=:id", array(':id' => $examId));
}

function getExamData($id)
{
	$result = _validateExamData($id, 'exam_id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$id = _sanitizeExamData($id, 'exam_id');
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

function getAvailableExams()
{
	$localDateTime = date("Y-m-d H:s");
	$sql = "SELECT * FROM exam WHERE :dateTime >= start_date_time AND :dateTime < end_date_time ORDER BY name";
	$parameters = array(':dateTime' => $localDateTime);
	return queryDatabase($sql, $parameters);
}


function getExamQuestions($examId)
{
	$result = _validateExamData($examId, 'exam_id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$examId = _sanitizeExamData($examId, 'exam_id');
	$data = getExamData($examId);
	$category = $data['questions_category'];
	$sql = "SElECT * FROM questions WHERE category=:category";
	$parameters = array(':category' => $category);
	return queryDatabase($sql, $parameters);
}

function deleteExam($id)
{
	$result = _validateExamData($id, 'exam_id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$id = _sanitizeExamData($id, 'exam_id');
	return deleteFromTable(EXAM_TABLE, 'exam_id=:id', array(':id' => $id));
}

function getExamTableColumns()
{
	return array('name', 'start_date_time', 'end_date_time', 'time_limit', 
				'passing_score', 'questions_category');
}

function _validateExamData($value, $key = null)
{
	//validate also if a given exam start and end time interval is valid
	if (is_array($value) && 
		isset($value['start_date_time'])&& 
		isset($value['end_date_time']) &&
		_isValidExamValue($value['start_date_time'], 'start_date_time') &&
		_isValidExamValue($value['end_date_time'], 'end_date_time')) {
	
		$value['exam_interval'] = array('start' => $value['start_date_time'],
										'end' => $value['end_date_time']);
	}
	
	$validatorFunction = function ($value, $key) {
		return _isValidExamValue($value, $key);
	};
	
	$errorMessageFunction = function ($key, $value) {
		return _getValidateExamErrorMessage($key, $value);
	};
	
	$inputData = $value;
	if (!is_array($value) && is_string($key)) {
		$inputData = array($key => $value);
	}
	
	return validateData($inputData, $validatorFunction, $errorMessageFunction);	
}

function _isValidExamValue($value, $key)
{
	if ($key == 'exam_id' && ctype_digit("$value")) {
		return true;
	} elseif ($key == 'name' && trim($value) != "" && (strlen($value) < 64) ) {
		return true;
	} elseif ($key == 'start_date_time' || $key == 'end_date_time') {
		return _isValidDateTime($value);
	} elseif ($key == 'time_limit' && $value > 0 && $value < 256) {
		return true;
	} elseif ($key == 'passing_score' && $value > 0 && $value <= 100) {
		return true;
	} elseif ($key == 'questions_category' && ctype_digit("$value")) {
		return true;
	} elseif ($key == 'exam_interval') {
		$startDateTime = date_create($value['start']);
		$endDateTime = date_create($value['end']);
		if ($startDateTime < $endDateTime) {
			return true;
		}
		return false;
	}
}

function _isValidDateTime($value)
{
	$dateTime = date_parse($value);
	if (!is_array($dateTime) || !isset($dateTime['month']) || 
		!isset($dateTime['year']) || !isset($dateTime['hour']) ||
		!isset($dateTime['minute'])) {
		return false;
	}
	if (!checkdate($dateTime['month'], $dateTime['day'], $dateTime['year'])) {
		return false;
	}
	if ($dateTime['hour'] < 0 || $dateTime['hour'] > 23) {
		return false;
	}
	if ($dateTime['minute'] < 0 || $dateTime['minute'] > 60) {
		return false;
	}
	return true;
}

function _getValidateExamErrorMessage($key, $value)
{
	$message = 'Invalid exam ';
	if ($key == 'name') {
		$message .= 'name';
	} elseif ($key == 'start_date_time') {
		$message .= 'starting time';
	} elseif ($key == 'end_date_time') {
		$message .= 'ending time';
	} elseif ($key == 'time_limit') {
		$message .= 'time limit';
	} elseif ($key == 'passing_score') {
		$message .= 'passing score';
	} elseif ($key == 'questions_category') {
		$message .= 'category for questions';
	} elseif ($key == 'exam_interval') {
		$message .= 'time interval for start and end of the exam';
		$value = $value['start'] . ' to ' . $value['end'];
	}
	$message .= " '$value'.";
	return $message;
}

function _sanitizeExamData($rawData, $key = null)
{
	if (is_array($rawData)) {
		$sanitizedData = array();
		foreach ($rawData as $key => $value) {
			$sanitizedData[$key] = _sanitizeExamValue($value, $key);
		}
		return $sanitizedData;
	} elseif (is_string($key)) {
		return _sanitizeExamValue($rawData, $key);
	}
}

function _sanitizeExamValue($rawData, $key)
{
	switch ($key) {
		case 'exam_id':
		case 'time_limit':
		case 'passing_score':
		case 'questions_category':
			return intval($rawData);
		case 'name':
			return trim($rawData);
		case 'start_date_time':
		case 'end_date_time':
			return date_format(date_create($rawData), "Y-m-d H:i");
		default:
			return $rawData;
	}
}