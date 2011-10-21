<?php
const EXAM_TABLE = 'exam';
const EXAM_ARCHIVES_TABLE = 'exam_archives';
const QUESTION_DISPLAY_ALL_AT_ONCE = 0;
const QUESTION_DISPLAY_ONE_BY_ONE = 1;
const EXAM_UNLIMITED_REPEAT = 0;
const EXAM_NO_REPEAT = 1;

function addExam($inputData)
{
	$step = $inputData['step'];
	if ($step == 1) {
		return _addExamProperties($inputData);
	} elseif ($step == 2) {
		return _updateExamQuestions($inputData);
	}
	return false;
}

function updateExam($inputData)
{
	$step = $inputData['step'];
	if ($step == 1) {
		return _updateExamProperties($inputData);
	} elseif ($step == 2) {
		return _updateExamQuestions($inputData);
	}
}

function getExamData($id)
{
	$result = validateExamData($id, 'exam_id');
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
		$data['start_date_time'] = _decodeDateTime($data['start_date_time']);
		$data['end_date_time'] = _decodeDateTime($data['end_date_time']);
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

function getExamQuestions($examId, $revision, $filterForExam = false)
{
	$questions = _getExamArchiveData($examId, $revision, 'questions');
	if (!$filterForExam) {
		return $questions;
	}
	
	$examData = getExamProperties($examId, $revision);
	foreach ($questions as $key => $value) {
		if (!$value['enabled']) {
			unset($questions[$key]);
		}
	}
	
	$totalQuestions = $examData['total_questions'];
	if ($examData['randomize']) {
		shuffle($questions);
		$questions = array_splice($questions, 0, $totalQuestions);
	} else {
		$queue = new SplPriorityQueue();
		foreach ($questions as $value) {
			$queue->insert($value, (-1 * $value['order']));
		}
		
		$questions = array();
		for ($a = 0; $a < $totalQuestions; $a++) {
			$questions[] = $queue->extract();
		}
	}
	return $questions;
}

function getExamProperties($examId, $revision)
{
	return _getExamArchiveData($examId, $revision, 'properties');
}

function getExamAnswerKey($examId, $revision)
{
	return _getExamArchiveData($examId, $revision, 'answer_key');
}

function deleteExam($inputData)
{
	$id = $inputData['exam_id'];
	$result = validateExamData($id, 'exam_id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processExamData($id, 'exam_id');
	return deleteFromTable(EXAM_TABLE, 'exam_id=:id', array(':id' => $id));
}

function validateExamData($data, $key = null)
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
		if (isset($data['total_questions']) && isset($data['questions_category'])) {
			$data['_questions_count'] = array('minimum' => $data['total_questions'],
											'category' => $data['questions_category']);
		}
	}
	return validateInputData($validator, $data, $key);
}

function gradeExamAnswers($answers, $examId, $revision)
{
	$answerKey = getExamAnswerKey($examId, $revision);
	$correctAnswers = 0;
	$totalPoints = 0;
	foreach ($answers as $id => $answer) {
		if (!isset($answerKey[$id])) {
			continue;
		}
		$correct = false;
		$questionAnswer = $answerKey[$id]['answer'];
		if (is_array($questionAnswer) && is_array($answer)) {
			$diff = array_diff($questionAnswer, $answer);
			if (empty($diff)) {
				$correct = true;
			}
		} elseif (is_array($questionAnswer) && count($questionAnswer) == 1 && is_string($answer)) {
			if (array_pop($questionAnswer) == $answer) {
				$correct = true;
			}
		} elseif ($questionAnswer == $answer) {
			$correct = true;
		}
		if ($correct) {
			$correctAnswers++;
			$totalPoints += $answerKey[$id]['points'];
		}
	}	
	$properties = getExamProperties($examId, $revision);
	$passingScore = $properties['passing_score'];
	$isPercentage = $properties['score_is_percentage'];
	
	return array('correct_answers' => $correctAnswers, 'total_points' => $totalPoints);
}

//------------------------------------------------------------------------------

