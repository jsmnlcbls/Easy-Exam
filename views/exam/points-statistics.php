<?php
include "functions/exam.php";
$examId = getUrlQuery('exam-id');
$revision = getUrlQuery('revision');
$accountId = getUrlQuery('account-id');
$statistics = getRecordedExamPointsStatistics($examId, $revision, $accountId);
?>
<div>
	<span class="panel-title">Examinee Points Statistics</span>
	<table>
		<tr>
			<th>Question</th>
			<th style="text-align:center">Points</th>
			<th style="text-align:center">Type</th>
		</tr>
	<?php
	$baseQuery = array('view' => 'exam-examinee-statistics',
					   'exam-id' => $examId,
					   'revision' => $revision,
					   'filter' => 'points');
	
	$totalPoints = 0;
	foreach ($statistics as $questionId => $value) {
		$totalPoints += $value['points'];
		$pointsType = $value['points_type'];
		echo '<tr>';
		echo '<td>';
		if (strlen($value['question']) > 80) {
			echo substr($value['question'], 0, 76), '...';
		} else {
			echo $value['question'];
		}
		echo '</td>';
		echo '<td>', $value['points'], '</td>';
		
		echo '<td>';
		if ($pointsType == POINTS_FULL) {
			echo 'Full'; 
		} elseif ($pointsType == POINTS_PARTIAL) {
			echo 'Partial';
		} elseif ($pointsType == POINTS_NONE) {
			echo 'None';
		} elseif ($pointsType == POINTS_UNKNOWN) {
			echo 'Unknown';
		}
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr><td colspan="3"><hr/></td></tr>';
	echo '<tr><td>Total Points</td><td>', $totalPoints, '</td><td></td>';
	?>
	</table>
</div>