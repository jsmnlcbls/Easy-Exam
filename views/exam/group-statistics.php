<?php
include "functions/exam.php";
include "functions/user.php";
$examId = getUrlQuery('exam-id');
$revision = getUrlQuery('revision');
$groupId = getUrlQuery('user-group-id');
$examData = getExamProperties($examId, $revision);
$groups = getAllUserGroups(getLoggedInUser('id'));
$statistics = getRecordedExamGroupStatistics($examId, $revision, $groupId);
?>
<div>
	<span class="panel-title">Exam Group Statistics</span>
	<table>
		<tr>
			<td>Exam Name</td>
			<td><strong><?php echo $examData['name']; ?></strong></td>
		</tr>
		<tr>
			<td>Group</td>
			<td><strong><?php echo $groups[$groupId]['name']; ?></strong></td>
		</tr>
		<tr>
			<td>Total Examinees</td>
			<td><a href="<?php echo "?view=exam-examinee-statistics&exam-id={$examId}&revision={$revision}"; ?>">
				<strong><?php echo $statistics['total_examinees']; ?></strong>
				</a>
			</td>
		</tr>
		<tr>
			<td>Passed Examinees</td>
			<td>
				<a href="<?php echo "?view=exam-examinee-statistics&exam-id={$examId}&revision={$revision}&filter=passed"; ?>">
				<strong><?php echo $statistics['passed_examinees']; ?></strong>
				</a>
			</td>
		</tr>
		<tr>
			<td>Failed Examinees</td>
			<td>
				<a href="<?php echo "?view=exam-examinee-statistics&exam-id={$examId}&revision={$revision}&filter=failed"; ?>">
				<strong><?php echo $statistics['failed_examinees']; ?></strong>
				</a>
			</td>
		</tr>
		<tr>
			<td>Passing Percentage</td>
			<td><strong><?php echo $statistics['passing_percentage']; ?>%</strong></td>
		</tr>
		<tr>
			<td>Average Score</td>
			<td><strong><?php echo $statistics['average_score']; ?></strong></td>
		</tr>
		<tr>
			<td>Highest Score</td>
			<td>
				<a href="<?php echo "?view=exam-examinee-statistics&exam-id={$examId}&revision={$revision}&filter=top"; ?>">
				<strong><?php echo $statistics['highest_score']; ?></strong>
				</a>
			</td>
		</tr>
		<tr>
			<td>Lowest Score</td>
			<td>
				<a href="<?php echo "?view=exam-examinee-statistics&exam-id={$examId}&revision={$revision}&filter=bottom"; ?>">
				<strong><?php echo $statistics['lowest_score']; ?></strong>
				</a>
			</td>
		</tr>
		<tr>
			<td>Average Score</td>
			<td><strong><?php echo $statistics['average_score']; ?></strong></td>
		</tr>
	</table>
</div>