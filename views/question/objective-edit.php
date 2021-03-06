<?php if (isset($data)) { ?>
<div id = "add-question-panel">
	<span class = "panel-title">Edit Objective Question</span>
	<form method = "post" action = "admin.php">
	<input type = "hidden" name = "type" value = "<?php echo OBJECTIVE_QUESTION; ?>"/>
	<input type = "hidden" name = "question_id" value = "<?php echo $data['question_id']; ?>"/>
	<input type="hidden" name="owner" value="<?php echo $data['owner']?>"/>
	<table id = "questions-table">
		<tr>
			<td>Category</td>
			<td>
				<?php
				$owner = getLoggedInUser('id');
				echo questionCategorySelectHTML(array('selected' => $data['category']), $owner); 
				?>
			</td>
		</tr>
		<tr>
			<td>Question</td>
			<td><textarea name = "question"><?php echo $data['question']; ?></textarea></td>
		</tr>
		<tr>
			<td>Answer</td>
			<td>
				<input type = "text" name = "answer" value = "<?php echo $data['answer']; ?>"/>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><input type ="submit" value ="Edit Question"/></td>
		</tr>
	</table>
	<input type = "hidden" name = "action" value = "editQuestion"/>
	</form>
</div>
<?php } ?>