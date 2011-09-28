<?php
	include "functions/question.php";

	$questionId = getUrlQuery("questionId");
	$data = escapeOutput(getQuestionData($questionId, TRUE_OR_FALSE_QUESTION));
?>
<div id = "add-question-panel">
	<span class = "panel-title">Edit True Or False Question</span>
	<form method = "post" action = "admin.php">
	<input type = "hidden" name = "type" value = "<?php echo TRUE_OR_FALSE_QUESTION; ?>"/>
	<input type = "hidden" name = "question_id" value = "<?php echo $data['question_id']; ?>"/>
	<table id = "questions-table">
		<tr>
			<td>Category</td>
			<td>
				<?php echo questionCategorySelectHTML(array('selected' => $data['category'])); ?>
			</td>
		</tr>
		<tr>
			<td>Question</td>
			<td><textarea name = "question"><?php echo $data['question']; ?></textarea></td>
		</tr>
		<tr>
			<td>Answer</td>
			<td>
				<?php echo trueOrFalseAnswerSelectHTML(array('selected' => $data['answer']));?>
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