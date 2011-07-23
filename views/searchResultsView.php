<div id = "search-results-panel">
	<span class = "panel-title">Search Results</span>
	<?php
	include "functions/question.php";
	
	$data = array();
	$data['category'] = intval(filterGET('category'));
	$data['question'] = filterGET('question');
	$data['choice'] = filterGET('choice');
	$data['type'] = substr(filterGET('questionType'), 0, 1);
	
	$result = searchQuestions($data);
	
	echo "<table id =\"searchResultsTable\">";
	echo "<tr><td>#</td><td>Question</td><td>Action</td></tr>";
	$counter = 1;
	foreach ($result as $question) {
		$id = $question['question_id'];
		echo "<tr><td>{$counter}</td><td>";
		echo "<form method=\"get\">";
		echo $question['question'];
		echo "</td><td>";
		echo "<a href=\"?view=editQuestion&category=$id\">Edit</a> | ";
		echo "<a href=\"?view=deleteQuestion&category=$id\">Delete</a>";
		echo "</td></tr>";
		echo "</form>";
		$counter++;
	}
	echo "</table>";
	?>
</div>
		