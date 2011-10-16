
<div id = "select-exam-panel">
	<span class = "panel-title">Select Exam To Modify</span>
	<form method = "get" action = "admin.php" id = "select-exam-form">
		<input type="hidden" name="view" value="exam-edit-properties"/>
		<select name = "examId">
			<option value = "">None Selected</option>
			<?php
				include "functions/exam.php";
				$exams = getAllExams();
				foreach ($exams as $value) {
					$name = escapeOutput($value['name']);
					echo "<option value = \"{$value['exam_id']}\">{$name}</option>";
				}
			?>
		</select>
		<button>Next</button>
	</form>
</div>
		
