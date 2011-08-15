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
	<div id = "menu-panel">
		<span class="submenu-title">Questions</span>
		<ul class = "sub-menu">
			<li><a href = "?view=addCategory">Add Category</a></li>
			<li><a href = "?view=selectCategory">Edit Category</a></li>
			<li><a href = "?view=deleteCategory">Delete Category</a></li>
			<li><a href="?view=addQuestion">Add Question</a></li>
			<li><a href="?view=searchQuestion">Search Questions</a></li>
		</ul>
		<br/>
		<span class="submenu-title">Exam</span>
		<ul class = "sub-menu">
			<li><a href="?view=addExam">Add</a></li>
			<li><a href="?view=selectExam">Edit</a></li>
			<li><a href="?view=deleteExam">Delete</a></li>
		</ul>
		<br/>
		<span class="submenu-title">User</span>
		<ul class = "sub-menu">
			<li><a href="?view=addUser">Add</a></li>
			<li><a href="?view=listUsers">List</a></li>
		</ul>
		
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
			'editUser' => 'editUser.php'
		);
		
		if ($view == "editExam") {
			$examView = filterGet("examView");
			if ($examView == "properties") {
				include "views/editExam.php";
			} else if ($examView == "questions") {
				include "views/editExamQuestions.php";
			}
		} else if ($view == "success") {
			echo "<h2>Success!</h2>";
		} else if ($view == "error") {
			echo "<h2>Error. Please try again.</h2>";
		} else if (isset($viewFile[$view])) {
			include "views/" . $viewFile[$view];
		}
		?>
	</div>
</body>
</html> 