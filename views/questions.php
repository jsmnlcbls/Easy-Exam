<div id = "take-exam-panel">	
	<?php
	if (isset($reviewCategory)) {
		$categoryData = getCategoryData($reviewCategory);
		echo "<span class = \"panel-title\">";
		echo $categoryData['name'] . " Review Questions";
		echo "</span>";
	} else if (isset($examData)) {
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
	<?php
	$questions = array();
	include "functions/question.php";
	if (isset($reviewCategory) && isset($questionTypeId)) {
		echo "<input type = \"hidden\" name = \"action\" value = \"checkReviewAnswers\"/>";
		echo "<input type =\"hidden\" name = \"category\" value = \"$reviewCategory\">";
		$questions = getCategoryQuestions($reviewCategory, $questionTypeId, true);
	} else if (isset($examData) && isset($questionTypeId)) {
		echo "<input type = \"hidden\" name = \"action\" value = \"checkExamAnswers\"/>";
		echo "<input type =\"hidden\" name = \"category\" value = \"{$examData['questions_category']}\">";
		$questions = getCategoryQuestions($examData['questions_category'], $questionTypeId, true);
	}
	
	$questionNumber = 0;
	foreach ($questions as $value) {
		$question = $value['question'];
		$questionId = $value['question_id'];
		$choices = array('A' => $value['choiceA'], 
						 'B' => $value['choiceB'],
						 'C' => $value['choiceC'],
						 'D' => $value['choiceD'],
						 'E' => $value['choiceE']);

		$choicesList = "";
		for ($a = 0; $a < 5; $a++) {
			$key = array_rand($choices);
			if ("" != $choices[$key]) {
				$choicesList .= "<input type = \"radio\" name = \"{$questionId}\" value = \"$key\">";
				$choicesList .= $choices[$key] ."<br/>";
			}
			unset($choices[$key]);
		}

		$output = "";
		$output .= '<div class = "question-div">';
		$output .= '<div class = "question">';
		$output .= ++$questionNumber. ".&nbsp;" . escapeOutput($question);
		$output .= '</div>';
		$output .= '<div class = "choices">';
		$output .= $choicesList;
		$output .= '</div>';
		$output .= '</div>';
		echo $output;
	}
	if ($questionNumber > 0) {
		echo "<input type = \"submit\" value = \"Submit\"/>";
	} else {
		echo "No Available Questions";
	}
	
	?>
	</form>
</div>