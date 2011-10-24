<?php
const QUESTIONS_TABLE = 'questions';
const QUESTION_CATEGORY_TABLE = 'question_category';

function addQuestionCategory($inputData)
{
	$data = getArrayValues($inputData, _getQuestionCategoryTableColumns());
	$result = validateQuestionCategoryData($data);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processQuestionCategoryData($data);
	return insertIntoTable(QUESTION_CATEGORY_TABLE, $data);
}

function editQuestionCategory($inputData)
{
	$data = getArrayValues($inputData, _getQuestionCategoryTableColumns(true));
	$result = validateQuestionCategoryData($data);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processQuestionCategoryData($data);
	$condition = 'category_id = :id';
	$parameters = array(':id' => $data['category_id']);
	return updateTable(QUESTION_CATEGORY_TABLE, $data, $condition, $parameters);
}

function getAllQuestionTypes()
{
	$sql = "SELECT id, name FROM question_type ORDER BY id";
	return queryDatabase($sql);
}

function getQuestionCategoryData($id, $columns = '*')
{
	$clause = array('WHERE' => array('condition' => 'category_id=:id',
									'parameters' => array(':id' => $id)));
	$data = selectFromTable(QUESTION_CATEGORY_TABLE, $columns, $clause);
	return array_shift($data);
}

function getCategoryQuestions($category, $includeSubcategories = true)
{
	$result = validateQuestionData($category, 'category');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$questions = _getQuestions($category);
	if ($includeSubcategories) {
		$subCategories = _getSubCategories($category);
		if (count($subCategories) > 0) {
			foreach ($subCategories as $value) {
				$questions += _getQuestions($value);
			}
		}
	}
	return $questions;
}

function addQuestion($inputData)
{
	$type = $inputData['type'];
	$mainTableColumns = _getQuestionTableColumns();
	$secondaryTableColumns = _getSecondaryQuestionTableColumns($type);
	$mainData = getArrayValues($inputData, $mainTableColumns);
	$secondaryData = getArrayValues($inputData, $secondaryTableColumns);
	
	$result = validateQuestionData(($mainData + $secondaryData), null, $type);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processQuestionData($mainData, null, $type);
	_processQuestionData($secondaryData, null, $type);
	if ($type == MULTIPLE_CHOICE_QUESTION || 
		$type == TRUE_OR_FALSE_QUESTION || 
		$type == OBJECTIVE_QUESTION) {
		
		return _insertAndSyncQuestion($type, $mainData, $secondaryData);
	} elseif ($type == ESSAY_QUESTION) {
		return _insertQuestion($mainData);
	}
	
	return false;
}

