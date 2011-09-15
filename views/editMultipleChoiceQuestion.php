<?php
	include "functions/question.php";

	$questionId = intval(filterGET("questionId", ""));
	$data = escapeOutput(getQuestionData($questionId, MULTIPLE_CHOICE_QUESTION));
?>
<div id = "edit-question-panel">
	<span class = "panel-title">Edit Multiple Choice Question</span>
	<form method = "post" action = "admin.php">
	<input type = "hidden" name = "type" value = "<?php echo MULTIPLE_CHOICE_QUESTION;?>"/>
	<input type = "hidden" name = "question_id" value = "<?php echo $data['question_id']; ?>"/>
	<table id = "questions-table">
		<tr>
			<td>Category</td>
			<td>
				<?php echo questionCategorySelectHTML(array('selected' => $data['category']));?>
			</td>
		</tr>
		<tr>
			<td>Question</td>
			<td><textarea name = "question"><?php echo $data['question']; ?></textarea></td>
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
				$out[] = '<input class =  "question-choice" type = "text" '
					   . 'value = "' . $data[$column] .'" name = "' . $column .'"/></td>';
				$out[] = '</tr>';
				$startRowTag = true;
			}
			echo implode ("\n", $out);
			?>
		<tr>
			<td>Answer</td>
			<td>
				<?php echo questionLetterAnswerSelectHTML(array('selected' => $data['answer'])); ?>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><input type ="submit" value ="Save"/></td>
		</tr>
	</table>
	<input type = "hidden" name = "action" value = "editQuestion"/>
	</form>
</div>
		