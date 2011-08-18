
<div id = "select-category-panel">
	<span class = "panel-title">Select Category To Edit</span>
	<form method = "get" action = "admin.php" id = "edit-category-form">
		<input type = "hidden" name = "view" value = "editCategory"/>
		<table>
			<tr>
				<td>Category Name</td>
				<td>
					<select name = "category">
					<?php
						$categories = (getAllCategories());
						foreach ($categories as $category) {
							$categoryId = $category['category_id'];
							if ($categoryId == 0) {
								echo '<option value = "0">None Selected</option>';
							} else {
								$name = escapeOutput($category['name']);
								echo "<option value = \"{$categoryId}\">{$name}</option>";
							}
						}
					?>
					</select>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><button>Edit</button></td>
			</tr>
		</table>
	</form>
</div>
		