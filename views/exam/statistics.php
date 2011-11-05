<div>
	<span class="panel-title">Recorded Exam Statistics</span>
	<table>
		<tr>
			<th>Exam Name</th><th>Start Time</th><th>End Time</th><th>Group</th><th>Questions</th><th>Examinees</th>
		</tr>
	<?php
	include "functions/exam.php";
	include "functions/user.php";
	$exams = getRecordedExams(getLoggedInUser('id'));

	$queue = new SplPriorityQueue();
	foreach ($exams as $value) {
		$priority = date_timestamp_get(date_create($value['properties']['start_date_time']));
		$queue->insert($value, $priority);
	}
	
	$groupList = getAllUserGroups(getLoggedInUser('id'));
	foreach ($queue as $value) {
		$properties = $value['properties'];
		$name = $properties['name'];
		$startTime = date_format(date_create($properties['start_date_time']), 'M j, Y h:s');
		$endTime = date_format(date_create($properties['end_date_time']) , 'M j, Y h:s');
		$questions = $properties['total_questions'];
		$takers = getRecordedExamTakersCount($value['exam_id'], $value['revision']); 
		
		$group = array();
		foreach ($value['properties']['group'] as $groupId) {
			$group[] = $groupList[$groupId]['name'];
		}
		echo '<tr>';
		echo '<td>', $name, '</a></td>';
		echo '<td>', $startTime, '</td>';
		echo '<td>', $endTime, '</td>';
		echo '<td>', implode(', ', $group), '</td>';
		echo '<td style="text-align:center">', $questions, '</td>';
		echo '<td style="text-align:center">', $takers, '</td>';
		echo '</tr>';
	}
	
	?>
</div>