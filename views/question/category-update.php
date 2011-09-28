<div id = "update-category-panel">
	<form method = "post" action = "admin.php" id = "edit-category-form">
		<input type = "hidden" name = "action" value = "editQuestionCategory" />
		<table>
			<tr>
				<td>New Category Name</td>
				<td>
				<?php
					echo "<input type = \"hidden\" name = \"category_id\" value = \"{$data['category_id']}\">";
					echo "<input type = \"text\" name = \"name\" value = \"{$data['name']}\"/>";
				?>
				</td>
			</tr>
			<tr>
				<td>New Parent Category</td>
				<td>
					<?php
					$attributes = array('name' => 'parent_category', 'selected' => $data['parent_category']);
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
		