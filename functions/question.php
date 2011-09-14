<?php

function sanitizeQuestionData($rawData, $key = null, $type = null)
{
	if (is_array($rawData) && $key == null) {
		if (isset($rawData['type'])) {
			$type = _sanitizeQuestionValue($rawData['type'], 'type');
		}
		$sanitized = array();
		foreach ($rawData as $key => $value) {
			$sanitized[$key] = _sanitizeQuestionValue($value, $key, $type);
		}
		return $sanitized;
	} else if (is_string($key)) {
		return _sanitizeQuestionValue($rawData, $key, $type); 
	}
}



function getCategoryQuestions($category, $includeSubcategories = true)
{
	$questions = getQuestions($category);
	
	if ($includeSubcategories) {
		$subCategories = getSubCategories($category);
		if (count($subCategories) > 0) {
			foreach ($subCategories as $value) {
				$questions += getQuestions($value);
			}
		}
	}
	return $questions;
}

function checkAnswersToQuestions($category, $userAnswers)
{
	$answers = getAnswersToQuestions($category);
	$total = count($answers);
	$correctAnswers = 0;
	foreach ($userAnswers as $key => $value) {
		if (isset($answers[$key]) && $answers[$key]['answer'] == $value) {
			$correctAnswers++;
		}
	}
	return (float) ($correctAnswers/$total) * 100;
}

function getAnswersToQuestions($category)
{
	$joins = array();
	foreach (getSecondaryQuestionTables() as $table) {
		$joins[] = "SELECT q.question_id, t.answer, q.type FROM questions as q INNER JOIN $table AS t "
			   . "ON q.question_id = t.question_id WHERE q.category = :category AND t.category = :category";
	}
	$sql = implode (" UNION ", $joins);
	
	$answers = queryDatabase($sql, array(':category' => $category), 'question_id');
	return $answers;
}

function getQuestions($category)
{
	$sql = "SELECT * FROM questions WHERE category = :category";
	$parameters = array(':category' => $category);
	return queryDatabase($sql, $parameters);
}

function addQuestion($type, $rawData)
{
	$type = sanitizeQuestionData($type, 'type');
	$questionData = sanitizeQuestionData(_getColumnValues($rawData, $type, "add"));
	$result = false;
	switch (intval($type)) {
		case MULTIPLE_CHOICE_QUESTION:
			$result = _insertAndSyncQuestion($type, $questionData);
			break;
		case TRUE_OR_FALSE_QUESTION:
			$result = _insertAndSyncQuestion($type, $questionData);
			break;
		case OBJECTIVE_QUESTION:
			$result = _insertAndSyncQuestion($type, $questionData);
			break;
		case ESSAY_QUESTION:
			$result = _insertQuestion($questionData);
			break;
		default:
			$result = false;
			break;
	}
	return $result;
}

