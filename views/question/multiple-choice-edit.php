<?php
	include "functions/question.php";

	$questionId = getUrlQuery("questionId");
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
			<td>Choices</td>
			<td>
				<ul id="multiple-choices-list">
				<?php
				$out = array();
				for ($a = 0; $a < 5; $a++) {
					$value = isset($data['choices'][$a]) ? $data['choices'][$a] : '';
					$checked = ' ';
					if (in_array($a, $data['answer'])) {
						$checked = ' checked="checked" ';
					}
					$out[] = '<li>';
					$out[] = "<input value=\"$value\" class=\"question-choice\" type=\"text\" name =\"choices[]\"/>";
					$out[] = "<input{$checked}type=\"checkbox\" name=\"answer[]\" value=\"{$a}\"/>";
					$out[] = '</li>';
				}
				echo implode("\n", $out);
				?>
				</ul>
			</td>
		</tr>
		<tr>
			<td>Option</td>
			<td>
				<input type ="checkbox" name="randomize" value="1" 
				<?php echo $data['randomize'] ? 'checked="checked"' : '';?>/>
				Randomize order of choices at exam
				</div>
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