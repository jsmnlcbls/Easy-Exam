
<div id = "select-exam-panel">
	<span class = "panel-title">Select Exam To Modify</span>
	<form method = "get" action = "admin.php" id = "select-exam-form">
		<table>
			<tr>
				<td>Exam Name</td>
				<td>
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
				</td>
			</tr>
			<tr>
				<td>Options</td>
				<td>
					<input checked="checked" type="radio" name="view" value="editExamProperties"/>Edit Exam Properties<br/>
					<input type="radio" name="view" value="editExamQuestions"/>Edit Questions
				</td>
			</tr>
			<tr>
				<td></td>
				<td><button>Edit</button></td>
			</tr>
		</table>
	</form>
</div>
		
