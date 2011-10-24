<div id = "add-question-panel">
	<span class = "panel-title">Add Objective Question</span>
	<form method = "post" action = "admin.php">
	<input type = "hidden" name = "type" value = "<?php echo OBJECTIVE_QUESTION; ?>"/>
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
				<input type = "text" name = "answer"/>
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