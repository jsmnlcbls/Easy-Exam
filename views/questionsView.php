<div id = "take-exam-panel">
	<form method = "post" action = "index.php">
	<input type = "hidden" name = "action" value = "checkReviewAnswers"/>
	<?php
	if (isset($reviewCategory)) {
		echo "<input type =\"hidden\" name = \"category\" value = \"$reviewCategory\">";

		include "functions/question.php";
		$questions = getCategoryQuestions($reviewCategory, "r", true);

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
		}
	}
	?>
	</form>
</div>