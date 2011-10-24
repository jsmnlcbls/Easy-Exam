<div id = "add-question-panel">
	<span class = "panel-title">Add Essay Question</span>
	<form method = "post" action = "admin.php">
	<input type = "hidden" name = "type" value = "<?php echo ESSAY_QUESTION;?>"/>
	<input type = "hidden" name = "action" value = "addQuestion"/>
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
			<td></td>
			<td><input type ="submit" value ="Add Question"/></td>
		</tr>
	</table>
	</form>
</div>