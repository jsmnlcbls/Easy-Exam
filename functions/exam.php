<?php
const EXAM_TABLE = "exam";
const QUESTION_DISPLAY_ALL_AT_ONCE = 0;
const QUESTION_DISPLAY_ONE_BY_ONE = 1;
const EXAM_UNLIMITED_REPEAT = 0;
const EXAM_NO_REPEAT = 1;

function addExam($data)
{
	$result = _validateExamData($data);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processExamData($data);
	return insertIntoTable(EXAM_TABLE, $data);
}

function updateExam($examId, $data)
{
	$data = array_merge(array('exam_id' => $examId), $data);
	$result = _validateExamData($data);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processExamData($data);
	return updateTable(EXAM_TABLE, $data, "exam_id=:id", array(':id' => $examId));
}

function getExamData($id)
{
	$result = _validateExamData($id, 'exam_id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$sql = "SELECT * FROM exam WHERE exam_id=:id";
	$parameters = array(':id' => $id);
	$result = queryDatabase($sql, $parameters);
	$data = array_shift($result);
	if (!empty($data)) {
		$data['group'] = decodeArray($data['group']);
		if ($data['max_take'] != EXAM_UNLIMITED_REPEAT &&
			$data['max_take'] != EXAM_NO_REPEAT) {
			$data['max_take'] = $data['max_take'] - 1;
		}
	}
	return $data;
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
	
	$examId = _processExamData($examId, 'exam_id');
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
	
	_processExamData($id, 'exam_id');
	return deleteFromTable(EXAM_TABLE, 'exam_id=:id', array(':id' => $id));
}

function getExamTableColumns()
{
	return array('name', 'group', 'start_date_time', 'end_date_time', 'time_limit', 
				 'questions_category', 'default_points', 'passing_score',
				 'question_display', 'recorded', 'randomize', 'max_take', 'max_questions');
}

function _validateExamData($data, $key = null)
{
	$validator = function($data, $key) {return _validateExamValue($data, $key);};
	
	if (is_array($data)) {
		//check for the exam interval also
		if (isset($data['start_date_time']) &&	isset($data['end_date_time'])) {
			$data['_exam_interval'] = array('start' => $data['start_date_time'],
											'end' => $data['end_date_time']);
			unset($data['start_date_time']);
			unset($data['end_date_time']);
		}
	}
	return validateInputData($validator, $data, $key);
}

function _validateExamValue($value, $key)
{
	//storage limits of database datatypes are not checked here.
	$errors = array();
	if ($key == 'exam_id' && !ctype_digit("$value")) {
		$errors[] = "Invalid exam id."; 
	} elseif ($key == 'name') {
		$name = trim($value);
		if ($name == '') {
			$errors[] = 'Exam name is empty.';
		} elseif (strlen($name) > 64) {
			$errors[] = 'Exam name is too long.';
		}
	} elseif ($key == 'group') {
		if (is_array($value)) {
			foreach ($value as $group) {
				if (!ctype_digit("$group")) {
					$errors[] = 'Invalid group id.';
				}
			}
		} else {
			$errors[] = 'Invalid user groups.';
		}
	} elseif ($key == 'questions_category') {
		if (!ctype_digit("$value")) {
			$errors[] = 'Invalid question category';
		}
	} elseif ($key == 'max_questions') {
		if (!ctype_digit("$value") || $value == 0) {
			$errors[] = 'Invalid max questions count.';
		}
	} elseif ($key == 'start_date_time') {
		if (is_array($value)) {
			if (!_isValidExamDate($value['date'])) {
				$errors[] = 'Invalid exam start date.';
			}
			if (!_isValidExamTime($value['time'])) {
				$errors[] = 'Invalid exam start time.';
			}
		} else {
			$errors[] = 'Invalid start date time.';
		}
	} elseif ($key == 'end_date_time') {
		if (is_array($value)) {
			if (!_isValidExamDate($value['date'])) {
				$errors[] = 'Invalid exam end date.';
			}
			if (!_isValidExamTime($value['time'])) {
				$errors[] = 'Invalid exam end time.';
			}
		} else {
			$errors[] = 'Invalid end date time.';
		}
	} elseif ($key == 'time_limit') {
		if ('' != trim($value) && filter_var($value, FILTER_VALIDATE_FLOAT)) {
			if ($value <= 0) {
				$errors[] = 'Time limit must be greater than zero.';
			}
		} else {
			$errors[] = 'Invalid time limit.';
		}
	} elseif ($key == 'default_points') {
		if ($value < 0 || $value > 10) {
			$errors[] = 'Points per question is out of range.';
		}
	} elseif ($key == 'passing_score') {
		if (preg_match('/^[0-9]+(%)$/', $value)) {
			$percentage = str_replace('%', '', $value);
			if ($percentage < 0 || $percentage > 100) {
				$errors[] = 'Invalid passing score percentage.';
			}
		} elseif (!ctype_digit("$value")) {
			$errors[] = 'Invalid passing score points.';
		}
	} elseif ($key == 'question_display') {
		if (is_array($value)) {
			if ($value['mode'] == 'G') {
				if (empty($value['group'])) {
					$errors[] = 'Number of questions per groups is empty.';
				} else if (!ctype_digit($value['group'])) {
					$errors[] = 'Invalid number of questions per group.';
				}
			} else if ($value['mode'] != QUESTION_DISPLAY_ALL_AT_ONCE &&
					   $value['mode'] != QUESTION_DISPLAY_ONE_BY_ONE) {
				$errors[] = 'Invalid question display options.';
			}
		} else {
			$errors[] = 'Invalid question display input.';
		}
	} elseif ($key == 'recorded' && '' != $value &&
			  !filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
		$errors[] = 'Invalid record exam option.';
	} elseif ($key == 'randomize' &&  '' != $value && 
			!filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
		$errors[] = 'Invalid randomize exam option.';
	} elseif ($key == 'max_take' && is_array($value)) {
		if ((!isset($value['enabled']) || 
			!filter_var($value['enabled'], FILTER_VALIDATE_BOOLEAN)) && 
			$value['count'] > 0) {
			
			$errors[] = 'Repeatable must be enabled first before setting a limit.';
		}
	} elseif ($key == '_exam_interval') {
		$result = array();
		$result[] = _validateExamValue($value['start'], 'start_date_time');
		$result[] =	_validateExamValue($value['end'], 'end_date_time');
		
		if (empty($result[0]) && empty($result[1])) {
			$startDateTime = date_create(implode(' ', $value['start']));
			$endDateTime = date_create(implode(' ', $value['end']));
			if ($startDateTime > $endDateTime) {
				$errors[] = 'Exam start is after exam end time.';
			} elseif ($startDateTime == $endDateTime) {
				$errors[] = 'Exam start is equal to exam end time.';
			}
		} else {
			$errors = array_merge($result[0], $result[1]);
		}
	}
	return $errors;
}

function _isValidExamDate($value)
{
	$result = date_parse($value);
	if (is_array($result) && $result['error_count'] == 0) {
		if (checkdate($result['month'], $result['day'], $result['year'])) {
			return true;
		}
	}
	return false;
}

function _isValidExamTime($value)
{
	$parts = explode(':', $value);
	if (count($parts) == 2) {
		$hour = $parts[0];
		$minutes = $parts[1];
		if ($hour < 0 || $hour > 23) {
			return false;
		}
		if ($minutes < 0 || $minutes > 59) {
			return false;
		}
		return true;
	}
	return false;
}


function _processExamData(&$data, $key = null)
{
	if (is_array($data)) {
		$function = function(&$data, $key) { _processExamValue($data, $key); };
		_processScoreIsPercentage($data);
		array_walk($data, $function);
	} elseif (is_string($key)) {
		_processExamValue($data, $key);
	}
}

function _processScoreIsPercentage(&$data)
{
	if (isset($data['passing_score'])) {
		if (false !== strrchr($data['passing_score'], '%')) {
			$data['score_is_percentage'] = true;
			$data['passing_score'] = str_replace('%', '', $data['passing_score']);
		} else {
			$data['score_is_percentage'] = false;
		}
	}
}

function _processExamValue(&$value, $key)
{
	if ($key == 'exam_id' || 
		$key == 'questions_category' || 
		$key == 'default_points' ||
		$key == 'passing_score' ||
		$key == 'max_questions') {
		filter_var($value, FILTER_SANITIZE_NUMBER_INT);
	} elseif ($key == 'name') {
		$value = trim($value);
	} elseif ($key == 'group' && is_array($value)) {
		$value = encodeArray($value);
	} elseif ($key == 'start_date_time' || 
			  $key == 'end_date_time' &&
			  is_array($value)) {
		$dateTime = $value['date'] . ' ' . $value['time'];
		$value = date_format(date_create($dateTime), "Y-m-d H:i");
	} elseif ($key == 'time_limit') {
		filter_var(abs($value), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	} elseif ($key == 'recorded' || 
			  $key == 'randomize') {
		$value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
	} elseif ($key == 'question_display' && is_array($value)) {
		if ($value['mode'] == 'G' &&
			isset($value['group']) && $value['group'] > 1) {
			$value = $value['group'];
		} elseif ($value['mode'] == QUESTION_DISPLAY_ALL_AT_ONCE ||
				  $value['mode'] == QUESTION_DISPLAY_ONE_BY_ONE) {
			$value = $value['mode'];
		}
	} elseif ($key == 'max_take' && is_array($value)) {
		if (isset($value['enabled']) && $value['enabled']) {
			if ($value['count'] > 1) {
				$value = $value['count'] + 1;
			} else {
				$value = EXAM_UNLIMITED_REPEAT;
			}
		} else {
			$value = EXAM_NO_REPEAT;
		}	
	}
}