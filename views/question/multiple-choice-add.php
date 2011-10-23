<div id = "add-question-panel">
	<span class = "panel-title">Add Multiple Choice Question</span>
	<form method = "post" action = "admin.php">
	<input type = "hidden" name = "type" value = "<?php echo MULTIPLE_CHOICE_QUESTION;?>"/>
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
			<td>Choices</td>
			<td>
				<ul id="multiple-choices-list">
				<?php
				$out = array();
				for ($a = 0; $a < 5; $a++) {
					$out[] = '<li>';
					$out[] = "<input class=\"question-choice\" type=\"text\" name =\"choices[]\"/>";
					$out[] = "<input type=\"checkbox\" name=\"answer[]\" value=\"{$a}\"/>";
					$out[] = '</li>';
				}
				echo implode("\n", $out);
				?>
				</ul>
			</td>
		<tr>
			<td>Option</td>
			<td>
				<input type ="checkbox" name="randomize" value="1" checked="checked"/>
				Randomize order of choices at exam
			</td>
		</tr>
		<tr>
			<td colspan="2"><hr/></td>
		</tr>
		<tr>
			<td></td>
			<td><input type ="submit" value ="Add Question"/></td>
		</tr>
		<tr>
			<td colspan="2" class="note">
				<br/>
				To mark a choice as an answer, select the checkbox beside it.<br/>
				One or more choices may be marked as the answer.
			</td>
		</tr>
	</table>
	<input type = "hidden" name = "action" value = "addQuestion"/>
	</form>
</div>