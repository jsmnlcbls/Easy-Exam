
<div id = "add-category-panel">
	<span class = "panel-title">New Category For Questions</span>
	<form method = "post" action = "admin.php" id = "add-category-form">
		<table>
			<tr>
				<td>Category Name</td>
				<td><input type = "text" name = "name" /></td>
			</tr>
			<tr>
				<td>Parent Category</td>
				<td>
				<?php
				echo questionCategorySelectHTML(array('name' => 'parent'));
				?>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Add" name = "action"/></td>
			</tr>
		</table>
		<input type = "hidden" name = "action" value = "addCategory" />
	</form>
</div>
		