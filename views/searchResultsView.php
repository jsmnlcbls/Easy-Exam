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
	
	$countResults = count($result);
	if ($countResults > 0) {
		if ($countResults == 1) {
			echo "1 question found.";
		} else {
			echo "$countResults questions found.";
		}
		echo "<br/><br/>";
		echo "<table id =\"searchResultsTable\">";
		echo "<tr><td>#</td><td>Question</td><td>Action</td></tr>";
		$counter = 1;
		foreach ($result as $question) {
			$id = $question['question_id'];
			echo "<tr><td>{$counter}</td><td>";
			echo "<form method=\"get\">";
			echo escapeOutput($question['question']);
			echo "</td><td>";
			echo "<a href=\"?view=editQuestion&questionId=$id\">Edit</a> | ";
			echo "<a href=\"?view=deleteQuestion&questionId=$id\">Delete</a>";
			echo "</td></tr>";
			echo "</form>";
			$counter++;
		}
		echo "</table>";
	} else {
		echo "No search results found.";
	}
	?>
</div>
		