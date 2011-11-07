<?php
include "functions/user.php";
$id = getUrlQuery('user-group-id');
$data = escapeOutput(getUserGroupData($id));
?>
<div id = "edit-user-group-panel">
	<span class = "panel-title">Edit User Group</span>
	<form method = "post" action = "admin.php">
		<input type = "hidden" name = "action" value = "editUserGroup" />
		<input type = "hidden" name = "group_id" value = "<?php echo $data['group_id']; ?>" />
		<input type = "hidden" name = "owner" value = "<?php echo $data['owner']; ?>" />
		<table>
			<tr>
				<td>Name</td>
				<td><input type = "text" name = "name" value = "<?php echo $data['name']; ?>"/></td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Update"/></td>
			</tr>
		</table>
	</form>
</div>