<?php
const QUESTIONS_TABLE = 'questions';
const QUESTION_CATEGORY_TABLE = 'question_category';

function addQuestionCategory($data)
{
	$result = _validateQuestionCategoryData($data);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$data = _sanitizeQuestionCategoryData($data);
	return insertIntoTable(QUESTION_CATEGORY_TABLE, $data);
}

function editQuestionCategory($id, $data)
{
	$input = array_merge(array('category_id' => $id), $data);
	$result = _validateQuestionCategoryData($input);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$id = _sanitizeQuestionCategoryData($id, 'category_id');
	$data = _sanitizeQuestionCategoryData($data);
	return updateTable(QUESTION_CATEGORY_TABLE, $data, 'category_id = :id', array(':id' => $id));
}

function getCategoryQuestions($category, $includeSubcategories = true)
{
	$result = _validateQuestionData($category, 'category');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$category = _sanitizeQuestionData($category, 'category');
	$questions = _getQuestions($category);
	
	if ($includeSubcategories) {
		$subCategories = getSubCategories($category);
		if (count($subCategories) > 0) {
			foreach ($subCategories as $value) {
				$questions += _getQuestions($value);
			}
		}
	}
	return $questions;
}

function checkAnswersToQuestions($category, $userAnswers)
{
	$result = _validateQuestionData($category, 'category');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$category = _sanitizeQuestionData($category, 'category');
	$answers = _getAnswersToQuestions($category);
	$total = count($answers);
	$correctAnswers = 0;
	foreach ($userAnswers as $key => $value) {
		if (isset($answers[$key]) && $answers[$key]['answer'] == $value) {
			$correctAnswers++;
		}
	}
	return (float) ($correctAnswers/$total) * 100;
}

