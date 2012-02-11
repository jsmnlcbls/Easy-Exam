<?php
include "functions/exam.php";
include "functions/question.php";
$examId = getUrlQuery('exam-id');
$revision = getUrlQuery('revision');
$questionId = getUrlQuery('question-id');
$questionData = getQuestionData($questionId);
$data = getAnswersForManualScoring($examId, $revision, $questionId);
$scoreData = getExamQuestions($examId, $revision);
?>
<div>
	<span class="panel-title">Answer Scoring</span>
	<div>
		Answers for the question "<em><?php echo $questionData['question']; ?></em>"
	</div>
	<form method="post" action="admin.php">
	<input type="hidden" name="action" value="updateExamScores"/>
	<input type="hidden" name="exam_id" value ="<?php echo $examId; ?>"/>
	<input type="hidden" name="revision" value ="<?php echo $revision; ?>"/>
	<input type="hidden" name="question_id" value="<?php echo $questionId; ?>"/>
	<ul>
	<?php
		$questionType = $questionData['type'];
		foreach ($data as $value) {
			$data = array('points' => $scoreData[$questionId]['points'],
						  'answer' => $value['answer'],
						  'account_id' => $value['account_id']);
			echo '<li>', answerScoringHTML($questionType, $data), '<hr/></li>';
		}
	?>
	</ul>
	<input type="submit" value="Submit" style="float:right"/>
	</form>
</div>