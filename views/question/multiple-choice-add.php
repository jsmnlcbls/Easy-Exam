<div id = "add-question-panel">
	<span class = "panel-title">Add Multiple Choice Question</span>
	<form method = "post" action = "admin.php">
	<input type = "hidden" name = "type" value = "<?php echo MULTIPLE_CHOICE_QUESTION;?>"/>
	<table id = "questions-table">
		<tr>
			<td>Category</td>
			<td>
				<?php echo questionCategorySelectHTML(); ?>
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
		