function _addExamProperties($inputData)
{
	$data = getArrayValues($inputData, _getExamTableColumns());
	$result = validateExamData($data);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processExamData($data);
	$data['revision'] = 0;
	$data['created'] = date('Y-m-d H:i:s');
	$data['modified'] = $data['created'];
	beginTransaction();
	$success = insertIntoTable(EXAM_TABLE, $data);
	if ($success) {
		$id = getLastInsertedId();
		$properties = json_encode($data);
		$archiveData = array('exam_id' => $id, 'revision' => 0, 
							 'properties' => $properties,
							 'created' => $data['created'],
							 'modified' => $data['modified']);
		$success = insertIntoTable(EXAM_ARCHIVES_TABLE, $archiveData);
		if ($success) {
			commitTransaction();
			return $id;
		}
	}
	rollbackTransaction();
	return errorMessage(DATABASE_ERROR, 'Failed to add new exam to database.');	
}

function _updateExamProperties($inputData)
{
	$data = getArrayValues($inputData, _getExamTableColumns(true));
	$result = validateExamData($data);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$examId = $data['exam_id'];
	$oldData = getExamData($examId);
	_processExamData($data);
	_processExamData($oldData);
	$diff = array_diff_assoc($oldData, $data);
	if (count($diff) == 2 && isset($diff['created']) && isset($diff['modified'])) {
		//no changes
		return true;
	}
	
	$revision = $data['revision'];
	$now = date('Y-m-d H:i:s');
	$data['created'] = $oldData['created'];
	$data['modified'] = $now;
	$data['revision'] += 1;
	beginTransaction();
	$success = updateTable(EXAM_TABLE, $data, "exam_id=:id", array(':id' => $examId));
	if ($success) {
		$currentProperties = getExamProperties($examId, $revision);
		$newProperties = json_encode($data);
		if ($currentProperties['is_taken']) {
			$archiveData = array('exam_id' => $examId, 
								 'revision' => ($revision + 1), 
								 'properties' => $newProperties,
								 'created' => $now,
								 'modified' => $now);
			$success = insertIntoTable(EXAM_ARCHIVES_TABLE, $archiveData);
		} else {
			$archiveData = array('properties' => $newProperties, 
								 'revision' => ($revision + 1),
								 'modified' => $now);
			$condition = 'exam_id=:id AND revision=:revisionCount';
			$parameters = array(':id' => $examId, ':revisionCount' => $revision);
			$success = updateTable(EXAM_ARCHIVES_TABLE, $archiveData, $condition, $parameters);
		}
		if ($success) {
			commitTransaction();
			return true;
		}
	}
	rollbackTransaction();
	return errorMessage(DATABASE_ERROR, 'Failed to update exam properties.');
}

function _updateExamQuestions($inputData)
{
	$data = _getExamQuestionsFromInputData($inputData);
	$examId = $inputData['exam_id'];
	$revision = $inputData['revision'];
	$result = _validateExamQuestionsData($examId, $revision, $data);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$questionsData = array();
	$answerKey = array();
	foreach ($data as $id => $value) {
		$question = getQuestionData($id, $value['type']);
		$question['points'] = $value['points'];
		$question['order'] = $value['order'];
		if (isset($value['enabled']) && $value['enabled']) {
			$question['enabled'] = true;
			if (isset($question['answer'])) {
				$answerKey[$id]['answer'] = $question['answer'];
				$answerKey[$id]['points'] = $value['points'];
			}
		} else {
			$question['enabled'] = false;
		}
		$questionsData[$id] = $question;
	}
	
	$questionsData = json_encode($questionsData);
	$answerKey = json_encode($answerKey);
	$modifiedDate = date('Y-m-d H:i:s');
	$examData = array('questions' => $questionsData, 
					  'answer_key' => $answerKey,
					  'modified' => $modifiedDate);
	$condition = 'exam_id=:id AND revision=:revisionCount';
	$parameters = array(':id' => $examId, ':revisionCount' => $revision);
	return updateTable(EXAM_ARCHIVES_TABLE, $examData, $condition, $parameters);
}

function _getExamTableColumns($includePrimaryKeys = false)
{
	$columns = array('name', 'group', 'start_date_time', 'end_date_time', 'time_limit', 
				 'questions_category', 'default_points', 'passing_score',
				 'question_display', 'recorded', 'randomize', 'max_take', 
				 'total_questions', 'revision');
	if ($includePrimaryKeys) {
		array_unshift($columns, 'exam_id');
	}
	return $columns;
}

