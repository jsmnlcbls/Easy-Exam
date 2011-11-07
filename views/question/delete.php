<?php
include "functions/question.php";

$questionId = getUrlQuery("question-id");
$type = getUrlQuery('type');
$data = escapeOutput(getQuestionData($questionId, $type));
if (!empty($data)) {
?>
<div id = "delete-question-panel">
	<span class = "panel-title">Confirm Question Removal</span>
	<form method = "post" action = "admin.php">
	<input type ="hidden" name="question_id" value="<?php echo $questionId;?>">
	<?php
	echo questionHTML($type, $data);
	?>
	<input type ="submit" value ="Delete"/></td>
	<input type = "hidden" name = "action" value = "deleteQuestion"/>
	</form>
</div>
<?php } ?>