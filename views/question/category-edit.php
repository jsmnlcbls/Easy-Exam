<?php
$categoryId = getQuery('category', '');
?>
<div id = "select-category-to-edit-panel">
	<span class = "panel-title">Edit Question Category</span>
	<form method = "get" action = "admin.php" id = "edit-category-form">
		<input type = "hidden" name = "view" value = "question-category-edit"/>
		<div>
			Category Name <?php echo questionCategorySelectHTML(array('selected' => $categoryId)); ?> <button>Edit</button>
		</div>
	</form>
	<hr/>
		<?php
		if (!empty($categoryId)) {
			$arguments = array();
			$arguments['data'] = getCategoryData($categoryId);
			echo renderView('question-category-update', $arguments);
		}
		?>
</div>