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

function addQuestionCategory($data)
{
	$sql = "INSERT INTO category (name, parent_category) VALUES (:name, :parent);";
	$parameters = array(':name' => $data['name'], ':parent' => $data['parent']);
	return executeDatabase($sql, $parameters);
}

function editQuestionCategory($id, $data)
{
	$sql = "UPDATE category SET name=:name, parent_category=:parentCategory WHERE category_id=:id;";
	$parameters = array(':name' => $data['name'], 
						':parentCategory' => $data['parent'],
						':id' => $id);
	return executeDatabase($sql, $parameters);
}

function getCategoryQuestions($category, $includeSubcategories = true)
{
	$category = sanitizeQuestionData($category, 'category');
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
	$category = sanitizeQuestionData($category, 'category');
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
	$category = sanitizeQuestionData($category, 'category');
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
	$category = sanitizeQuestionData($category, 'category');
	$sql = "SELECT * FROM questions WHERE category = :category";
	$parameters = array(':category' => $category);
	return queryDatabase($sql, $parameters);
}

function addQuestion($type, $rawData)
{
	$type = sanitizeQuestionData($type, 'type');
	$questionData = sanitizeQuestionData($rawData);
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
	$category = $data['category'];
	$question = $data['question'];
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
	$id = sanitizeQuestionData($id, 'question_id');
	if ($type != null) {
		$type = sanitizeQuestionData($type, 'type');
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
	$id = sanitizeQuestionData($id, 'question_id');
	$type = sanitizeQuestionData($rawData['type'], 'type');
	$questionData = sanitizeQuestionData($rawData);
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
	$id = sanitizeQuestionData($id, 'question_id');
	$sql = "DELETE FROM questions WHERE question_id=:id";
	$parameters = array(':id' => $id);
	return executeDatabase($sql, $parameters);
}

function getQuestionTableColumns($options = array())
{
	$includePrimaryKeys = false;
	if (isset($options['INCLUDE_PRIMARY_KEYS']) && 
			  $options['INCLUDE_PRIMARY_KEYS']) {
		$includePrimaryKeys = true;
	}
	$type = isset($options['TYPE']) ? sanitizeQuestionData($options['TYPE'], 'type') : null;
	
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

function getSecondaryQuestionTables()
{
	return array(MULTIPLE_CHOICE_QUESTION => 'multiple_choice',
				 TRUE_OR_FALSE_QUESTION => 'true_or_false',
				 OBJECTIVE_QUESTION => 'objective'
				);
}

function getSecondaryQuestionTableName($type)
{
	$tables = getSecondaryQuestionTables();
	if (isset($tables[$type])) {
		return $tables[$type];
	}
	return null;
}

function _insertAndSyncQuestion($type, $data)
{
	$secondaryTable = getSecondaryQuestionTableName($type);
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
	return insertIntoTable('questions', $data);
}

function _updateAndSyncQuestion($id, $type, $data)
{
	$secondaryTable = getSecondaryQuestionTableName($type);
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
	return updateTable('questions', $data, $condition, $conditionParameters);
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