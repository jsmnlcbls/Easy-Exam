<?php if (isset($examData)) { ?>
<div id = "take-exam-panel">
	<?php
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
		$out .= " | Total Questions: " . $examData['total_questions'];
		$out .= "</div><br/>";
		
		echo $out;
	?>
	<form method = "post" action = "index.php">
	<input type="hidden" name="action" value="endExam"/>
	<input type="hidden" name="exam_id" value="<?php echo $examData['exam_id']; ?>" />
	<input type="hidden" name="revision" value="<?php echo $examData['revision']; ?>" />
	<?php
	$questions = getExamQuestions($examData['exam_id'], $examData['revision'], true);
	$displayed = 0;
	if (!empty($questions)) {
		echo "<ol>";
		foreach ($questions as $value) {
			$type = $value['type'];
			echo "<li>";
			echo examQuestionHTML($type, $value);
			echo "<hr/>";
			echo "</li>";
			$displayed++;
		}
		echo "</ol>";
		echo "<input type = \"submit\" value = \"I'm Done\"/>";
	} else {
		echo "No Available Questions";
	}
	?>
	</form>
</div>
<?php } ?>