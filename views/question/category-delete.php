
<div id = "select-category-panel">
	<span class = "panel-title">Select Category To Delete</span>
	<form method = "post" action = "admin.php" id = "edit-category-form">
		<input type = "hidden" name = "action" value = "deleteCategory"/>
		<table>
			<tr>
				<td>Category Name</td>
				<td>
					<select name = "category">
					<?php
						$categories = getAllCategories();
						foreach ($categories as $category) {
							$name = escapeOutput($category['name']);
							if ($category['category_id'] == 0) {
								echo '<option value = "0">None Selected</option>';
							} else {
								echo "<option value = \"{$category['category_id']}\">{$name}</option>";
							}
						}
					?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan = "2">
				<span style ="color:RED; font-style: italic; font-size: 80%">
					Warning: Delete is irrevocable. <br/> Proceed only if you are sure.
				</span>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Delete"/></td>
			</tr>
		</table>
	</form>
</div>
		