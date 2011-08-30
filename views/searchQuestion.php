<div id = "search-question-panel">
	<span class = "panel-title">Search Database For Questions</span>
	<form method = "get" action = "admin.php">
	<input type = "hidden" name = "view" value = "searchResultsQuestion">
	<table id = "questions-table">
		<tr>
			<tr>
				<td>Question Type</td>
				<td>
					<select name = "questionType">
					<option value = "">None Selected</option>
					<?php
					$questionTypes = getAllQuestionTypes();
					foreach ($questionTypes as $type) {
						echo "<option value = \"{$type['id']}\">{$type['name']}</option>";
					}
					?>
					</select>
				</td>
			</tr>
			<td>In Category</td>
			<td>
				<select name = "category">
				<?php
					$categories = getAllCategories();
					foreach ($categories as $category) {
						if ($category['category_id'] == 0) {
							echo '<option value = "">None Selected</option>';
						} else {
							$name = escapeOutput($category['name']);
							echo "<option value = \"{$category['category_id']}\">{$name}</option>";
						}
					}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Search Questions For</td>
			<td><input type = "text" name = "question"/></td>
		</tr>
		<tr>
			<td>Search Choices For</td>
			<td><input type = "text" name = "choice"/></td>
		</tr>
		<tr>
			<td></td>
			<td><button>Search</button></td>
		</tr>
	</table>
	<div style="font-size:80%; color:GREEN">
		The wildcard characters "%" and "_" can be used in searching.<br/>
		"%" will match any number of characters<br/>
		"_" will match just one character<br/>
		Examples: <br/>
		calculate% will find any question that begins with "calculate".<br/>
		"%average% will find any question containing the string "average"
	</div>
	</form>
</div>
		