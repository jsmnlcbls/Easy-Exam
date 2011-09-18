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
		if (isset($menu)) {
			echo $menu;
		}
	?>
	</div>
	<div id = "main-panel">
		<?php
		if (isset($mainPanel)) {
			echo $mainPanel;
		} elseif (isset($view) && $view == "success") {
			echo "<h2>Success!</h2>";
		} elseif (isset($view) && $view == "error") {
			echo "<h2>Error. Please try again.</h2>";
		}
		?>
	</div>
</body>
</html> 