<?php
$data = getCategoryData(intval(filterGET("category")));
$categoryName = escapeOutput($data['name']);
$categoryId = intval($data['category_id']);
$parentId = intval($data['parent_category']);
?>
<div id = "edit-category-panel">
	<span class = "panel-title">Edit Category: <em><?php echo $categoryName;?></em></span>
	<form method = "post" action = "admin.php" id = "add-category-form">
		<input type = "hidden" name = "action" value = "editCategory" />
		<table>
			<tr>
				<td>New Category Name: </td>
				<td>
				<?php
					echo "<input type = \"hidden\" name = \"categoryId\" value = \"{$categoryId}\">";
					echo "<input type = \"text\" name = \"categoryName\" value = \"{$categoryName}\"/>";
				?>
				</td>
			</tr>
			<tr>
				<td>New Parent Category</td>
				<td>
				<select name = "parentCategory">
					<?php
						$categories = getAllCategories();
						foreach ($categories as $category) {
							$name = escapeOutput($category['name']);
							if ($category['category_id'] == 0) {
								echo '<option value = "0">None Selected</option>';
							} else if ($category['category_id'] == $parentId) {
								echo "<option value = \"{$category['category_id']}\" selected=\"selected\">{$name}</option>";
							}else {
								echo "<option value = \"{$category['category_id']}\">{$name}</option>";
							}
						}
					?>
				</select>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Save"/></td>
			</tr>
		</table>
	</form>
</div>
		