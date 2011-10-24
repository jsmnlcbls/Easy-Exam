
<div id = "add-category-panel">
	<span class = "panel-title">New Category For Questions</span>
	<form method = "post" action = "admin.php" id = "add-category-form">
		<input type = "hidden" name = "action" value = "addQuestionCategory" />
		<table>
			<tr>
				<td>Category Name</td>
				<td><input type = "text" name = "name" /></td>
			</tr>
			<tr>
				<td>Parent Category</td>
				<td>
				<?php
				$attributes = array('name' => 'parent_category', 
									'blankOption' => false,
									'selected' => 0);
				$owner = getLoggedInUser('id');
				echo questionCategorySelectHTML($attributes, $owner, true);
				?>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Add"/></td>
			</tr>
		</table>
	</form>
</div>
		