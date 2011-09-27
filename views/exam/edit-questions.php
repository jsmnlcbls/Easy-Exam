<?php
include "functions/exam.php";
include "functions/question.php";
$examId = getUrlQuery("examId");
$data = getExamData($examId);
?>
<div id = "edit-category-panel">
	<span class = "panel-title">Edit Questions Of Exam: <em><?php echo escapeOutput($data['name']);?></em></span>
	
	<input type = "hidden" name = "action" value = "editCategory" />
	<?php
	$questions = getExamQuestions($examId);
	$questionsCount = count($questions);

	echo "<div style=\"color: GREEN; margin-bottom: 1em;\">";
	echo "Total Questions: $questionsCount";
	echo "</div>";

	$count = 1;
	echo "<ol>";
	foreach ($questions as $value) {
		$out = "<li><div class=\"question-div\">";
		$type = $value['type'];
		$data = getQuestionData($value['question_id'], $type);
		$view = getQuestionEditView($type);
		$out .= questionHTML($type, $data);
		
		$parameters = array('view' => $view, 'questionId' => $value['question_id'],
							'examId' => $examId);
		$editLink = http_build_query($parameters);
		$out .= "<div class=\"question-options\"><a href=\"?$editLink\">Modify</a>";
		$out .= "<form class=\"hidden-form\" method=\"post\" action=\"admin.php\">";
		$out .= "</form></div>";
		$out .= "</div></li>";
		echo $out;
		$count++;
	}
	echo "</ol>"
	?>

</div>
		