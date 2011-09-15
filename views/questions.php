<div id = "take-exam-panel">	
	<?php
	if (isset($examData)) {
		$out = "<span class = \"panel-title\">". $examData['name'] . "</span>";
		$out .= "<div>";
		$timeLimit = $examData['time_limit'];
		$out .= "Time Limit: $timeLimit hour";
		if ($timeLimit > 1) {
			$out .= "s";
		}
		$out .= " | Passing Score: " . $examData['passing_score'] . "%"; 
		$out .= "</div><br/>";
		
		echo $out;
	}
	?>
	<form method = "post" action = "index.php">
	<input type = "hidden" name = "action" value = "checkExam"/>
	<?php
	$questions = array();
	include "functions/question.php";
	
	echo "<input type =\"hidden\" name = \"category\" value = \"{$examData['questions_category']}\">";
	$questions = getCategoryQuestions($examData['questions_category'], true);
	
	$questionNumber = 0;
	echo "<ol>";
	foreach ($questions as $value) {
		$question = $value['question'];
		$questionId = $value['question_id'];
		$type = $value['type'];
		$data = getQuestionData($questionId, $type);
		unset($data['type']);
		unset($data['answer']);
		echo "<li>";
		$attributes = array('name' => $questionId);
		if ($type == MULTIPLE_CHOICE_QUESTION) {
			echo multipleChoiceQuestionHTML($data);
			echo 'Answer: ' . multipleChoiceAnswerSelectHTML($attributes);
		} elseif ($type == ESSAY_QUESTION) {
			echo essayQuestionHTML($data);
			echo 'Answer: ' . essayQuestionAnswerInputHTML($attributes);
		} elseif ($type == OBJECTIVE_QUESTION) {
			echo objectiveQuestionHTML($data);
			echo 'Answer: ' . objectiveQuestionAnswerInputHTML($attributes);
		} elseif ($type == TRUE_OR_FALSE_QUESTION) {
			echo trueOrFalseQuestionHTML($data);
			echo 'Answer: ' . trueOrFalseAnswerSelectHTML(array('name' => $questionId, 'selected' => null));
		}
		echo "<hr/>";
		echo "</li>";
		$questionNumber++;
	}
	echo "</ol>";
	if ($questionNumber > 0) {
		echo "<input type = \"submit\" value = \"I'm Done\"/>";
	} else {
		echo "No Available Questions";
	}
	
	?>
	</form>
</div>