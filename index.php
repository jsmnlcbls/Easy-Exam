<?php
include 'common.php';
?>
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
		<div id = "take-exam-panel">
		<?php
			if (isset($_GET['category'])) {
				include 'question.php';
				
				$category = intval($_GET['category']);
				$questions = getQuestions($category, 10);
				
				$questionNumber = 1;
				foreach ($questions as $value) {
					$question = $value['question'];
					$questionId = $value['question_id'];
					$answer = $value['answer'];
					$choices = array('A' => $value['choiceA'], 
									 'B' => $value['choiceB'],
									 'C' => $value['choiceC'],
									 'D' => $value['choiceD'],
									 'E' => $value['choiceE']);
					
					$choicesList = "";
					for ($a = 0; $a < 5; $a++) {
						$key = array_rand($choices);
						if ("" != $choices[$key]) {
							$choicesList .= "<input type = \"radio\" name = \"choice_{$questionId}\" value = \"$key\">";
							$choicesList .= $choices[$key] ."<br/>";
						}
						unset($choices[$key]);
					}
							
					$output = "";
					$output .= '<div class = "question-div">';
					$output .= '<div class = "question">';
					$output .= $questionNumber. ".&nbsp;" . $question;
					$output .= '</div>';
					$output .= '<div class = "choices">';
					$output .= $choicesList;
					$output .= '</div>';
					$output .= '</div>';
					echo $output;
					$questionNumber++;
				}
			}
		?>	
		</div>
	</div>
</body>
</html> 