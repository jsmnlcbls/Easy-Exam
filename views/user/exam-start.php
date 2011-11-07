<?php if (isset($data)) { ?>
<div id = "take-exam-panel">
<span class="panel-title"><?php echo $data['name']; ?></span>
<ul>
<li>Time Limit: <?php echo $data['time_limit'], ' ', $data['time_limit'] <= 1 ? 'hour' : 'hours'; ?></li>
<li>Passing Score: <?php echo $data['passing_score'], $data['score_is_percentage'] ? '%' : ' pts'; ?></li>
<li>Total Questions: <?php echo $data['total_questions']; ?></li>
</ul>
<br/>
<form method = "post" action = "index.php">
	<input type="hidden" name="action" value="startExam"/>
	<input type="hidden" name="exam_id" value="<?php echo $data['exam_id']; ?>"/>
	<input type="hidden" name="revision" value="<?php echo $data['revision']; ?>"/>
	<input type="submit" value="Start Exam"/>
</form>
</div>
<?php } ?>