function searchQuestions($data)
{
	//no validation intended
	
	$category = $data['category'];
	$type = $data['type'];
	$question = $data['question'];
	$owner = isset($data['owner']) ? $data['owner'] : '';
	
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
	
	$ownerCondition = '';
	if ($owner != '') {
		$ownerCondition = 'owner=:owner';
		$parameters[':owner'] = $owner;
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
	if ("" != $ownerCondition) {
		$sqlCondition[] = $ownerCondition;
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
	$result = validateQuestionData($id, 'question_id');
	if (isErrorMessage($result)) {
		return $result;
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
	$result = array_shift($result);
	
	if ($type == MULTIPLE_CHOICE_QUESTION && !empty($result)) {
		$result['choices'] = _decodeMultipleChoices($result['choices']);
		$result['answer'] = _decodeMultipleChoiceAnswer($result['answer']);
	}
	return $result;
}

function updateQuestion($inputData)
{
	$id = $inputData['question_id'];
	$type = $inputData['type'];
	$mainTableColumns = _getQuestionTableColumns();
	$secondaryTableColumns = _getSecondaryQuestionTableColumns($type);
	$mainData = getArrayValues($inputData, $mainTableColumns);
	$secondaryData = getArrayValues($inputData, $secondaryTableColumns);
	$result = validateQuestionData(($mainData + $secondaryData), null, $type);
	if (isErrorMessage($result)) {
		return $result;
	}

	_processQuestionData($mainData, null, $type);
	_processQuestionData($secondaryData, null, $type);
	if ($type == MULTIPLE_CHOICE_QUESTION ||
		$type == TRUE_OR_FALSE_QUESTION ||
		$type == OBJECTIVE_QUESTION) {
		
		return _updateAndSyncQuestion($id, $type, $mainData, $secondaryData);
	} elseif ($type == ESSAY_QUESTION) {
		return _updateQuestion($id, $mainData);
	}	
	
	return false;
}

function deleteQuestion($inputData)
{
	$id = $inputData['question_id'];
	$result = validateQuestionData($id, 'question_id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	return deleteFromTable(QUESTIONS_TABLE, 'question_id=:id', array(':id' => $id));
}

function deleteQuestionCategory($inputData)
{
	$id = $inputData['category_id'];
	$result = validateQuestionCategoryData($id, 'category_id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$condition = 'category_id = :id';
	$parameters = array(':id' => $id);
	return deleteFromTable(QUESTION_CATEGORY_TABLE, $condition, $parameters);
}

function getQuestionEditView($type)
{
	if ($type == MULTIPLE_CHOICE_QUESTION) {
		return "question-multiple-choice-edit";
	} elseif ($type == ESSAY_QUESTION) {
		return "question-essay-edit";
	} elseif ($type == TRUE_OR_FALSE_QUESTION) {
		return "question-true-or-false-edit";
	} elseif ($type == OBJECTIVE_QUESTION) {
		return "question-objective-edit";
	}
}

function validateQuestionCategoryData($value, $key = null)
{
	$validator = function ($value, $key) {
		return _validateQuestionCategoryValue($value, $key);
	};
	
	return validateInputData($validator, $value, $key);
}

function validateQuestionData($value, $key = null, $type = null)
{	
	$validator = function($value, $key) use ($type) {
		return _validateQuestionValue($value, $key, $type);
	};
	
	return validateInputData($validator, $value, $key);
}

//------------------------------------------------------------------------------

function _getQuestionCategoryTableColumns($includePrimaryKeys = false)
{
	$columns = array('name', 'parent_category', 'owner');
	if ($includePrimaryKeys) {
		array_unshift($columns, 'category_id');
	}
	return $columns;
}

function _getQuestionTableColumns($includePrimaryKeys = false)
{
	$columns = array('question', 'category', 'type', 'owner');
	if ($includePrimaryKeys) {
		array_unshift($columns, 'question_id');
	}
	return $columns;
}

function _getSecondaryQuestionTableColumns($type, $includePrimaryKeys = false)
{
	$columns = array();
	if ($type == MULTIPLE_CHOICE_QUESTION) {
		$columns = array('answer', 'category', 'choices', 'randomize');
	} elseif ($type == TRUE_OR_FALSE_QUESTION) {
		$columns = array('answer', 'category');
	} elseif ($type == OBJECTIVE_QUESTION) {
		$columns = array('answer', 'category');
	}
	
	if ($includePrimaryKeys && !empty($columns)) {
		array_unshift($columns, 'question_id');
	}
	return $columns;
}

function _insertAndSyncQuestion($type, $mainData, $secondaryData)
{
	$secondaryTable = _getSecondaryQuestionTableName($type);
	beginTransaction();
	$success = _insertQuestion($mainData);
	if ($success) {
		$secondaryData['question_id'] = getLastInsertedId();
		$success = insertIntoTable($secondaryTable, $secondaryData);
		if ($success) {
			commitTransaction();
			return true;
		}
		rollbackTransaction();
	}
	return false;
}

function _insertQuestion($data)
{
	return insertIntoTable(QUESTIONS_TABLE, $data);
}

function _updateAndSyncQuestion($id, $type, $mainData, $secondaryData)
{
	$secondaryTable = _getSecondaryQuestionTableName($type);
	beginTransaction();
	$success = _updateQuestion($id, $mainData);
	if ($success) {
		$condition = "question_id = :question_id";
		$conditionParameters = array(':question_id' => $id);
		$success = updateTable($secondaryTable, $secondaryData, $condition, $conditionParameters);
		if ($success) {
			commitTransaction();
			return true;
		}
		rollbackTransaction();
	}
	return false;
}

function _updateQuestion($id, $data)
{
	$condition = "question_id = :question_id";
	$conditionParameters = array(':question_id' => $id);
	return updateTable(QUESTIONS_TABLE, $data, $condition, $conditionParameters);
}

function _validateQuestionCategoryValue($value, $key)
{
	$errors = array();
	if ($key == 'category_id' && !ctype_digit("$value")) {
		$errors[] = 'Invalid question category id.';
	} elseif ($key == 'parent_category' && !ctype_digit("$value")) {
		$errors[] = 'Invalid parent question category id.';
	} elseif ($key == 'name' && '' == trim($value)) {
		$errors[] = 'Question category name is empty.';
	}
	return $errors;
}

function _validateQuestionValue($value, $key, $type = null)
{
	$errors = array();
	if ($key == 'question_id' && !ctype_digit("$value")) {
		$errors[] = 'Invalid question id.';
	} elseif ($key == 'category' && !ctype_digit("$value")) {
		$errors[] = 'Invalid question category.';
	} elseif ($key == 'type') {
		if ($value != MULTIPLE_CHOICE_QUESTION && 
			$value != ESSAY_QUESTION &&
			$value != TRUE_OR_FALSE_QUESTION &&
			$value != OBJECTIVE_QUESTION) {
			
			$errors[] = 'Invalid question type.';
		}
	} elseif ($key == 'question' && "" == trim($value)) {
		$errors[] = 'Question is empty.';
	} elseif (null != $type) {
		$result = array();
		if ($type == MULTIPLE_CHOICE_QUESTION) {
			$result = _validateMultipleChoiceValue($value, $key);
		} elseif ($type == OBJECTIVE_QUESTION) {
			$result = _validateObjectiveValue($value, $key);
		} elseif ($type == TRUE_OR_FALSE_QUESTION) {
			$result = _validateTrueOrFalseValue($value, $key);
		}
		if (!empty($result)) {
			$errors = array_merge($errors, $result);
		}
	}
	return $errors;
}

function _validateMultipleChoiceValue($value, $key)
{
	$errors = array();
	if ($key == 'answer' && is_array($value)) {
		foreach ($value as $choice => $isAnswer) {
			if (!ctype_digit("$choice") || !ctype_digit("$isAnswer")) {
				$errors[] = 'Invalid multiple choice answer.';
			}
		}
	} elseif ($key == 'answer' && empty($value)) {
		$errors[] = 'No answer to question was specified.';
	} elseif ($key == 'randomize' && !empty($value) && 
			  !filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
		$errors[] = 'Invalid randomize option.';
	} elseif ($key == 'choices' && is_array($value)) {
		$nonEmpty = 0;
		foreach ($value as $choice) {
			if (trim($choice) != '') {
				$nonEmpty++;
			}
		}
		if ($nonEmpty < 2) {
			$errors[] = 'Number of choices must be greater than one.';
		}
	}
	return $errors;
}

function _validateObjectiveValue($value, $key)
{
	$errors = array();
	if ($key == 'answer' && '' == trim($value)) {
		$errors[] = 'Answer is empty.';
	}
	return $errors;
}

function _validateTrueOrFalseValue($value, $key)
{
	$errors = array();
	if ($key == 'answer' && !filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
		$errors[] = 'Invalid answer.';
	}
	return $errors;
}

function _processQuestionCategoryData(&$value, $key = null) 
{
	$function = function(&$value, $key) {_processQuestionCategoryValue($value, $key);};
	processData($function, $value, $key);
}

function _processQuestionCategoryValue(&$value, $key) {
	if ($key == 'category_id' ||
		$key == 'parent_category') {
		$value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
	} elseif ($key == 'name') {
		$value = trim($value);
	}
}

function _processQuestionData(&$data, $key = null, $type = null)
{
	if (is_array($data) && isset($data['type'])) {
		$type = $data['type'];
	}
	$function = function (&$data, $key) use ($type) {
		_processQuestionValue($data, $key, $type);
	};
	
	processData($function, $data, $key);
}

function _processQuestionValue(&$value, $key, $type = null)
{
	if ($key == 'question_id' || $key == 'category' || $key == 'type') {
		$value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
	} elseif ($key == 'answer') {
		if ($type == TRUE_OR_FALSE_QUESTION) {
			$value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
		} elseif ($type == MULTIPLE_CHOICE_QUESTION &&
				  is_array($value)) {
			$value = _encodeMultipleChoiceAnswer($value);
		} elseif ($type == OBJECTIVE_QUESTION) {
				$value = trim($value);
		}
	} elseif ($key == 'choices' && $type == MULTIPLE_CHOICE_QUESTION) {
		$value = _encodeMultipleChoices($value);
	} elseif ($key == 'randomize' && $type == MULTIPLE_CHOICE_QUESTION) {
		$value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}
}

function _getQuestions($category)
{
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

function _getSubCategories($parent)
{
	function _searchSubCategories($hierarchy)
	{
		$subCategories = array();
		foreach ($hierarchy as $key => $value) {
			if (is_array($value)) {
				$subCategories[] = $key;
				$result = _searchSubCategories($value);
				if (!empty($result)) {
					$subCategories = array_merge($result, $subCategories);
				}
			} else if (empty($value)) {
				$subCategories[] = $key;
			}
		}
		return $subCategories;
	}
	
	$hierarchy = _getCategoryHierarchy($parent);
	if (!empty($hierarchy)) {
		return _searchSubCategories($hierarchy);
	} else {
		return array();
	}
}

function _getCategoryHierarchy($parent = 0)
{
	function _createTree(&$categories, $parent)
	{
		$tree = array();
		foreach ($categories as $key => $value) {
			if ($parent == $value['parent_category'] &&
				"" != $value['name']) {
				$categoryId = $value['category_id'];
				$tree[$categoryId] = _createTree($categories, $categoryId);
				unset($categories[$key]);
			}
		}
		if (!empty($tree)) {
			return $tree;	
		}
	}
	
	$categories = getAllQuestionCategories();
	
	return _createTree($categories, $parent);
}

function _encodeMultipleChoices($choices)
{
	foreach ($choices as $key => $value) {
		if (trim($value) == '') {
			unset($choices[$key]);
		}
	}
	return json_encode($choices);
}

function _decodeMultipleChoices($choices)
{
	return json_decode($choices);
}

function _encodeMultipleChoiceAnswer($answer)
{
	return json_encode($answer);
}

function _decodeMultipleChoiceAnswer($answer)
{
	return json_decode($answer);
}