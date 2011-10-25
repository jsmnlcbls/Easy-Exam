<div id = "select-exam-panel">
	<span class = "panel-title">Select Exam To Modify</span>
	<form method = "get" action = "admin.php" id = "select-exam-form">
		<input type="hidden" name="view" value="exam-edit-properties"/>
		<select name = "exam-id">
			<option value = ""></option>
			<?php
				include "functions/exam.php";
				$exams = getAllExams(getLoggedInUser('id'));
				foreach ($exams as $value) {
					$id = $value['exam_id'];
					$name = escapeOutput($value['name']);
					echo "<option value = \"{$id}\">{$name}</option>";
				}
			?>
		</select>
		<button>Next</button>
	</form>
</div>
		
