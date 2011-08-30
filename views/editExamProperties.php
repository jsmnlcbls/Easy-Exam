<?php
include "functions/exam.php";
$data = escapeOutput(getExamData(intval(filterGET("examId"))));
?>
<div id = "add-exam-panel">
	<span class = "panel-title">Modify An Exam</span>
	<form method = "post" action = "admin.php" id = "add-exam-form">
		<input type = "hidden" name = "action" value = "editExam" />
		<input type = "hidden" name = "examId" value = "<?php echo $data['exam_id']; ?>" />
		<table>
			<tr>
				<td>Exam Name</td>
				<td><input type = "text" name = "examName" value="<?php echo $data['name']?>"/></td>
			</tr>
			<tr>
				<td>Get Questions From</td>
				<td>
				<select name = "category">
					<?php
						$categories = getAllCategories();
						foreach ($categories as $category) {
							$name = escapeOutput($category['name']);
							$id = $category['category_id'];
							if ($id == $data['questions_category']) {
								echo "<option selected = \"selected\" value = \"{$id}\">{$name}</option>";
							} else {
								echo "<option value = \"{$id}\">{$name}</option>";
							}
						}
					?>
				</select>
				</td>
			</tr>
			<tr>
				<td>Exam Availability Start</td>
				<td>
					<?php
						$date = date_create($data['start_date_time']);
						$startDate = date_format($date, "Y-m-d");
						$startTime = date_format($date, "H:i");
						
						$date = date_create($data['end_date_time']);
						$endDate = date_format($date, "Y-m-d");
						$endTime = date_format($date, "H:i");
					?>
					Date <input value = "<?php echo $startDate; ?>" style="width:5em;text-align:center" type="text" name="startDate"/>
					Time <input value = "<?php echo $startTime; ?>" style="width:3em;text-align:center" type="text" name="startTime"/>
				</td>
			</tr>
			<tr>
				<td>Exam Availability End</td>
				<td>
					Date <input value = "<?php echo $endDate; ?>" style="width:5em;text-align:center" type="text" name="endDate"/>
					Time <input value = "<?php echo $endTime; ?>" style="width:3em;text-align:center" type="text" name="endTime"/>
				</td>
			</tr>
			<tr>
				<td>Time Limit In Hours</td>
				<td>
					<input value = "<?php echo $data['time_limit']; ?>" style="width:2em;text-align:center" type="text" name="timeLimit"/>
				</td>
			</tr>
			<tr>
				<td>Passing Score</td>
				<td><input value ="<?php echo $data['passing_score']; ?>" type="text" name="passingScore" style="width:2em"/> %
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Save"/></td>
			</tr>
			<tr>
				
				<td colspan="2" style="color:GREEN;font-size:80%">
					<p>
					The date of exam expects the following format for entry: YYYY-MM-DD.<br/>
					YYYY is a 4 digit year, MM is a 2 digit month and DD is the 2 digit day.<br/>
					Example: For May 8, 2012 the entry should be 2012-05-08 <br/><br/>
					For the time of exam, the following format is expected: HH:MM<br/>
					HH is the time in hour (0 t0 24) and MM is in minutes (0 to 60)<br/>
					Example: 6:30PM should be specified ad 18:30
					</p>
				</td>
			</tr>
			
		</table>
		
	</form>
</div>
		