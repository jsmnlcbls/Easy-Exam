<?php
include "functions/exam.php";
$examId = getUrlQuery('exam-id');
$revision = getUrlQuery('revision');
$data = getQuestionsForManualScoring($examId, $revision);
?>
<div>
	<span class="panel-title">Question Scoring</span>
	<ul>
	<?php
		foreach ($data as $questionId => $question) {
			$link = relativeLink(null, array('view' => 'exam-answer-scoring',
											 'exam-id' => $examId,
											 'revision' => $revision,
											 'question-id' => $questionId));
			echo '<li><a href="', $link, '">', $question, '</a></li>';
		}
	?>
	</ul>
</div>