function _getExamQuestionsFromInputData($inputData)
{
	$data = array();
	foreach ($inputData as $id => $question) {
		if (ctype_digit("$id")) {
			$data[$id] = $question; 
		}
	}
	return $data;
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
	} elseif ($key == 'total_questions') {
		if (!ctype_digit("$value") || $value == 0) {
			$errors[] = 'Invalid total questions count.';
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
		if ($value < 0 || $value > 99) {
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
	} elseif ($key == 'revision' && !ctype_digit("$value")) {
		$errors[] = 'Invalid revision value.';
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
	} elseif ($key == '_questions_count') {
		$questions = getCategoryQuestions($value['category']);
		if (count($questions) < $value['minimum']) {
			$errors[] = 'Total questions count exceeds the available questions of category.';
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

function _validateExamQuestionsData($examId, $revision, $data)
{	
	$error = _validateExamValue($examId, 'exam_id');
	if (!empty($error)) {
		return errorMessage(VALIDATION_ERROR, array_pop($error));
	}
	$error = _validateExamValue($revision, 'revision');
	if (!empty($error)) {
		return errorMessage(VALIDATION_ERROR, array_pop($error));
	}
	
	$questionsCount = 0;
	foreach ($data as $id => $question) {
		if (!ctype_digit("$id")) {
			return errorMessage(VALIDATION_ERROR, 'Invalid question id.');
		}
		if (!empty($question['enabled']) && $question['enabled'] != 1) {
			return errorMessage(VALIDATION_ERROR, 'Invalid question enable value.');
		}
		
		if (isset($question['points']) && ($question['points'] < 0 || $question['points'] > 99)) {
			return errorMessage(VALIDATION_ERROR, 'Invalid question points value.');
		}
		if (isset($question['order']) && !ctype_digit($question['order'])) {
			return errorMessage(VALIDATION_ERROR, 'Invalid question order value.');
		}
		
		if (isset($question['enabled']) && $question['enabled'] == 1) {
			$questionsCount++;
		}
	}

	$examProperties = getExamProperties($examId, $revision);
	if ($examProperties['total_questions'] > $questionsCount) {
		$message = 'Selected questions are less than specified total.';
		return errorMessage(VALIDATION_ERROR, $message);
	}
	if ($questionsCount > $examProperties['total_questions'] && 
		!$examProperties['randomize']) {
		
		$message = 'Selected questions exceeds the specified total.';
		return errorMessage(VALIDATION_ERROR, $message);
	}
}

function _processExamData(&$data, $key = null)
{
	$function = function(&$data, $key) { 
		if (is_array($data)) {
			_processScoreIsPercentage($data);
		}
		_processExamValue($data, $key); 
	};
	processData($function, $data, $key);
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
		$key == 'total_questions') {
		$value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
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
		$value = filter_var(abs($value), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
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

function _getExamArchiveData($examId, $revision, $column)
{
	$result = validateExamData(array('exam_id' => $examId, 'revision' => $revision));
	if (isErrorMessage($result)) {
		return $result;
	}
	
	if ($column != 'questions' && $column != 'properties' && $column != 'answer_key') {
		return errorMessage(VALIDATION_ERROR, 'Unsupported archive column name.');
	}
	
	$tableColumns = $column;
	if ($column == 'properties') {
		$tableColumns = 'properties, is_taken';
	}
	
	$table = EXAM_ARCHIVES_TABLE;
	$sql = "SELECT $tableColumns FROM {$table} WHERE exam_id=:id AND revision=:revision";
	$parameters = array(':id' => $examId, ':revision' => $revision);
	$result = queryDatabase($sql, $parameters);
	if (!empty($result)) {
		$data = array_shift($result);
		$decodedData = json_decode($data[$column], true);
		if ($column == 'properties') {
			$decodedData['is_taken'] = $data['is_taken'];
		}
		return $decodedData;
	}
}

function _decodeDateTime($dateTime)
{
	$dateTime = date_create($dateTime);
	$date = date_format($dateTime, 'Y-m-d');
	$time = date_format($dateTime, 'H:i');
	return array('date' => $date, 'time' => $time);
}