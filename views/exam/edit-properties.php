<?php
include "functions/exam.php";
include "functions/user.php";
$examId = getUrlQuery("examId");
$data = escapeOutput(getExamData($examId));
?>
<div id = "add-exam-panel">
	<span class = "panel-title">Modify An Exam</span>
	<form method = "post" action = "admin.php" id = "edit-exam-form">
		<input type = "hidden" name = "action" value = "editExam" />
		<input type = "hidden" name = "exam_id" value = "<?php echo $data['exam_id']; ?>" />
		<table>
			<tr>
				<td>Exam Name</td>
				<td><input type = "text" name = "name" value="<?php echo $data['name'];?>"/></td>
			</tr>
			<tr>
				<td>Available Only To</td>
				<td>
					<?php
					$count = 0;
					foreach ($data['group'] as $groupId) {
						$button = '';
						$attributes = array();
						if ($count == 0) {
							$attributes = array('name' => 'group[]', 'selected' => $groupId);
						} else {
							$attributes = array('name' => 'group[]', 'selected' => $groupId);
						}
						echo userGroupSelectHTML($attributes);
						echo "\n";
						echo $button;
						$count++;
					}
					?>
					<script>
						$("select[name='group[]']").userGroupChoice();
					</script>
				</td>
			</tr>
			<tr>
				<td>Get Questions From</td>
				<td>
				<?php 
				$attributes = array('name' => 'questions_category',
									'selected' => $data['questions_category']);
				echo questionCategorySelectHTML($attributes);
				?>
				</td>
			</tr>
			<tr>
				<td>Total Questions</td>
				<td><input type="text" name="max_questions" value="<?php echo $data['max_questions'];?>"style="width:2em"/>
				</td>
			</tr>
			<tr>
				<td>Exam Availability Start</td>
				<td>
					<?php 
					$dateTime = date_create($data['start_date_time']);
					$date = date_format($dateTime, 'Y-m-d');
					$time = date_format($dateTime, 'H:i');
					?>
					Date <input value="<?php echo $date;?>" style="width:5em;text-align:center" type="text" name="start_date_time[date]"/>
					<span class="note">*</span>
					Time <input value ="<?php echo $time;?>" style="width:3em;text-align:center" type="text" name="start_date_time[time]"/>
					<span class="note">**</span>
				</td>
			</tr>
			<tr>
				<td>Exam Availability End</td>
				<td>
					<?php
					$dateTime = date_create($data['end_date_time']);
					$date = date_format($dateTime, 'Y-m-d');
					$time = date_format($dateTime, 'H:i');
					?>
					Date <input value="<?php echo $date; ?>" style="width:5em;text-align:center" type="text" name="end_date_time[date]"/>
					<span class="note">*</span>
					Time <input value="<?php echo $time; ?>" style="width:3em;text-align:center" type="text" name="end_date_time[time]"/>
					<span class="note">**</span>
				</td>
			</tr>
			<tr>
				<td>Time Limit In Hours</td>
				<td>
					<input value="<?php echo $data['time_limit']; ?>"style="width:2em;text-align:center" type="text" name="time_limit"/>
				</td>
			</tr>
			<tr>
				<td>Passing Score</td>
				<td>
					<?php
					$score = $data['passing_score'];
					if ($data['score_is_percentage']) {
						$score .= '%';
					}
					?>
					<input value="<?php echo $score; ?>" type="text" name="passing_score" style="width:2em"/>
					<span class="note">***</span>
				</td>
			</tr>
			<tr>
				<td>Points Per Question</td>
				<td>
					<select name ="default_points">
					<?php
					for ($a = 1; $a <= 10; $a++) {
						if ($a == $data['default_points']) {
							echo "<option selected=\"selected\" value =\"{$a}\">$a</option>";
						} else {
							echo "<option value =\"{$a}\">$a</option>";
						}
					}
					?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Question Display</td>
				<td>
					<?php
					$checkAllAtOnce = '';
					$checkOneByOne = '';
					$checkByGroup = '';
					$groupCount = '';
					$check = 'checked="checked"';
					if ($data['question_display'] == QUESTION_DISPLAY_ALL_AT_ONCE) {
						$checkAllAtOnce = $check;
					} elseif ($data['question_display'] == QUESTION_DISPLAY_ONE_BY_ONE) {
						$checkOneByOne = $check;
					} else {
						$checkByGroup = $check;
						$groupCount = $data['question_display'];
					}
					?>
					<input <?php echo $checkAllAtOnce;?> type="radio" name="question_display[mode]" value="0"/> All At Once<br/>
					<input <?php echo $checkOneByOne;?> type="radio" name="question_display[mode]" value="1"/> One By One<br/>
					<input <?php echo $checkByGroup;?> type="radio" name="question_display[mode]" value="G"/> In Groups Of 
					<input value="<?php echo $groupCount;?>" type="text" name="question_display[group]" style="width:2em"/><br/>
				</td>
			</tr>
			<tr>
				<td>Other Options</td>
				<td>
					<?php
					$check = 'checked="checked"';
					$checkRecorded = $data['recorded'] ? $check : '';
					$checkRandomize = $data['randomize'] ? $check : '';
					$checkMaxTakeEnabled = '';
					$maxTakeCount = '';
					if ($data['max_take'] != EXAM_NO_REPEAT) {
						$checkMaxTakeEnabled = $check;
						$maxTakeCount = $data['max_take'];
					}
					?>
					<input <?php echo $checkRecorded;?> type="checkbox" name="recorded" value="true"/> Recorded <br/>
					<input <?php echo $checkRandomize;?> type="checkbox" name="randomize" value="true"/> Randomize Questions<br/>
					<input <?php echo $checkMaxTakeEnabled;?> type="checkbox" name="max_take[enabled]" value="true"/> Repeatable <br/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Up To 
					<select name ="max_take[count]">
						<option value=""></option>
					<?php
					for ($a = 1; $a < 10; $a++) {
						if ($a == $maxTakeCount) {
							echo "<option selected=\"selected\" value=\"{$a}\">{$a}</option>";
						} else {
							echo "<option value=\"{$a}\">{$a}</option>";
						}
					}
					?>
					</select>
					Times <span class="note">****</span><br/>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Save"/></td>
			</tr>
			<tr>
				
				<td colspan="2" class="note">
					<p>
					* The date of exam expects the following format for entry: YYYY-MM-DD.<br/>
					YYYY is a 4 digit year, MM is a 2 digit month and DD is the 2 digit day.<br/>
					Example: For May 8, 2012 the entry should be 2012-05-08
					</p>
					<p>
					** For the time of exam, the following format is expected: HH:MM<br/>
					HH is the time in hour (0 to 24) and MM is in minutes (0 to 60)<br/>
					Example: 6:30PM should be specified ad 18:30
					</p>
					<p>
					*** Specify number of points or percentage by appending '%' at the end.
					</p>
					<p>
					**** Specify if the exam allows retake. The first take of an exam is not<br/>
					counted in the repeat count, so a repeat count of 1 will allow an exam to</br>
					be taken twice.
					</p>
				</td>
			</tr>
		</table>
	</form>
</div>
		