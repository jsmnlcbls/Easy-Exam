<?php
include "functions/exam.php";
$examId = getUrlQuery('exam-id');
$revision = getUrlQuery('revision');
$data = getRecordedExamAccountStatistics($examId, $revision);
?>
<div>
	<span class="panel-title">Exam Examinee Statistics</span>
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