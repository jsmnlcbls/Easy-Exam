
<div id = "select-exam-panel">
	<span class = "panel-title">Select Exam To Modify</span>
	<form method = "get" action = "admin.php" id = "select-exam-form">
		<input type = "hidden" name = "view" value = "editExam"/>
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
							echo "<option value = \"{$value['exam_id']}\">{$value['name']}</option>";
						}
					?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Options</td>
				<td>
					<input checked="checked" type="radio" name="examView" value="properties"/>Edit Exam Properties<br/>
					<input type="radio" name="examView" value="questions"/>Edit Questions
				</td>
			</tr>
			<tr>
				<td></td>
				<td><button>Edit</button></td>
			</tr>
		</table>
	</form>
</div>
		
