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
		$out .= " | Passing Score: " . $examData['passing_score'];
		if ($examData['score_is_percentage']) {
			$out .= '%';
		} else {
			$out .= ' pts';
		}
		$out .= "</div><br/>";
		
		echo $out;
	}
	?>
	<form method = "post" action = "index.php">
	<input type="hidden" name="action" value="checkExam"/>
	<input type="hidden" name="examId" value="<?php echo $examData['exam_id']; ?>" />
	<input type="hidden" name="revision" value="<?php echo $examData['revision']; ?>" />
	<?php
	$questions = getExamQuestions($examData['exam_id']);
	
	if (!empty($questions)) {
		echo "<ol>";
		foreach ($questions as $value) {
			$type = $value['type'];
			echo "<li>";
			echo examQuestionHTML($type, $value);
			echo "<hr/>";
			echo "</li>";
		}
		echo "</ol>";
		echo "<input type = \"submit\" value = \"I'm Done\"/>";
	} else {
		echo "No Available Questions";
	}
	?>
	</form>
</div>