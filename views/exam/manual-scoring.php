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
				echo '<tr>';
				echo '<td>', $value['name'], '</td>';
				echo '<td>', $value['start_date_time']['date'], ' ', $value['start_date_time']['time'], '</td>';
				echo '<td>', $value['end_date_time']['date'], ' ', $value['end_date_time']['time'], '</td>';
				echo '</tr>';
			}
		?>
	</table>
</div>