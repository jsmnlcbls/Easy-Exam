<div id = "add-question-panel">
	<span class = "panel-title">Add A Question To Database</span>
	<form method = "post" action = "admin.php">
	<table id = "questions-table">
		<tr>
			<td>Type</td>
			<td>
				<select name = "questionType">
					<option value = "">None Selected</option>
					<option value = "r">Review Question</option>
					<option value = "e">Exam Question</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Category</td>
			<td>
				<select name = "category">
				<?php
					$categories = getAllCategories();
					foreach ($categories as $category) {
						if ($category['category_id'] == 0) {
							echo '<option value = "0">None Selected</option>';
						} else {
							echo "<option value = \"{$category['category_id']}\">{$category['name']}</option>";
						}
					}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Question</td>
			<td><textarea name = "question"></textarea></td>
		</tr>
		<tr>
			<td rowspan = "5">Choices</td>
			<td><span class="letterChoice">A</span><input class = "question-choice" type = "text" name = "choices[]"></td>
		</tr>
		<tr>
			<td><span class="letterChoice">B</span><input class = "question-choice" type = "text" name = "choices[]"></td>
		</tr>
		<tr>
			<td><span class="letterChoice">C</span><input class = "question-choice" type = "text" name = "choices[]"></td>
		</tr>
		<tr>
			<td><span class="letterChoice">D</span><input class = "question-choice" type = "text" name = "choices[]"></td>
		</tr>
		<tr>
			<td><span class="letterChoice">E</span><input class = "question-choice" type = "text" name = "choices[]"></td>
		</tr>
		<tr>
			<td>Answer</td>
			<td>
				<select name = "answer">
					<option value = ""></option>
					<option value = "A">A</option>
					<option value = "B">B</option>
					<option value = "C">C</option>
					<option value = "D">D</option>
					<option value = "E">E</option>
				</select></td>
		</tr>
		<tr>
			<td></td>
			<td><input type ="submit" value ="Add Question"/></td>
		</tr>
	</table>
	<input type = "hidden" name = "action" value = "addQuestion"/>
	</form>
</div>
		