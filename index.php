<!DOCTYPE HTML>
<html>
	<head>
		<script src = "jquery-1.5.1.js"></script>
		<link type="text/css" href="style.css" rel="stylesheet" />
		<title>Easy Exam</title>
	</head>
<body>
	<div id ="header">
		<span id ="program-name">Easy Exam</span> Your Do It Yourself Multiple Choice Exam Review!
	</div>
	<div id = "menu-panel">
		<?php
			include "common.php";
			
			$categories = getAllCategories();
			
			function printMenu(&$menu, $parent)
			{
				$listItems = "";
				foreach ($menu as $key => $value) {
					if ($parent == $value['parent_category'] &&
						"" != $value['name']) {
						$listItems .= "<li catId = {$value['category_id']} parent = {$value['parent_category']}>";
						$listItems .= $value['name'];
						unset ($menu[$key]);
						$listItems .= printMenu($menu, $value['category_id']);
						echo "</li>";
					}
				}
				if ("" != $listItems) {
					$listItems = "<ul>" . $listItems . "</ul>";
				}
				return $listItems;
			}
			echo printMenu($categories, 0);
		?>
	</div>
	<div id = "main-panel">
		<div id = "take-exam-panel"></div>
	</div>
</body>
</html> 