function searchQuestions($data)
{
	$category = $data['category'];
	$question = $data['question'];
	$choice = $data['choice'];
	$type = $data['type'];
	
	$categoryCondition = "";
	$parameters = array();
	if (is_array($category)) {
		$condition = array();
		foreach ($category as $key => $value) {
			$parameterName = ":category{$key}";
			$condition[] = "category=$parameterName";
			$parameters[$parameterName] = $value;
		}
		$categoryCondition = implode(" OR ", $condition);
	} else if (!empty($category)) {
		$categoryCondition = "category=:category";
		$parameters[':category'] = $category;
	}
	
	$questionCondition = "";
	if ($question != "") {
		$questionCondition = "question LIKE :question";
		$parameters[":question"] = $question;
	}
	
	$choiceCondition = "";
	if ($choice != "") {
		$condition = array();
		foreach (getChoicesLetterColumns() as $columnName) {
			$condition[] = "{$columnName} LIKE :{$columnName}";
			$parameters[":{$columnName}"] = $choice;
		}
		$choiceCondition = implode(" OR ", $condition);
	}
	
	$typeCondition = "";
	if ($type != "") {
		$typeCondition = "type = :type";
		$parameters[":type"] = $type;
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
	if ("" == $sqlCondition) {
		return array();
	}
	
	$sql = "SELECT question_id, type, question FROM questions WHERE $sqlCondition";
	return queryDatabase($sql, $parameters);
}

function getQuestionData($id, $type = null)
{
	$sql = "SELECT * FROM questions AS t1 ";
	$join = true;
	if ($type == MULTIPLE_CHOICE_QUESTION) {
		$sql .= "INNER JOIN multiple_choice AS t2 ";
	} elseif ($type == TRUE_OR_FALSE_QUESTION) {
		$sql .= "INNER JOIN true_or_false AS t2 ";
	} elseif ($type == OBJECTIVE_QUESTION) {
		$sql .= "INNER JOIN objective AS t2 ";
	} elseif ($type == ESSAY_QUESTION) {
		$join = false;
	}
	if ($join) {
		$sql .= "ON t1.question_id = t2.question_id ";
	}
	$sql .= "WHERE t1.question_id=:questionId";
	
	$parameters = array(':questionId' => $id);
	$result = queryDatabase($sql, $parameters);
	return array_shift($result);
}

function updateQuestion($id, $rawData)
{
	$id = sanitizeQuestionData($id, 'question_id');
	$type = sanitizeQuestionData($rawData['type'], 'type');
	$questionData = sanitizeQuestionData(_getColumnValues($rawData, $type, "edit"));
	$result = false;
	switch ($type) {
		case MULTIPLE_CHOICE_QUESTION:
			$result = _updateAndSyncQuestion($id, $type, $questionData);
			break;
		case TRUE_OR_FALSE_QUESTION:
			$result = _updateAndSyncQuestion($id, $type, $questionData);
			break;
		case OBJECTIVE_QUESTION:
			print_r($questionData);
			$result = _updateAndSyncQuestion($id, $type, $questionData);
			break;
		case ESSAY_QUESTION:
			$result = _updateQuestion($id, $questionData);
			break;
		default:
			break;
	}
	return $result;
}

function deleteQuestion($id)
{
	$sql = "DELETE FROM questions WHERE question_id=:id";
	$parameters = array(':id' => $id);
	return executeDatabase($sql, $parameters);
}

function _insertAndSyncQuestion($type, $data)
{
	$secondaryTableList = getSecondaryQuestionTables();
	$secondaryTable = $secondaryTableList[$type];
	$mainTableColumnValues = _getNonPrimaryQuestionColumnValues($data);
	$secondaryTableColumnValues = array_diff_key($data, $mainTableColumnValues);
	beginTransaction();
	$success = _insertQuestion($mainTableColumnValues);
	if ($success) {
		$secondaryTableColumnValues['category'] = $data['category'];
		$secondaryTableColumnValues['question_id'] = getLastInsertedId();
		$success = insertIntoTable($secondaryTable, $secondaryTableColumnValues);
		if ($success) {
			commitTransaction();
			return true;
		} else {
			rollbackTransaction();
			return false;
		}
	}
}

function _insertQuestion($data)
{
	return insertIntoTable('questions', $data);
}

function _updateAndSyncQuestion($id, $type, $data)
{
	$secondaryTableList = getSecondaryQuestionTables();
	$secondaryTable = $secondaryTableList[$type];
	$mainTableColumnValues = _getNonPrimaryQuestionColumnValues($data);
	$secondaryTableColumnValues = array_diff_key($data, $mainTableColumnValues);
	beginTransaction();
	$success = _updateQuestion($id, $mainTableColumnValues);
	if ($success) {
		$secondaryTableColumnValues['category'] = $mainTableColumnValues['category'];
		$condition = "question_id = :question_id";
		$conditionParameters = array(':question_id' => $id);
		$success = updateTable($secondaryTable, $secondaryTableColumnValues, $condition, $conditionParameters);
		if ($success) {
			commitTransaction();
			return true;
		} else {
			rollbackTransaction();
			return false;
		}
	}
}

function _updateQuestion($id, $data)
{
	$columnValues = _getNonPrimaryQuestionColumnValues($data);
	$condition = "question_id = :question_id";
	$conditionParameters = array(':question_id' => $id);
	return updateTable('questions', $columnValues, $condition, $conditionParameters);
}


function _getNonPrimaryQuestionColumnValues($data)
{
	return getArrayValues($data, array('question', 'category', 'type'));
}

function _getColumnValues($rawData, $type, $operation)
{
	$keys = array('question', 'category', 'type');
	if ($type == MULTIPLE_CHOICE_QUESTION && $operation == "add") {
		$keys = array_merge($keys, array('answer'), getChoicesLetterColumns());
	} elseif ($type == MULTIPLE_CHOICE_QUESTION && $operation == "edit") {
		$keys = array_merge($keys, array('answer', 'question_id'), getChoicesLetterColumns());
	} elseif ($type == TRUE_OR_FALSE_QUESTION && $operation == "add") {
		$keys = array_merge($keys, array('answer')); 
	} elseif ($type == TRUE_OR_FALSE_QUESTION && $operation == "edit") {
		$keys = array_merge($keys, array('answer', 'question_id'));
	} elseif ($type == OBJECTIVE_QUESTION && $operation == "add") { 
		$keys = array_merge($keys, array('answer'));
	} elseif ($type == OBJECTIVE_QUESTION && $operation == "edit") { 
		$keys = array_merge($keys, array('answer', 'question_id'));
	} elseif ($type == ESSAY_QUESTION) {
		//default key values
	} else {
		return array();
	}
	return getArrayValues($rawData, $keys);
}

function _sanitizeQuestionValue($value, $key, $type = null)
{
	switch ($key) {
		case 'question_id':
			//cascade intentional
		case 'category':
			//cascade intentional
		case 'type':
			return intval($value);
		case 'answer':
			if ($type == MULTIPLE_CHOICE_QUESTION) { 
				return substr($value, 0, 1);
			} elseif ($type == TRUE_OR_FALSE_QUESTION) {
				return (bool) $value;
			} else {
				return $value;
			}
			break;
		default:
			//by default accept input as is. 
			//escape or filter it later for output
			return $value;
			break;
	}
}