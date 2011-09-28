<?php

function questionHTML($type, $data)
{
	if ($type == MULTIPLE_CHOICE_QUESTION) {
		return multipleChoiceQuestionHTML($data);
	} elseif ($type == ESSAY_QUESTION) {
		return essayQuestionHTML($data);
	} elseif ($type == TRUE_OR_FALSE_QUESTION) {
		return trueOrFalseQuestionHTML($data);
	} elseif ($type == OBJECTIVE_QUESTION) {
		return objectiveQuestionHTML($data);
	}
}

function multipleChoiceQuestionHTML($data)
{
	$contents = array();
	$letterColumns = getChoicesLetterColumns();
	$choices = getArrayValues($data, $letterColumns);
	$contents['question'] = isset($data['question']) ? escapeOutput($data['question']) : '';
	if (!empty($choices)) {
		$choices = escapeOutput($choices);
		$contents['choices'] = _generateListHTML($choices, true, array('class' => 'choices-list'));
	}
	if (isset($data['type'])) {
		$contents['type'] = $data['type'];
	}
	if (isset($data['answer'])) {
		$letter = $data['answer'];
		$answer = escapeOutput($choices[$letterColumns[$letter]]);
		$contents['answer'] = '(' . $letter . ') ' . $answer;
	}
	return _questionTemplate($contents);
}

function trueOrFalseQuestionHTML($data)
{
	$contents = array();
	$contents['question'] = escapeOutput($data['question']);
	if (isset($data['answer'])) {
		$data['answer'] = (bool) $data['answer'] ? 'True' : 'False';
	}
 	return _questionTemplate($data);
}

function essayQuestionHTML($data)
{
	$contents = array();
	$contents['question'] = escapeOutput($data['question']);
	return _questionTemplate($contents);
}

function objectiveQuestionHTML($data)
{
	$contents = array();
	$contents['question'] = escapeOutput($data['question']);
	if (isset($data['answer'])) {
		$contents['answer'] = escapeOutput($data['answer']);
	}
	return _questionTemplate($contents);
}

function questionCategorySelectHTML($attributes = array())
{
	if (!isset($attributes['name'])) {
		$attributes['name'] = 'category';
	}
	$categories = getAllQuestionCategories();
	$input = array();
	foreach ($categories as $value) {
		if (!empty($value['name'])) {
			$input[$value['category_id']] = $value['name'];
		}
	}
	return _generateSelectHTML($input, $attributes);
}

function questionLetterAnswerSelectHTML($attributes = array())
{
	if (!isset($attributes['name'])) {
		$attributes['name'] = 'answer';
	}
	$letters = array_keys(getChoicesLetterColumns());
	$input = array_combine($letters, $letters);
	return _generateSelectHTML($input, $attributes);
}

function trueOrFalseAnswerSelectHTML($attributes = array()) 
{
	$input = array(1 => 'True', 0 => 'False');
	if (!isset($attributes['name'])) {
		$attributes['name'] = 'answer';
	}
	return _generateSelectHTML($input, $attributes);
}

function essayQuestionAnswerInputHTML($attributes = array())
{
	$name = isset($attributes['name']) ? " name = \"{$attributes['name']}\" " : '';
	$out = "<textarea{$name}></textarea>";
	return $out;
}

function objectiveQuestionAnswerInputHTML($attributes = array())
{
	$name = isset($attributes['name']) ? "name = \"{$attributes['name']}\" " : '';
	$input = "<input type = \"text\" {$name}/>";
	return $input;
}

function multipleChoiceAnswerSelectHTML($attributes = array())
{
	$letters = array_keys(getChoicesLetterColumns());
	$choices = array_combine($letters, $letters);
	return _generateSelectHTML($choices, $attributes);
}

function _questionTemplate($contents)
{
	$typeMarkup = "";
	$output = array();
	$output[] = '<div class = "question-div">';
	if (isset($contents['type'])) {
		$typeMarkup = '[<em>' . _getQuestionTypeAbbreviation($contents['type']) . '</em>]';
	}
	if (isset($contents['question'])) {
		$output[] = "<div>{$typeMarkup} {$contents['question']}</div>";
	}
	if (isset($contents['choices'])) {
		$output[] = "<div>{$contents['choices']}</div>";
	}
	if (isset($contents['answer'])) {
		$output[] = "<div class = \"question-answer\">Answer: {$contents['answer']}</div>";
	}
	$output[] = "</div>";
	return implode("\n", $output);
}

function _generateSelectHTML($input, $attributes = array())
{
	$name = isset($attributes['name']) ? $attributes['name'] : '';
	$selected = isset($attributes['selected']) ? $attributes['selected'] : null;
	$out = array();
	$out[] = "<select name = \"{$name}\">";
	if ($selected === null) {
		$out[] = "<option selected = \"selected\" value = \"\"></option>";
	} else {
		$out[] = "<option value = \"\"></option>";
	}
	foreach ($input as $key => $value) {
		if ($selected == $key && null !==$selected) {
			$out[] = "<option selected = \"selected\" value = \"{$key}\">{$value}</option>";
		} else {
			$out[] = "<option value = \"{$key}\">{$value}</option>";
		}
	}
	$out[] = "</select>";
	return implode("", $out);
}

function _generateListHTML($input, $ordered = false, $attributes = array())
{
	$listClass = isset($attributes['class']) ? " class = {$attributes['class']} " : "";
	$listStartMarkup = ($ordered) ? "<ol{$listClass}>" : "<ul{$listClass}>";
	$listEndMarkup = ($ordered) ? "</ol>" : "</ul>";

	$out = array();
	$out[] = $listStartMarkup;
	foreach ($input as $value) {
		$value = escapeOutput($value);
		$out[] = "<li>$value</li>";
	}
	$out[] = $listEndMarkup;
	return implode("\n", $out);
}

function _getQuestionTypeAbbreviation($type)
{
	switch ($type) {
		case MULTIPLE_CHOICE_QUESTION: 
			return 'MC';
		case ESSAY_QUESTION:
			return 'E';
		case OBJECTIVE_QUESTION:
			return 'O';
		case TRUE_OR_FALSE_QUESTION:
			return 'TF';
		default:
			return '';
	}
}