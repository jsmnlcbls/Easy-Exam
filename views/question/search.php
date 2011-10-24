<?php
include "functions/question.php";
?>
<div id = "search-question-panel">
	<span class = "panel-title">Search Database For Questions</span>
	<form method = "get" action = "admin.php">
	<input type = "hidden" name = "view" value = "question-search-results">
	<table id = "questions-table">
		<tr>
			<tr>
				<td>Question Type</td>
				<td>
					<select name = "type">
					<option value = ""></option>
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
				<?php
				echo questionCategorySelectHTML(array(), getLoggedInUser('id'));
				?>
			</td>
		</tr>
		<tr>
			<td>Search Questions For</td>
			<td><input type = "text" name = "question"/></td>
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
		"%average%" will find any question containing the string "average
	</div>
	</form>
</div>
		