<?php
include "functions/user.php";
$id = getUrlQuery('id');
$data = escapeOutput(getUserGroupData($id));
?>
<div id = "edit-user-group-panel">
	<span class = "panel-title">Delete User Group</span>
	<form method = "post" action = "admin.php">
		<input type = "hidden" name = "action" value = "deleteUserGroup" />
		<input type = "hidden" name = "group_id" value = "<?php echo $data['group_id']; ?>" />	
		<table>
			<tr>
				<td>Name: </td>
				<td><strong><?php echo $data['name']; ?></strong></td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Delete"/></td>
			</tr>
		</table>
	</form>
</div>