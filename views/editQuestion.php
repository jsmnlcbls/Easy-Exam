<?php
	include "functions/question.php";

	$view = filterGET("view");
	$questionId = intval(filterGET("questionId", ""));
	$examId = intval(filterGET("examId"));
	$data = escapeOutput(getQuestionData($questionId));
	$questionType = $data['type'];
?>
<div id = "edit-question-panel">
	<span class = "panel-title">
	<?php
		if ($view == "editQuestion") {
			echo "Modify Question";
		} else {
			echo "Modify Exam Question";
		}
	?>
	</span>
	<form method = "post" action = "admin.php">
	<input type ="hidden" name ="questionId" value ="<?php echo $questionId?>"/>
	<input type ="hidden" name ="action" value="editQuestion">
	<table id = "questions-table">
		<?php
		if ($view == "editExamQuestion") {
			echo "<input type =\"hidden\" name =\"questionType\" value=\"e\">";
			echo "<input type =\"hidden\" name =\"category\" value=\"{$data['category']}\">";
			echo "<input type =\"hidden\" name =\"redirect\" value=\"r\">";
			echo "<input type =\"hidden\" name =\"examId\" value=\"$examId\">";
			 
		} else if ($view == "editQuestion") {
		?>	
		<tr>
			<td>Type</td>
			<td>
				<?php
				function markIfSelectedType($questionType) {
					global $questionType;
					if ($questionType == $questionType) {
						echo "selected = \"selected\"";
					}
				}
				?>
				<select name = "questionType">
					<option value = "" <?php markIfSelectedType("");?>>Unassigned</option>
					<option value = "r" <?php markIfSelectedType("r");?>>Review Question</option>
					<option value = "e" <?php markIfSelectedType("e");?>>Exam Question</option>
				</select>
				
			</td>
		</tr>
		<tr>
			<td>Category</td>
			<td>
				<select name = "category">
				<?php
					$categories = getAllCategories();
					foreach ($categories as $value) {
						$categoryId = $value['category_id'];
						$categoryName = $value['name'];
						if ($data['category'] == $categoryId){
							echo "<option selected=\"selected\" value = \"{$categoryId}\">{$categoryName}</option>";
						} else {
							echo "<option value = \"{$categoryId}\">{$categoryName}</option>";
						}
					}
				?>
				</select>
			</td>
		</tr>
		<?php
		}
		?>
		<tr>
			<td>Question</td>
			<td><textarea name = "question"><?php echo $data['question'];?></textarea></td>
		</tr>
		<tr>
			<td rowspan = "5">Choices</td>
			<td><span class="letterChoice">A</span>
				<input value ="<?php echo $data['choiceA'];?>" class = "question-choice" type = "text" name = "choices[]"></td>
		</tr>
		<tr>
			<td><span class="letterChoice">B</span>
				<input value ="<?php echo $data['choiceB'];?>" class = "question-choice" type = "text" name = "choices[]"></td>
		</tr>
		<tr>
			<td><span class="letterChoice">C</span>
				<input value ="<?php echo $data['choiceC'];?>" class = "question-choice" type = "text" name = "choices[]"></td>
		</tr>
		<tr>
			<td><span class="letterChoice">D</span>
				<input value ="<?php echo $data['choiceD'];?>" class = "question-choice" type = "text" name = "choices[]"></td>
		</tr>
		<tr>
			<td><span class="letterChoice">E</span>
				<input value ="<?php echo $data['choiceE'];?>" class = "question-choice" type = "text" name = "choices[]"></td>
		</tr>
		<tr>
			<td>Answer</td>
			<td>
				<select name = "answer">
					<?php
					foreach (range('A', 'E') as $value) {
						if ($value == $data['answer']) {
							echo "<option selected=\"selected\" value=\"$value\">$value</option>";
						} else {
							echo "<option value=\"$value\">$value</option>";
						}
					}
					?>
				</select></td>
		</tr>
		<tr>
			<td></td>
			<td><input type ="submit" value ="Update"/></td>
		</tr>
	</table>
	</form>
</div>
		