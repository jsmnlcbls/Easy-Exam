<?php
$categoryId = getUrlQuery('question-category-id', '');
?>
<div id = "select-category-to-edit-panel">
	<span class = "panel-title">Edit Question Category</span>
	<form method = "get" action = "admin.php" id = "edit-category-form">
		<input type = "hidden" name = "view" value = "question-category-edit"/>
		<div>
			Category Name 
			<?php
			$attributes = array('name' => 'question-category-id', 
								'selected' => $categoryId,
								'blankOption' => true);
			$owner = getLoggedInUser('id');
			echo questionCategorySelectHTML($attributes, $owner); 
			?>
			<button>Edit</button>
		</div>
	</form>
	<hr/>
		<?php
		if (!empty($categoryId)) {
			include "functions/question.php";
			$arguments = array();
			$arguments['data'] = getQuestionCategoryData($categoryId);
			echo renderView('question-category-update', $arguments);
		}
		?>
</div>