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
	$baseQuery = array('view' => 'exam-examinee-statistics',
					   'exam-id' => $examId,
					   'revision' => $revision,
					   'filter' => 'points');
	
	foreach ($statistics as $questionId => $value) {
		$point = $value['point'];
		
		$fullPointsQuery = array_merge($baseQuery, array('type' => POINTS_FULL, 'question' => $questionId));
		$fullPointsLink = relativeLink('', $fullPointsQuery);
		
		$partialPointsQuery = array_merge($baseQuery, array('type' => POINTS_PARTIAL, 'question' => $questionId));
		$partialPointsLink = relativeLink('', $partialPointsQuery);
		
		$noPointsQuery = array_merge($baseQuery, array('type' => POINTS_NONE, 'question' => $questionId));
		$noPointsLink = relativeLink('', $noPointsQuery);
		
		$unknownPointsQuery = array_merge($baseQuery, array('type' => POINTS_UNKNOWN, 'question' => $questionId));
		$unknownPointsLink = relativeLink('', $unknownPointsQuery);
		
		echo '<tr>';
		echo '<td>';
		if (strlen($value['question']) > 80) {
			echo substr($value['question'], 0, 76), '...';
		} else {
			echo $value['question'];
		}
		echo '</td>';
		echo '<td><a href="', $fullPointsLink, '">', $point[POINTS_FULL], '</a></td>';
		echo '<td><a href="', $partialPointsLink, '">', $point[POINTS_PARTIAL], '</td>';
		echo '<td><a href="', $noPointsLink, '">', $point[POINTS_NONE], '</td>';
		echo '<td><a href="', $unknownPointsLink, '">', $point[POINTS_UNKNOWN], '</td>';
		echo '</tr>';
	}
	?>
	</table>
</div>