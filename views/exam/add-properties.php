<?php
include "functions/user.php";
?>
<div id = "add-exam-panel-step-1">
	<div class = "panel-title">Add Exam <em style="font-size:70%">(Step 1 of 2)</em></div>
	<div><em>Set Exam Properties</em></div>
	<hr/>
	<form method = "post" action = "admin.php" id = "add-exam-form">
		<input type="hidden" name="action" value="addExam" />
		<input type="hidden" name="step" value="1" />
		<input type="hidden" name="revision" value="0" />
		<table>
			<tr>
				<td>Exam Name</td>
				<td><input type = "text" name = "name" /></td>
			</tr>
			<tr>
				<td>Available Only To</td>
				<td>
					<?php
					$attributes = array('name' => 'group[]', 'id' => 'initial-user-group');
					$owner = getLoggedInUser('id');
					echo userGroupSelectHTML($attributes, $owner); 
					?>
					<script>
						$('#initial-user-group').userGroupChoice();
					</script>
				</td>
			</tr>
			<tr>
				<td>Get Questions From</td>
				<td>
				<?php 
				$attributes = array('name' => 'questions_category');
				$owner = getLoggedInUser('id');
				echo questionCategorySelectHTML($attributes, $owner);
				?>
				</td>
			</tr>
			<tr>
				<td>Total Questions</td>
				<td><input type="text" name="total_questions" style="width:2em"/>
				</td>
			</tr>
			<tr>
				<td>Exam Availability Start</td>
				<td>
					Date <input style="width:5em;text-align:center" type="text" name="start_date_time[date]"/>
					<span class="note">*</span>
					Time <input style="width:3em;text-align:center" type="text" name="start_date_time[time]"/>
					<span class="note">**</span>
				</td>
			</tr>
			<tr>
				<td>Exam Availability End</td>
				<td>
					Date <input style="width:5em;text-align:center" type="text" name="end_date_time[date]"/>
					<span class="note">*</span>
					Time <input style="width:3em;text-align:center" type="text" name="end_date_time[time]"/>
					<span class="note">**</span>
				</td>
			</tr>
			<tr>
				<td>Time Limit In Hours</td>
				<td>
					<input style="width:2em;text-align:center" type="text" name="time_limit"/>
				</td>
			</tr>
			<tr>
				<td>Passing Score</td>
				<td>
					<input type="text" name="passing_score" style="width:2em"/>
					<span class="note">***</span>
				</td>
			</tr>
			<tr>
				<td>Points Per Question</td>
				<td>
					<input type="text" name="default_points" value="1" style="width:1.5em" maxlength="2"/>
				</td>
			</tr>
			<tr>
				<td>Question Display</td>
				<td>
					<input type="radio" name="question_display[mode]" value="0" checked="checked"/> All At Once<br/>
					<input type="radio" name="question_display[mode]" value="1"/> One By One<br/>
					<input type="radio" name="question_display[mode]" value="G"/> In Groups Of 
					<input type="text" name="question_display[group]" style="width:2em"/><br/>
				</td>
			</tr>
			<tr>
				<td>Other Options</td>
				<td>
					<input type="checkbox" name="recorded" value="true" checked="checked"/> Recorded <br/>
					<input type="checkbox" name="randomize" value="true" checked="checked"/> Randomize Questions<br/>
					<input type="checkbox" name="max_take[enabled]" value="true"/> Repeatable <br/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Up To 
					<select name ="max_take[count]">
						<option value=""></option>
					<?php
					for ($a = 1; $a < 10; $a++) {
						echo "<option value=\"{$a}\">{$a}</option>";
					}
					?>
					</select>
					Times <span class="note">****</span><br/>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><button id="next-button">Next</button></td>
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
