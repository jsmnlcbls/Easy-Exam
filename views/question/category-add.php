
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
				echo questionCategorySelectHTML(array('name' => 'parent_category'));
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
		