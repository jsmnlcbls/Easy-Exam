
<div id = "select-exam-panel">
	<span class = "panel-title">Delete An Exam</span>
	<form method = "post" action = "admin.php" id = "select-exam-form">
		<input type = "hidden" name = "action" value = "deleteExam"/>
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
				<td colspan = "2">
				<span style ="color:RED; font-style: italic; font-size: 80%">
					Warning: Delete is irrevocable. <br/> Proceed only if you are sure.
				</span>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><button>Delete</button></td>
			</tr>
		</table>
	</form>
</div>
		
