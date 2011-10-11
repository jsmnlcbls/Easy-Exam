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
		$questionId = $value['question_id'];
		$type = $value['type'];
		$data = getQuestionData($questionId, $type);
		echo "<li>";
		echo examQuestionHTML($type, $data);
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