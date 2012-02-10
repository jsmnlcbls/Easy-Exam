<?php
include "functions/exam.php";
$data = getRecordedExamsForManualScoring();
?>
<div>
	<span class="panel-title">Manual Scoring</span>
	<table>
		<tr>
			<th>Exam</th><th>Start Time</th><th>End Time</th>
		</tr>
		<?php
			foreach ($data as $value) {
				$examId = $value['exam_id'];
				$revision = $value['revision'];
				$link =  relativeLink(null, array('view' => 'exam-question-scoring', 
												  'exam-id' => $examId, 
												  'revision' => $revision));
				echo '<tr>';
				echo '<td><a href="',$link,'">', $value['name'], '</a></td>';
				echo '<td>', $value['start_date_time']['date'], ' ', $value['start_date_time']['time'], '</td>';
				echo '<td>', $value['end_date_time']['date'], ' ', $value['end_date_time']['time'], '</td>';
				echo '</tr>';
			}
		?>
	</table>
</div>