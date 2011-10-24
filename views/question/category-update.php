<?php
if (isset($data)) { ?>
<div id = "update-category-panel">
	<form method = "post" action = "admin.php" id = "edit-category-form">
		<input type = "hidden" name = "action" value = "editQuestionCategory" />
		<input type="hidden" name="owner" value="<?php echo $data['owner']; ?>"/>
		<input type="hidden" name="category_id" value="<?php echo $data['category_id']; ?>"/>
		<table>
			<tr>
				<td>New Category Name</td>
				<td>
					<input type="text" name="name" value="<?php echo $data['name']; ?>"/>
				</td>
			</tr>
			<tr>
				<td>New Parent Category</td>
				<td>
					<?php
					$attributes = array('name' => 'parent_category', 
										'selected' => $data['parent_category'],
										'blankOption' => false);
					$owner = getLoggedInUser('id');
					echo questionCategorySelectHTML($attributes, $owner, true); 
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
<?php } ?>