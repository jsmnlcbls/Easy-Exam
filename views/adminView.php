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
		
	</div>
	<div id = "main-panel">
		<?php
		if ($view == "addCategory") {
			include "views/addCategory.php";
		} else if ($view == "selectCategory") {
			include "views/selectCategory.php";
		} else if ($view == "editCategory") {
			include "views/editCategory.php";
		} else if ($view == "deleteCategory") {
			include "views/deleteCategory.php";
		}else if ($view == "addQuestion") { 
			include "views/addQuestion.php";
		} else if ($view == "searchQuestion") {
			include "views/searchQuestion.php";
		} else if ($view == "editQuestion" || $view == "editExamQuestion") {
			include "views/editQuestion.php";
		} else if ($view == "searchResultsQuestion") {
			include "views/searchResultsView.php";
		} else if ($view == "addExam") {
			include "views/addExam.php";
		} else if ($view == "selectExam") {
			include "views/selectExam.php";
		} else if ($view == "editExam") {
			$examView = filterGet("examView");
			if ($examView == "properties") {
				include "views/editExam.php";
			} else if ($examView == "questions") {
				include "views/editExamQuestions.php";
			}
		} else if ($view == "deleteQuestion") { 
			include "views/deleteQuestion.php";
		}else if ($view == "success") {
			echo "<h2>Success!</h2>";
		} else if ($view == "error") {
			echo "<h2>Error. Please try again.</h2>";
		}
		?>
	</div>
</body>
</html> 