<!DOCTYPE HTML>
<html>
	<head>
		<script src = "jquery-1.5.1.js"></script>
		<link type="text/css" href="style.css" rel="stylesheet" />
		<title>Easy Exam</title>
	</head>
<body>
	<div id ="header">
		<span id ="program-name">Easy Exam</span>
		<span>Welcome <strong>
		<?php
		include "functions/user.php";
		$userData = getUserData(getLoggedInUser('id'));
		echo $userData['name'];
		?>
		</em></strong>
	</div>
	<div id = "left-panel">
		<span style="font-size:120%;color:GOLD">Available Exams</span>
		<ul>
		<?php
			$exams = getAvailableExams();
			foreach ($exams as $value) {
				$name = escapeOutput($value['name']);
				$id = $value['exam_id'];
				echo "<li><a href=\"index.php?exam=$id\">$name</a></li>";
			}
		?>
		</ul>
		<br/>
		<span style="font-size:120%;color:GOLD">My Account</span>
		<ul>
			<li>
				<form action = "login.php" method ="post" class="hidden-form">
					<input type = "hidden" name ="action" value ="logout"/>
					<button id ="logout-button">Logout</button>
				</form>
			</li>
		</ul>
	</div>
	<div id = "main-panel">
		<?php
		if (isset($innerView) && !empty($innerView)) {
			if ($innerView == "examQuestions" && isset($examData)) {
				echo renderView('user-questions', array('examData' => $examData));
			} else if ($innerView == "results" && isset($score)) {
				echo "<h2>Your Score: $score %</h2>";
			}
		}
		?>
	</div>
</body>
</html>