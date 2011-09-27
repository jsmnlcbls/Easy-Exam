<?php
include "functions/question.php";

$questionId = getUrlQuery("questionId");
$type = getUrlQuery('type');
$data = escapeOutput(getQuestionData($questionId, $type));
?>
<div id = "delete-question-panel">
	<span class = "panel-title">Confirm Question Removal</span>
	<form method = "post" action = "admin.php">
	<input type ="hidden" name="questionId" value="<?php echo $questionId;?>">
	<?php
	echo questionHTML($type, $data);
	?>
	<input type ="submit" value ="Delete"/></td>
	<input type = "hidden" name = "action" value = "deleteQuestion"/>
	</form>
</div>
		