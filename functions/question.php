<?php

function getCategoryQuestions($category, $questionType, $includeSubcategories = true)
{
	$questions = getQuestions($category, $questionType);
	
	if ($includeSubcategories) {
		$subCategories = getSubCategories($category);
		if (count($subCategories) > 0) {
			foreach ($subCategories as $value) {
				$questions += getQuestions($value, $questionType);
			}
		}
	}
	return $questions;
}

function checkAnswersToQuestions($category, $userAnswers, $questionType)
{
	$answers = getAnswersToQuestions($category, $questionType);
	$total = count($answers);
	$correctAnswers = 0;
	foreach ($userAnswers as $key => $value) {
		if (isset($answers[$key]) && $answers[$key] == $value) {
			$correctAnswers++;
		}
	}
	return (float) ($correctAnswers/$total) * 100;
}

function getAnswersToQuestions($category, $questionType)
{
	$database = getDatabase();
	$sql = "SELECT question_id, answer FROM questions WHERE category = :category AND type = :questionType";
	$statement = $database->prepare($sql);
	$statement->bindValue(':category', $category);
	$statement->bindValue(':questionType', $questionType);
	
	$result = @$statement->execute();
	$answers = array();
	if ($result !== false) {
		while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
			$answers[$row['question_id']] = $row['answer'];
		}
		return $answers;
	}
	return false;
}

function getQuestions($category, $type)
{
	$sql = "SELECT * FROM questions WHERE category = :category AND type = :type";
	$parameters = array(':category' => $category, ':type' => $type);
	return queryDatabase($sql, $parameters);
}

function addQuestion($data)
{
	$parameterChoices = array();
	foreach (getChoicesLetterColumns() as $columnName) {
		$parameterChoices[":{$columnName}"] = $data[$columnName];
	}
	
	$columns = "question, answer, category, type, " . implode(", ", getChoicesLetterColumns());
	$values = ":question, :answer, :category, :type, " . implode (", ", array_keys($parameterChoices));
	
	$sql = "INSERT INTO questions ($columns) VALUES ($values)";
	$parameters = array(':question' => $data['question'],
						':answer' => $data['answer'],
						':category' => $data['category'],
						':type' => $data['type']);
	$parameters += $parameterChoices;
	return executeDatabase($sql, $parameters);
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
	
	$sql = "SELECT question_id, question FROM questions WHERE $sqlCondition";
	return queryDatabase($sql, $parameters);
}

function getQuestionData($id)
{
	$sql = "SELECT * FROM questions WHERE question_id=:questionId";
	$parameters = array(':questionId' => $id);
	$result = queryDatabase($sql, $parameters);
	return array_shift($result);
}

function updateQuestion($id, $data)
{
	$columnValues = array();
	$parameters = array();
	
	$columnValues[] = "question=:question";
	$columnValues[] = "type=:type";
	$columnValues[] = "category=:category";
	$columnValues[] = "answer=:answer";
	
	$parameters[':question'] = $data['question'];
	$parameters[':type'] = $data['type'];
	$parameters[':category'] = $data['category'];
	$parameters[':answer'] = $data['answer'];
	$parameters[':id'] = $id;
	
	foreach (getChoicesLetterColumns() as $columnName) {
		$columnValues[] = "{$columnName}=:{$columnName}";
		if (isset($data[$columnName])) {
			$parameters[":{$columnName}"] = $data[$columnName];
		} else {
			$parameters[":{$columnName}"] = "";
		}
	}
	$columnValuesSql = implode(", ", $columnValues);
	
	$sql = "UPDATE questions SET $columnValuesSql WHERE question_id=:id";
	return executeDatabase($sql, $parameters);
}

function deleteQuestion($id)
{
	$sql = "DELETE FROM questions WHERE question_id=:id";
	$parameters = array(':id' => $id);
	return executeDatabase($sql, $parameters);
}