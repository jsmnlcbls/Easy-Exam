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
		<ul id = "main-menu">
			<li>Category
				<ul class = "sub-menu">
					<li><a href = "?view=addCategory">Add</a></li>
					<li><a href = "?view=editCategory">Edit</a></li>
					<li><a href = "?view=deleteCategory">Delete</a></li>
				</ul>
			</li>
			<li>Question
				<ul class = "sub-menu">
					<li><a href="?view=addQuestion">Add</a></li>
					<li><a href="?view=editQuestion">Edit</a></li>
					<li><a href="?view=deleteQuestion">Delete</a></li>
					<li><a href="?view=searchQuestion">Search</a></li>
				</ul>
			</li>
			<li>Settings</li>
		</ul>
	</div>
	<div id = "main-panel">
		<?php
		if ($view == "addCategory") {
			include "views/addCategory.php";
		} else if ($view == "success") {
			echo "<h2>Success!</h2>";
		} else if ($view == "error") {
			echo "<h2>Error. Please try again.</h2>";
		}
		?>
	</div>
</body>
</html> 