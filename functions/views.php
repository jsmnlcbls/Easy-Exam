<?php

function userGroupSelectHTML($attributes = array(), $userId = 0)
{
	$attributes['name'] = isset($attributes['name']) ? $attributes['name'] : 'group_id';
	$groups = getAllUserGroups($userId);
	$input = array();
	foreach ($groups as $value) {
		$input[$value['group_id']] = $value['name'];
	}
	return _generateSelectHTML($input, $attributes);
}

function answerScoringHTML($type, $data)
{
	if ($type == ESSAY_QUESTION) {
		return essayScoringHTML($data);
	}
}

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

function examQuestionHTML($type, $data)
{
	$question = escapeOutput($data['question']);
	$input = '';
	if ($type == MULTIPLE_CHOICE_QUESTION) {
		$choices = escapeOutput($data['choices']);
		$multipleAnswers = count($data['answer']) > 1 ? true : false; 
		$input = _multipleChoiceExamAnswerInputHTML($choices, 
													$data['question_id'], 
													$multipleAnswers, 
													$data['randomize']);
	} elseif ($type == ESSAY_QUESTION) {
		$name = _generateAnswerInputName($data['question_id']);
		$input = essayQuestionAnswerInputHTML(array('name' => $name));
	} elseif ($type == TRUE_OR_FALSE_QUESTION) {
		$name = _generateAnswerInputName($data['question_id']);
		$input = trueOrFalseAnswerSelectHTML(array('name' => $name));
	} elseif ($type == OBJECTIVE_QUESTION) {
		$name = _generateAnswerInputName($data['question_id']);
		$input = objectiveQuestionAnswerInputHTML(array('name' => $name));
	} else {
		return '';
	}
	return _examQuestionTemplate($question, $input, $data['points']);
}

function multipleChoiceQuestionHTML($data)
{
	$contents = array();
	$contents['question'] = escapeOutput($data['question']);
	$choices = escapeOutput($data['choices']);
	$contents['choices'] = _generateListHTML($choices, true);
	$contents['type'] = $data['type'];
	
	$answer = array();
	foreach ($data['answer'] as $value) {
		$answer[] = $value + 1;
	}
	$contents['answer'] = implode(', ', $answer);
	
	return _questionTemplate($contents);
}

function trueOrFalseQuestionHTML($data)
{
	$contents = array();
	$contents['question'] = escapeOutput($data['question']);
	$contents['answer'] = (bool) $data['answer'] ? 'True' : 'False';
	$contents['type'] = $data['type'];
 	return _questionTemplate($contents);
}

function essayQuestionHTML($data)
{
	$contents = array();
	$contents['question'] = escapeOutput($data['question']);
	$contents['type'] = $data['type'];
	return _questionTemplate($contents);
}

function objectiveQuestionHTML($data)
{
	$contents = array();
	$contents['question'] = escapeOutput($data['question']);
	$contents['answer'] = escapeOutput($data['answer']);
	$contents['type'] = $data['type'];
	return _questionTemplate($contents);
}

function essayScoringHTML($data)
{
	$name = $data['account_id'];
	$html = '<div>' . escapeOutput($data['answer']) . '</div>'
		  . '<div>Score: <input name="scores[' . $name . ']" type="text" style="width:1em"/>'
		  . ' / ' . $data['points'] . ' pts</div>';
	return $html;
}

function questionCategorySelectHTML($attributes = array(), $owner = 0, $includeRootCategory = false)
{
	if (!isset($attributes['name'])) {
		$attributes['name'] = 'category';
	}
	$categories = getAllQuestionCategories($owner, $includeRootCategory);
	$input = array();
	foreach ($categories as $value) {
		$input[$value['category_id']] = $value['name'];
	}
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

function _multipleChoiceExamAnswerInputHTML($choices, $questionId, $multipleAnswers = false, $randomize = false)
{
	$list = '<ul class="exam-multiple-choices">';
	$options = array();
	foreach ($choices as $key => $value) {
		$name = _generateAnswerInputName($questionId, $multipleAnswers);
		$type = 'radio';
		if ($multipleAnswers) {
			$type = 'checkbox';
		}
		$options[] = "<li><input type=\"$type\" value=\"{$key}\" name=\"{$name}\"> {$value}</li>";
	}
	if ($randomize) {
		shuffle($options);
	}
	$list .= implode("\n", $options);
	$list .= '</ul>';
	return $list;
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

function _examQuestionTemplate($question, $answerInput, $points = '')
{
	if ($points != '') {
		$points = ($points > 1) ? "($points pts)" : '(1 pt)';
		$points = '<span class="question-exam-points">' . $points . '</span>';	
	}
	$output = array();
	$output[] = '<div class = "exam-question-div">';
	$output[] = "<div>{$question}{$points}</div>";
	$output[] = "<div>{$answerInput}</div>";
	$output[] = "</div>";
	return implode("\n", $output);
}

function _generateSelectHTML($input, $attributes = array())
{
	$name = isset($attributes['name']) ? $attributes['name'] : '';
	$id = isset($attributes['id']) ? " id = \"{$attributes['id']}\" " : '';
	$class = isset($attributes['class']) ? " class = \"{$attributes['class']}\" ": '';
	$selected = isset($attributes['selected']) ? $attributes['selected'] : null;
	$blankOption = isset($attributes['blankOption']) ? $attributes['blankOption'] : true;
	
	$out = array();
	$out[] = "<select name = \"{$name}\"{$id}{$class}>";
	if ($selected === null && !$blankOption) {
		$out[] = "<option selected = \"selected\" value = \"\"></option>";
	} elseif ($blankOption) {
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

function _generateAnswerInputName($questionId, $multipleAnswers = false)
{
	if ($multipleAnswers) {
		return "{$questionId}[]";
	}
	return "{$questionId}";
}