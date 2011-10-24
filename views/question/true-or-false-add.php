<div id = "add-question-panel">
	<span class = "panel-title">Add True Or False Question</span>
	<form method = "post" action = "admin.php">
	<input type = "hidden" name = "type" value = "<?php echo TRUE_OR_FALSE_QUESTION; ?>"/>
	<table id = "questions-table">
		<tr>
			<td>Category</td>
			<td>
				<?php echo questionCategorySelectHTML(array(), getLoggedInUser('id')); ?>
			</td>
		</tr>
		<tr>
			<td>Question</td>
			<td><textarea name = "question"></textarea></td>
		</tr>
		<tr>
			<td>Answer</td>
			<td>
				<?php echo trueOrFalseAnswerSelectHTML(array('selected' => null)); ?>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><input type ="submit" value ="Add Question"/></td>
		</tr>
	</table>
	<input type = "hidden" name = "action" value = "addQuestion"/>
	</form>
</div>