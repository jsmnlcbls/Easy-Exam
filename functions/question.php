<?php
const QUESTIONS_TABLE = 'questions';
const QUESTION_CATEGORY_TABLE = 'question_category';

function addQuestionCategory($data)
{
	$result = validateQuestionCategoryData($data);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processQuestionCategoryData($data);
	return insertIntoTable(QUESTION_CATEGORY_TABLE, $data);
}

function editQuestionCategory($id, $data)
{
	$input = array_merge(array('category_id' => $id), $data);
	$result = validateQuestionCategoryData($input);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processQuestionCategoryData($data);
	return updateTable(QUESTION_CATEGORY_TABLE, $data, 'category_id = :id', array(':id' => $id));
}

function getAllQuestionTypes()
{
	$sql = "SELECT id, name FROM question_type ORDER BY id";
	return queryDatabase($sql);
}

function getQuestionCategoryData($id)
{
	$sql = "SELECT * FROM question_category WHERE category_id = :id";
	$parameters = array(':id' => $id);
	$result = queryDatabase($sql, $parameters);
	return array_shift($result);
}

function getCategoryQuestions($category, $includeSubcategories = true)
{
	$result = validateQuestionData($category, 'category');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processQuestionData($category, 'category');
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

function addQuestion($type, $data)
{
	$result = validateQuestionData($data, null, $type);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processQuestionData($data);
	$result = false;
	switch (intval($type)) {
		case MULTIPLE_CHOICE_QUESTION:
			$data['choices'] = _encodeMultipleChoices($data['choices']);
			$data['answer'] = _encodeMultipleChoiceAnswer($data['answer']);
		case TRUE_OR_FALSE_QUESTION:
			//cascade intentional
		case OBJECTIVE_QUESTION:
			$result = _insertAndSyncQuestion($type, $data);
			break;
		case ESSAY_QUESTION:
			$result = _insertQuestion($data);
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
	
	$category = $data['category'];
	$type = $data['type'];
	$question = $data['question'];
	
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
	
	if ($type == MULTIPLE_CHOICE_QUESTION) {
		$result['choices'] = _decodeMultipleChoices($result['choices']);
		$result['answer'] = _decodeMultipleChoiceAnswer($result['answer']);
	}
	return $result;
}

function updateQuestion($id, $data)
{
	$type = $data['type'];
	$result = array();
	$result[] = validateQuestionData($type, 'type');
	$result[] = validateQuestionData($id, 'question_id');
	$result[] = validateQuestionData($data, null, $type);
	foreach ($result as $value) {
		if (isErrorMessage($value)) {
			return $value;
		}
	}
	
	_processQuestionData($data);
	$result = false;
	switch ($type) {
		case MULTIPLE_CHOICE_QUESTION:
			$data['choices'] = _encodeMultipleChoices($data['choices']);
			$data['answer'] = _encodeMultipleChoiceAnswer($data['answer']);
		case TRUE_OR_FALSE_QUESTION:
			//cascade intentional
		case OBJECTIVE_QUESTION:
			$result = _updateAndSyncQuestion($id, $type, $data);
			break;
		case ESSAY_QUESTION:
			$result = _updateQuestion($id, $data);
			break;
		default:
			break;
	}
	return $result;
}

function deleteQuestion($id)
{
	$result = validateQuestionData($id, 'question_id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	return deleteFromTable(QUESTIONS_TABLE, 'question_id=:id', array(':id' => $id));
}

function deleteQuestionCategory($id)
{
	$result = validateQuestionCategoryData($id, 'category_id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	return deleteFromTable(QUESTION_CATEGORY_TABLE, 'category_id=:id', array(':id' => $id));
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
	$type = isset($options['TYPE']) ? $options['TYPE'] : null;
	
	$columns = array();
	if ($type == MULTIPLE_CHOICE_QUESTION && $includePrimaryKeys) {
		$columns = array('question_id', 'answer', 'category', 'choices', 'randomize');
	} elseif ($type == MULTIPLE_CHOICE_QUESTION) {
		$columns = array('answer', 'category', 'choices', 'randomize');
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
	} elseif ($key == 'answer' || $type == TRUE_OR_FALSE_QUESTION) {
		$value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
	} elseif ($key == 'answer') {
		$value = trim($value);
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