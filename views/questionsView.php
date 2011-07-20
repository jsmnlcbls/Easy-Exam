<div id = "take-exam-panel">
	<form method = "post" action = "results.php">
	<?php
		if (isset($_GET['category'])) {
			$category = intval($_GET['category']);
			echo "<input type =\"hidden\" name = \"category\" value = \"$category\">";
			$questions = getQuestions($category, 10);

			$questionNumber = 1;
			foreach ($questions as $value) {
				$question = $value['question'];
				$questionId = $value['question_id'];
				$answer = $value['answer'];
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
				$output .= $questionNumber. ".&nbsp;" . $question;
				$output .= '</div>';
				$output .= '<div class = "choices">';
				$output .= $choicesList;
				$output .= '</div>';
				$output .= '</div>';
				echo $output;
				$questionNumber++;
			}
		}
	?>
	<input type = "hidden" name = "action" value = "getResults"/>
	<input type = "submit" name = "" value = "Submit"/>
	</form>
</div>