<div id = "update-category-panel">
	<form method = "post" action = "admin.php" id = "edit-category-form">
		<input type = "hidden" name = "action" value = "editCategory" />
		<table>
			<tr>
				<td>New Category Name</td>
				<td>
				<?php
					echo "<input type = \"hidden\" name = \"categoryId\" value = \"{$data['category_id']}\">";
					echo "<input type = \"text\" name = \"categoryName\" value = \"{$data['name']}\"/>";
				?>
				</td>
			</tr>
			<tr>
				<td>New Parent Category</td>
				<td>
					<?php
					$attributes = array('name' => 'parentCategory', 'selected' => $data['parent_category']);
					echo questionCategorySelectHTML($attributes); 
					?>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Save"/></td>
			</tr>
		</table>
	</form>
</div>
		