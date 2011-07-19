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
			$categories = getAllCategories();
			
			function printMenu(&$menu, $parent)
			{
				$listItems = "";
				foreach ($menu as $key => $value) {
					if ($parent == $value['parent_category'] &&
						"" != $value['name']) {
						$categoryId = $value['category_id'];
						$listItems .= "<li catId = {$categoryId} parent = {$value['parent_category']}>";
						$listItems .= "<a href = \"?category={$categoryId}\">" . $value['name'] . "</a>";
						unset ($menu[$key]);
						$listItems .= printMenu($menu, $categoryId);
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
		<?php
		if ($view == "questions") {
			include "/views/questionsView.php";
		} else if ($view == "results") {
			echo "<h2>Your Score: $score %</h2>";
		}
		?>
	</div>
</body>
</html>