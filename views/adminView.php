<!DOCTYPE HTML>
<html>
	<head>
		<script src = "jquery-1.5.1.js"></script>
		<link type="text/css" href="style.css" rel="stylesheet" />
		<title>Administration</title>
	</head>
<body>
	<div id ="header">
		<span id ="program-name">Easy Exam</span> Administration Panel. Configure settings, add questions etc...
	</div>
	<div id = "left-panel">
	<?php
	if (isset($isInstalled) && $isInstalled) {
		echo renderView("views/adminMenu.php");
	}
	?>
	</div>
	<div id = "main-panel">
		<?php
		$viewFile = array(
			'addCategory' => 'addCategory.php', 
			'selectCategory' => 'selectCategory.php',
			'editCategory' => 'editCategory.php',
			'deleteCategory' => 'deleteCategory.php',
			'addQuestion' => 'addQuestion.php',
			'searchQuestion' => 'searchQuestion.php',
			'editQuestion' => 'editQuestion.php',
			'editExamQuestion' => 'editQuestion.php',
			'searchResultsQuestion' => 'searchResultsView.php',
			'addExam' => 'addExam.php',
			'selectExam' => 'selectExam.php',
			'deleteQuestion' => 'deleteQuestion.php',
			'deleteExam' => 'deleteExam.php',
			'addUser' => 'addUser.php',
			'listUsers' => 'listUsers.php',
			'editUser' => 'editUser.php',
			'deleteUser' => 'deleteUser.php',
		);
		
		if (isset($isInstalled) && !$isInstalled) {
			echo renderView("views/installView.php");
		}elseif ($view == "editExam") {
			$examView = filterGet("examView");
			if ($examView == "properties") {
				include "views/editExam.php";
			} else if ($examView == "questions") {
				include "views/editExamQuestions.php";
			}
		} elseif ($view == "success") {
			echo "<h2>Success!</h2>";
		} elseif ($view == "error") {
			echo "<h2>Error. Please try again.</h2>";
		} elseif (isset($viewFile[$view])) {
			include "views/" . $viewFile[$view];
		}
		?>
	</div>
</body>
</html> 