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
			echo "<input type =\"hidden\" name =\"type\" value=\"$questionType\">";
			echo "<input type =\"hidden\" name =\"category\" value=\"{$data['category']}\">";
			echo "<input type =\"hidden\" name =\"redirect\" value=\"r\">";
			echo "<input type =\"hidden\" name =\"examId\" value=\"$examId\">";
			 
		} else if ($view == "editQuestion") {
		?>	
		<tr>
			<td>Type</td>
			<td>
				<select name = "type">
				<?php
				$questionTypes = getAllQuestionTypes();
				foreach ($questionTypes as $value) {
					$name = escapeOutput($value['name']);
					$id = intval($value['id']);
					if ($id == $questionType) {
						echo "<option selected = \"selected\" value = \"{$id}\">{$name}</option>";	
					} else {
						echo "<option value = \"{$id}\">{$name}</option>";
					}
				}
				?>
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
			<?php
			$startRowTag = false;
			$out = array();
			foreach (getChoicesLetterColumns() as $letter => $column) {
				if ($startRowTag) {
					$out[] = '<tr>'; 
				}
				$out[] = '<td><span class = "letterChoice">' . $letter . '</span>';
				$out[] = '<input class =  "question-choice" type = "text" name = "' . $column .'" value = "'. $data[$column].'"/></td>';
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
						if ($letter == $data['answer']) {
							echo "<option selected=\"selected\" value=\"$letter\">$letter</option>";
						} else {
							echo "<option value=\"$letter\">$letter</option>";
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
		