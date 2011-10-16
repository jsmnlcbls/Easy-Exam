<?php
include "functions/exam.php";
include "functions/question.php";
$examId = getUrlQuery("examId");
$examData = getExamData($examId);
$randomQuestions = (bool) $examData['randomize'];
$categoryData = getQuestionCategoryData($examData['questions_category']);
$questions = getExamQuestions($examId, $examData['revision']);
if (empty($questions)) {
	$questions = getCategoryQuestions($examData['questions_category']);
}
?>
<div id = "edit-exam-panel">
	<div class = "panel-title">Edit Exam <em style="font-size:70%">(Step 2 of 2)</em></div>
	<div><em>Set Exam Questions</em></div>
	<hr/>
	<div class="note">
		Selected Questions: <span id="selected-questions-count"></span>
		<?php echo $randomQuestions ? '' : "/{$examData['total_questions']}"; ?> |
		Passing Score: <?php echo $examData['passing_score'], 
								  $examData['score_is_percentage'] ? '%' : ' pts';?> | 
		
		<?php echo $randomQuestions ? 'Total Exam Points: ' : 'Current Exam Points Total: '; ?> 
		<span id="total-exam-points-container">
		<?php echo $randomQuestions ? ($examData['total_questions'] * $examData['default_points']) : '0'; ?>
		</span>
	</div>
	<form method="post" action="admin.php">
	<input type="hidden" name="action" value="editExam"/> 
	<input type="hidden" name="step" value="2"/> 
	<input type="hidden" name="exam_id" value="<?php echo $examId;?>" />
	<input type="hidden" name="revision" value="<?php echo $examData['revision']; ?>" />
	<ol id="questions-list">
	<?php
		$out = array();
		$defaultPoints = $examData['default_points'];
		foreach ($questions as $value) {
			$name = $value['question_id'];
			
			$includeCheckbox = '<input type="checkbox" value="1" name="'. $name . '[enabled]" />';
			if ($value['enabled']) {
				$includeCheckbox = '<input checked="checked" type="checkbox" value="1" name="'. $name . '[enabled]" />';	
			}
			
			$options = array();
			if (!$randomQuestions) {
				$pointsInput = '<input class="question-points" style="width:1.5em" maxlength="2" type="text" value="'. $value['points'] . '" name="' . $name . '[points]" />';
				$options[] = "Points {$pointsInput}";
				$options[] = 'Order <input style="width:2em" maxlength="3" type="text" value="' . $value['order'] . '" name="' . $name . '[order]" />';
			} else {
				$options[] = '<input type="hidden" name="' . $name . '[points]" value="' . $defaultPoints . '" />';
				$options[] = '<input type="hidden" name="' . $name . '[order]" value="' . $value['order'] . '" />';
			}
			$out[] = '<li>';
			$out[] = '<input type="hidden" name="' . $name . '[type]" value="' . $value['type'] .'" />'; 
			$out[] = "<div>{$includeCheckbox}{$value['question']}</div>";
			$out[] = '<div style="font-size:80%">'. implode(' ', $options) . '</div>';
			$out[] = '</li>';
		}
		echo implode("\n", $out);
	?>
	</ol>
	<input type="submit" value ="Done"/>
	<form>
</div>
<script>
	$('input[type=checkbox]').questionSelection({container:'#selected-questions-count'});
	$('input[type="text"]').allowOnlyDigits();
	<?php if (!$randomQuestions) { ?>
	$('.question-points').examPointsCounter({container:'#total-exam-points-container'});
	<?php } ?>
</script>
		