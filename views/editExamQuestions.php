<?php
$examId = intval(filterGET("examId"));
$data = getExamData($examId);
$examName = $data['name'];
?>
<div id = "edit-category-panel">
	<span class = "panel-title">Edit Questions Of Exam: <em><?php echo $examName;?></em></span>
	<form method = "post" action = "admin.php" id = "add-category-form">
		<input type = "hidden" name = "action" value = "editCategory" />
		<?php
		include "functions/exam.php";
		
		$questions = getExamQuestions($examId);
		$count = 1;
		foreach ($questions as $value) {
			$out = "<div class=\"question-div\">";
			$out .= "<div class=\"question\">$count. {$value['question']}</div>";
			foreach (range('A', 'E') as $letter) {
				$key = "choice$letter";
				if ($value[$key] != "") {
					$out .= "<div class=\"choices\">$letter. {$value[$key]}</div>";
				}
			}
			$parameters = array('view' => 'editExamQuestion', 'questionId' => $value['question_id'],
								'examId' => $examId);
			$editLink = http_build_query($parameters);
			$out .= "<div class=\"question-options\"><a href=\"?$editLink\">Edit</a> | Remove</div>";
			$out .= "</div>";
			echo $out;
			$count++;
		}
		?>
	</form>
</div>
		