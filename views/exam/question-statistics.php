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
	
	$pointsTypeList = array(POINTS_FULL, POINTS_PARTIAL, POINTS_NONE, POINTS_UNKNOWN);
	foreach ($statistics as $questionId => $value) {
		$point = $value['point'];
	
		echo '<tr>';
		echo '<td>';
		if (strlen($value['question']) > 80) {
			echo substr($value['question'], 0, 76), '...';
		} else {
			echo $value['question'];
		}
		echo '</td>';
		
		foreach ($pointsTypeList as $type) {
			$query = array_merge($baseQuery, array('type' => $type, 'question' => $questionId));
			$link = relativeLink('', $query);
			if (isset($point[$type])) {
				echo '<td style="text-align:center"><a href="', $link, '">', $point[$type], '</a></td>';
			} else {
				echo '<td style="text-align:center">0</td>';
			}
		}
		echo '</tr>';
	}
	?>
	</table>
</div>