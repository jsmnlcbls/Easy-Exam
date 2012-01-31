<?php
include "functions/exam.php";
$examId = getUrlQuery('exam-id');
$revision = getUrlQuery('revision');
$statistics = getRecordedExamQuestionStatistics($examId, $revision);
?>
<div>
	<span class="panel-title">Exam Question Statistics</span>
	<table>
		<tr>
			<th>Question</th>
			<th style="text-align:center">Full</th>
			<th style="text-align:center">Partial</th>
			<th style="text-align:center">None</th>
			<th style="text-align:center">Unknown</th>
		</tr>
	<?php
	foreach ($statistics as $value) {
		$point = $value['point'];
		echo '<tr>';
		echo '<td>';
		if (strlen($value['question']) > 80) {
			echo substr($value['question'], 0, 76), '...';
		} else {
			echo $value['question'];
		}
		echo '</td>';
		echo '<td>', $point[POINTS_FULL], '</td>';
		echo '<td>', $point[POINTS_PARTIAL], '</td>';
		echo '<td>', $point[POINTS_NONE], '</td>';
		echo '<td>', $point[POINTS_UNKNOWN], '</td>';
		echo '</tr>';
	}
	?>
	</table>
</div>