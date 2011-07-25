
<div id = "add-exam-panel">
	<span class = "panel-title">Create New Exam</span>
	<form method = "post" action = "admin.php" id = "add-exam-form">
		<table>
			<tr>
				<td>Exam Name</td>
				<td><input type = "text" name = "categoryName" /></td>
			</tr>
			<tr>
				<td>Get Questions From</td>
				<td>
				<select name = "parentCategory">
					<?php
						$categories = (getAllCategories());
							foreach ($categories as $category) {
							if ($category['category_id'] == 0) {
								echo '<option value = "0">None Selected</option>';
							} else {
								echo "<option value = \"{$category['category_id']}\">{$category['name']}</option>";
							}
						}
					?>
				</select>
				</td>
			</tr>
			<tr>
				<td>Passing Score</td>
				<td><input type="text" name="passingScore" style="width:2em"/> %
				</td>
			</tr>
			<tr>
				<td>Start Of Exam</td>
				<td>
					Date <input style="width:5em;text-align:center" type="text" name="startDate"/>
					Time <input style="width:3em;text-align:center" type="text" name="startTime"/>
				</td>
			</tr>
			<tr>
				<td>End Of Exam</td>
				<td>
					Date <input style="width:5em;text-align:center" type="text" name="endDate"/>
					Time <input style="width:3em;text-align:center" type="text" name="endTime"/>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Add" name = "action"/></td>
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
		<input type = "hidden" name = "action" value = "addCategory" />
	</form>
</div>
		