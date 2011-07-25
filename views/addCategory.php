
<div id = "add-category-panel">
	<span class = "panel-title">New Category For Questions</span>
	<form method = "post" action = "admin.php" id = "add-category-form">
		<table>
			<tr>
				<td>Category Name</td>
				<td><input type = "text" name = "categoryName" /></td>
			</tr>
			<tr>
				<td>Parent Category</td>
				<td>
				<select name = "parentCategory">
					<?php
						$categories = (getAllCategories());
							foreach ($categories as $category) {
							if ($category['category_id'] == 0) {
								echo '<option value = "0">None Selected</option>';
							} else {
								echo "<option value = \"{$category['category_id']}\">{$category['name']}</option>";
							}
						}
					?>
				</select>
				</td>
			</tr>
			<tr>
				<td>Options</td>
				<td>
					<input type ="checkbox" name ="menuVisibility" value="1" checked="checked">Show on menu *<br/>
					<span style="color:GREEN; font-size:80%">
						*This category will be visible on the main menu,<br/>
						and it will be shown under its parent category.</span>
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
		