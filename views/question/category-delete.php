
<div id = "select-category-panel">
	<span class = "panel-title">Select Category To Delete</span>
	<form method = "post" action = "admin.php" id = "edit-category-form">
		<input type = "hidden" name = "action" value = "deleteQuestionCategory"/>
		<table>
			<tr>
				<td>Category Name</td>
				<td>
					<?php echo questionCategorySelectHTML(); ?>
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
		