function addQuestion($type, $rawData)
{
	$result = _validateQuestionData($rawData, null, $type);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$type = _sanitizeQuestionData($type, 'type');
	$questionData = _sanitizeQuestionData($rawData);
	$result = false;
	switch (intval($type)) {
		case MULTIPLE_CHOICE_QUESTION:
			//cascade intentional
		case TRUE_OR_FALSE_QUESTION:
			//cascade intentional
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
	//no validation intended
	
	$category = _sanitizeQuestionData($data['category'], 'category');
	$question = _sanitizeQuestionData($data['question'], 'question');
	$type = _sanitizeQuestionData($data['type'], 'type');
	
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
	$result = _validateQuestionData($id, 'question_id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$id = _sanitizeQuestionData($id, 'question_id');
	if ($type != null) {
		$type = _sanitizeQuestionData($type, 'type');
	}
	$sql = "SELECT * FROM questions AS t1 ";
	$join = true;
	if ($type == MULTIPLE_CHOICE_QUESTION) {
		$sql .= "INNER JOIN multiple_choice AS t2 ";
	} elseif ($type == TRUE_OR_FALSE_QUESTION) {
		$sql .= "INNER JOIN true_or_false AS t2 ";
	} elseif ($type == OBJECTIVE_QUESTION) {
		$sql .= "INNER JOIN objective AS t2 ";
	} else {
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
	$type = $rawData['type'];
	$result = array();
	$result[] = _validateQuestionData($type, 'type');
	$result[] = _validateQuestionData($id, 'question_id');
	$result[] = _validateQuestionData($rawData, null, $type);
	foreach ($result as $value) {
		if (isErrorMessage($value)) {
			return $value;
		}
	}
	
	$id = _sanitizeQuestionData($id, 'question_id');
	$type = _sanitizeQuestionData($type, 'type');
	$questionData = _sanitizeQuestionData($rawData);
	$result = false;
	switch ($type) {
		case MULTIPLE_CHOICE_QUESTION:
			//cascade intentional
		case TRUE_OR_FALSE_QUESTION:
			//cascade intentional
		case OBJECTIVE_QUESTION:
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
	$result = _validateQuestionData($id, 'question_id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$id = _sanitizeQuestionData($id, 'question_id');
	$sql = "DELETE FROM questions WHERE question_id=:id";
	$parameters = array(':id' => $id);
	return executeDatabase($sql, $parameters);
}

function getQuestionCategoryTableColumns()
{
	return array('name', 'parent_category');
}

function getQuestionTableColumns($options = array())
{
	$includePrimaryKeys = false;
	if (isset($options['INCLUDE_PRIMARY_KEYS']) && 
			  $options['INCLUDE_PRIMARY_KEYS']) {
		$includePrimaryKeys = true;
	}
	$type = isset($options['TYPE']) ? _sanitizeQuestionData($options['TYPE'], 'type') : null;
	
	$columns = array();
	if ($type == MULTIPLE_CHOICE_QUESTION && $includePrimaryKeys) {
		$choices = array_values(getChoicesLetterColumns());
		$columns = array_merge(array('question_id', 'answer', 'category'), $choices);
	} elseif ($type == MULTIPLE_CHOICE_QUESTION) {
		$choices = array_values(getChoicesLetterColumns());
		$columns = array_merge(array('answer', 'category'), $choices);
	} elseif ($type == TRUE_OR_FALSE_QUESTION && $includePrimaryKeys) {
		$columns = array('question_id', 'answer', 'category');
	} elseif ($type == TRUE_OR_FALSE_QUESTION) {
		$columns = array('answer', 'category');
	} elseif ($type == OBJECTIVE_QUESTION && $includePrimaryKeys) {
		$columns = array('question_id', 'answer', 'category');
	} elseif ($type == OBJECTIVE_QUESTION) {
		$columns = array('answer', 'category');
	} elseif ($type == null && $includePrimaryKeys) {
		$columns = array('question_id', 'question', 'category', 'type');
	} elseif ($type == null) {
		$columns = array('question', 'category', 'type');
	} else {
		$columns = array();
	}
	return $columns;
}

function _insertAndSyncQuestion($type, $data)
{
	$secondaryTable = _getSecondaryQuestionTableName($type);
	$mainTableColumnValues = getArrayValues($data, getQuestionTableColumns());
	$secondaryTableColumnValues = getArrayValues($data, getQuestionTableColumns(array('TYPE' => $type)));
	beginTransaction();
	$success = _insertQuestion($mainTableColumnValues);
	if ($success) {
		$secondaryTableColumnValues['question_id'] = getLastInsertedId();
		$success = insertIntoTable($secondaryTable, $secondaryTableColumnValues);
		if ($success) {
			commitTransaction();
			return true;
		} else {
			rollbackTransaction();
		}
	}
	return false;
}

function _insertQuestion($data)
{
	return insertIntoTable(QUESTIONS_TABLE, $data);
}

function _updateAndSyncQuestion($id, $type, $data)
{
	$secondaryTable = _getSecondaryQuestionTableName($type);
	$mainTableColumnValues = getArrayValues($data, getQuestionTableColumns());
	$secondaryTableColumnValues = getArrayValues($data, getQuestionTableColumns(array('TYPE' => $type)));
	beginTransaction();
	$success = _updateQuestion($id, $mainTableColumnValues);
	if ($success) {
		$condition = "question_id = :question_id";
		$conditionParameters = array(':question_id' => $id);
		$success = updateTable($secondaryTable, $secondaryTableColumnValues, $condition, $conditionParameters);
		if ($success) {
			commitTransaction();
			return true;
		} else {
			rollbackTransaction();
		}
	}
	return false;
}

function _updateQuestion($id, $data)
{
	$condition = "question_id = :question_id";
	$conditionParameters = array(':question_id' => $id);
	return updateTable(QUESTIONS_TABLE, $data, $condition, $conditionParameters);
}

function _validateQuestionCategoryData($value, $key = null)
{
	$validatorFunction = function ($value, $key) {
		return _isValidQuestionCategoryValue($value, $key);
	};
	
	$errorMessageFunction = function ($key, $value) {
		return _getValidateQuestionCategoryErrorMessage($key, $value);
	};
	
	$inputData = $value;
	if (!is_array($value) && is_string($key)) {
		$inputData = array($key => $value);
	}
	
	return validateData($inputData, $validatorFunction, $errorMessageFunction);
}

function _isValidQuestionCategoryValue($value, $key)
{
	if ($key == 'category_id' || $key == 'parent_category' && ctype_digit("$value")) {
		return true;
	} elseif ($key == 'name' && is_string($value) && 
			  !empty($value) && strlen($value) < 65) {
		return true;
	}
	return false;
}

function _getValidateQuestionCategoryErrorMessage($key, $data)
{
	$message = 'Invalid ';
	if ($key == 'category_id') {
		$message .= 'category id';
	} elseif ($key == 'parent_category') {
		$message .= 'parent category';
	} elseif ($key == 'name') {
		$message .= 'category name';
	}
	$message .= ": '$data'";
	return $message;
}

function _validateQuestionData($value, $key = null, $type = null)
{
	$validatorFunction = function($value, $key) use ($type) {
		return _isValidQuestionValue($value, $key, $type);
	};
	
	$errorMessageFunction = function($key, $value) {
		return _getValidateQuestionErrorMessage($key, $value);
	};
	
	$inputData = $value;
	if (!is_array($value) && is_string($key)) {
		$inputData = array($key => $value);
	}
	
	return validateData($inputData, $validatorFunction, $errorMessageFunction);
}

function _isValidQuestionValue($value, $key, $type = null)
{
	if ($key == 'question_id' || $key == 'category' && ctype_digit("$value")) {
		return true;
	} elseif ($key == 'type' && ctype_digit("$value") && $value < 256) {
		return true;
	} elseif ($key == 'question' && "" != trim($value)) {
		return true;
	} elseif (null != $type) {
		if ($type == MULTIPLE_CHOICE_QUESTION) {
			return _isValidMultipleChoiceValue($value, $key);
		} elseif ($type == OBJECTIVE_QUESTION) {
			return _isValidObjectiveValue($value, $key);
		} elseif ($type == TRUE_OR_FALSE_QUESTION) {
			return _isValidTrueOrFalseValue($value, $key);
		}
	}
	return false;
}

function _isValidMultipleChoiceValue($value, $key)
{
	if ($key == 'answer' && strlen($value) == 1 && ctype_alpha($value)) {
		return true;
	} elseif ($key == 'choiceA' || $key == 'choiceB' || $key == 'choiceC' || 
			  $key == 'choiceD' || $key == 'choiceE') {
		return true;
	}
	return false;
}

function _isValidObjectiveValue($value, $key)
{
	if ($key == 'answer' && '' != trim($value)) {
		return true;
	}
	return false;
}

function _isValidTrueOrFalseValue($value, $key)
{
	if ($key == 'answer' && ctype_digit("$value") && ($value == 1 || $value == 0)) {
		return true;
	}
	return false;
}

function _getValidateQuestionErrorMessage($key, $value)
{
	$message = 'Invalid ';
	if ($key == 'question_id') {
		$message .= 'question id';
	} elseif ($key == 'category') {
		$message .= 'question category';
	} elseif ($key == 'type') {
		$message .= 'question type';
	} elseif ($key == 'question') {
		$message .= 'question';
	} elseif ($key == 'answer') {
		$message .= 'answer';
	} elseif ($key == 'choiceA' || $key == 'choiceB' || 
			  $key == 'choiceC' || $key == 'choiceD' || $key == 'choiceE') {
		$message .= 'question choice';
	}
	return $message .= ": '$value'"; 
}

function _sanitizeQuestionCategoryData($rawData, $key = null) 
{
	if (is_array($rawData)) {
		$sanitizedData = array();
		foreach ($rawData as $key => $value) {
			$sanitizedData[$key] = _sanitizeQuestionCategoryValue($value, $key);
		}
		return $sanitizedData;
	} elseif (is_string($key)) {
		return _sanitizeQuestionCategoryValue($rawData, $key);
	}
}

function _sanitizeQuestionCategoryValue($value, $key)
{
	if ($key == 'category_id' || $key == 'parent_category') {
		return intval($value);
	} elseif ($key == 'name') {
		return trim($value);
	}
}

function _sanitizeQuestionData($rawData, $key = null, $type = null)
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
		case 'question':
			return trim($value);
		default:
			//by default accept input as is. 
			//escape or filter it later for output
			return $value;
			break;
	}
}

function _getAnswersToQuestions($category)
{
	$category = _sanitizeQuestionData($category, 'category');
	$joins = array();
	foreach (_getSecondaryQuestionTables() as $table) {
		$joins[] = "SELECT q.question_id, t.answer, q.type FROM questions as q INNER JOIN $table AS t "
			   . "ON q.question_id = t.question_id WHERE q.category = :category AND t.category = :category";
	}
	$sql = implode (" UNION ", $joins);
	
	$answers = queryDatabase($sql, array(':category' => $category), 'question_id');
	return $answers;
}

function _getQuestions($category)
{
	$category = _sanitizeQuestionData($category, 'category');
	$sql = "SELECT * FROM questions WHERE category = :category";
	$parameters = array(':category' => $category);
	return queryDatabase($sql, $parameters);
}

function _getSecondaryQuestionTables()
{
	return array(MULTIPLE_CHOICE_QUESTION => 'multiple_choice',
				 TRUE_OR_FALSE_QUESTION => 'true_or_false',
				 OBJECTIVE_QUESTION => 'objective'
				);
}

function _getSecondaryQuestionTableName($type)
{
	$tables = _getSecondaryQuestionTables();
	if (isset($tables[$type])) {
		return $tables[$type];
	}
	return null;
}

