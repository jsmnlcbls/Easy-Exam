<?php
include "functions/exam.php";
$examId = getUrlQuery('exam-id');
$revision = getUrlQuery('revision');
$filter = getUrlQuery('filter', null);

$data = array();
$title = 'Exam Examinee Statistics';
if ($filter == 'passed') {
	$title = 'Passed Examinee Statistics';
	$data = getRecordedExamAccountStatistics($examId, $revision, 'pass');
} elseif ($filter == 'failed') {
	$title = 'Failed Examinee Statistics';
	$data = getRecordedExamAccountStatistics($examId, $revision, 'fail');
} elseif ($filter == 'top') {
	$title = 'Top Examinee Statistics';
	$data = getRecordedExamAccountStatistics($examId, $revision, 'top');
} elseif ($filter == 'bottom') {
	$title = 'Last Examinee Statistics';
	$data = getRecordedExamAccountStatistics($examId, $revision, 'bottom');
} elseif ($filter == 'points') {
	$title = 'Examinee By Points Statistics';
	$type = getUrlQuery('type');
	$question = getUrlQuery('question');
	$filterArguments = array('type' => $type, 'question' => $question);
	$data = getRecordedExamAccountStatistics($examId, $revision, 'points', $filterArguments);
}
else {
	$data = getRecordedExamAccountStatistics($examId, $revision);
}


?>
<div>
	<span class="panel-title"><?php echo $title; ?></span>
	<table>
		<tr>
			<th>Account</th><th>Total Points</th><th>Time Started</th><th>Time Ended</th>
		</tr>
		<?php
		foreach ($data as $value) {
			$timeStarted = date_format(date_create($value['time_started']), 'M j, Y h:i');
			$timeEnded = date_format(date_create($value['time_ended']), 'M j, Y h:i');
			echo '<tr>';
			echo '<td>', $value['name'], '</td>';
			echo '<td style="text-align:center">', $value['total_points'], '</td>';
			echo '<td>', $timeStarted, '</td>';
			echo '<td>', $timeEnded, '</td>';
			echo '</tr>';
		}
		?>
	</table>
</div>