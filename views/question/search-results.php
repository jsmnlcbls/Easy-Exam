<div id = "search-results-panel">
	<span class = "panel-title">Search Results</span>
	<?php
	include "functions/question.php";
	
	$data = getUrlQuery(array('question-category-id', 'question', 'type'));
	$userRole = getLoggedInUser('role');
	if ($userRole != ADMINISTRATOR_ROLE) {
		$data['owner'] = getLoggedInUser('id');
	}
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
			$type = $question['type'];
			echo "<tr><td>{$counter}</td><td>";
			echo "<form method=\"get\">";
			echo escapeOutput($question['question']);
			echo "</td><td>";
			echo "<a href=\"?view=question-edit&question-id=$id&type=$type\">Edit</a> | ";
			echo "<a href=\"?view=question-delete&question-id=$id&type=$type\">Delete</a>";
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