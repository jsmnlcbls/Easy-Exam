<div id = "add-question-panel">
	<span class = "panel-title">Add A Question To Database</span>
	<form method = "post" action = "admin.php">
	<table id = "questions-table">
		<tr>
			<td>Type</td>
			<td>
				<select name = "type">
					<?php
					$questionTypes = getAllQuestionTypes();
					foreach ($questionTypes as $type) {
						echo "<option value = \"{$type['id']}\">{$type['name']}</option>";
					}
					?>
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
			<?php
			$startRowTag = false;
			$out = array();
			foreach (getChoicesLetterColumns() as $letter => $column) {
				if ($startRowTag) {
					$out[] = '<tr>'; 
				}
				$out[] = '<td><span class = "letterChoice">' . $letter . '</span>';
				$out[] = '<input class =  "question-choice" type = "text" name = "' . $column .'"/></td>';
				$out[] = '</tr>';
				$startRowTag = true;
			}
			echo implode ("\n", $out);
			?>
		<tr>
			<td>Answer</td>
			<td>
				<select name = "answer">
					<option value = ""></option>
					<?php
					foreach (getChoicesLetterColumns() as $letter => $value) {
						echo "<option value = \"$letter\">$letter</option>";
					}
					?>
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
		