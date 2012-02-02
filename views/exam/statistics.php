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
		$dateTime = $value['properties']['start_date_time']['date'] . ' ' . $value['properties']['start_date_time']['time'];
		$priority = date_timestamp_get(date_create($dateTime));
		$queue->insert($value, $priority);
	}
	
	$groupList = getAllUserGroups(getLoggedInUser('id'));
	foreach ($queue as $value) {
		$properties = $value['properties'];
		$name = $properties['name'];
		$startTime = $properties['start_date_time']['date'] . ' ' . $properties['start_date_time']['time'];
		$endTime = $properties['end_date_time']['date'] . ' ' . $properties['end_date_time']['time'];
		$questions = $properties['total_questions'];
		$takers = getRecordedExamTakersCount($value['exam_id'], $value['revision']); 
		
		$group = array();
		foreach ($value['properties']['group'] as $groupId) {
			$href = "?view=exam-group-statistics&user-group-id=$groupId"
				  . "&exam-id={$value['exam_id']}&revision={$value['revision']}";
			$link = "<a href=\"$href\">" . $groupList[$groupId]['name'] . '</a>';
			$group[] = $link;
		}
		
		$accountsLink = "?view=exam-examinee-statistics&exam-id={$value['exam_id']}&revision={$value['revision']}";
		$questionsLink = "?view=exam-question-statistics&exam-id={$value['exam_id']}&revision={$value['revision']}";
		
		echo '<tr>';
		echo '<td>', $name, '</a></td>';
		echo '<td>', $startTime, '</td>';
		echo '<td>', $endTime, '</td>';
		echo '<td>', implode(', ', $group), '</td>';
		echo '<td style="text-align:center"><a href="', $questionsLink, '">', $questions,  '</a></td>';
		echo '<td style="text-align:center"><a href="', $accountsLink, '">', $takers, '</a></td>';
		echo '</tr>';
	}
	
	?>
</div>