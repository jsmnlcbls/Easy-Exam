<div>
	<span class="panel-title">Exam Results Summary</span>
	<table>
		<tr>
			<th>Exam Name</th><th>Date Taken</th><th>Points</th><th>Notes</th>
		</tr>
		
			<?php
			$data = getRecordedExamResultsByAccount(getLoggedInUser('id'));
			foreach ($data as $value) {
				$examProperty = $value['properties'];
				$note = '';
				if ($value['status'] == STATUS_NEEDS_MANUAL_SCORING) {
					$note = 'FMC';
				}
				
				echo '<tr>';
				echo '<td>', $examProperty['name'], '</td>';
				echo '<td>', date_format(date_create($value['time_started']), "Y-m-d H:i"), '</td>';
				echo '<td style="text-align:center">', $value['total_points'], '</td>';
				echo '<td class="note">', $note, '</td>';
				echo '</tr>';
			}
			?>
		
	</table>
</div>