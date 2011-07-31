<?php
include "functions/question.php";

$questionId = intval(filterGet("questionId"));
$data = getQuestionData($questionId);
?>
<div id = "delete-question-panel">
	<span class = "panel-title">Confirm Question Removal</span>
	<form method = "post" action = "admin.php">
	<input type ="hidden" name="questionId" value="<?php echo $questionId;?>">
	<table id = "questions-table">
		<tr>
			<td>Type</td>
			<td>
				<strong>
				<?php
				$type = $data['type'];
				if ($type == "r") {
					echo "Review Question";
				} else if ($type == "e") {
					echo "Exam Question";
				}
				?>
				</strong>
			</td>
		</tr>
		<tr>
			<td>Category</td>
			<td>
				<strong>
			<?php
			$category = getCategoryData($data['category']);
			echo $category['name'];
			?>
				</strong>
			</td>
		</tr>
		<tr>
			<td>Question</td>
			<td><strong><?php echo $data['question'];?></strong></td>
		</tr>
		<tr>
			<td rowspan = "5">Choices</td>
			<td>A. <strong><?php echo $data['choiceA'];?></strong></td>
		</tr>
		<tr>
			<td>B. <strong><?php echo $data['choiceB'];?></strong></td>
		</tr>
		<tr>
			<td>C. <strong><?php echo $data['choiceC'];?></strong></td>
		</tr>
		<tr>
			<td>D. <strong><?php echo $data['choiceD'];?></strong></td>
		</tr>
		<tr>
			<td>E. <strong><?php echo $data['choiceE'];?></strong></td>
		</tr>
		<tr>
			<td>Answer</td>
			<td><strong><?php echo $data['answer']; ?></strong></td>
		</tr>
		<tr>
			<td></td>
			<td><input type ="submit" value ="Delete"/></td>
		</tr>
	</table>
	<input type = "hidden" name = "action" value = "deleteQuestion"/>
	</form>
</div>
		