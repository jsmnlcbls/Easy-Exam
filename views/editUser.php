<?php
include "functions/user.php";

$id = intval(filterGET('id'));
$data = getUserData($id);
?>
<div id = "edit-user-panel">
	<span class = "panel-title">Edit User</span>
	<form method = "post" action = "admin.php" id = "edit-user-form">
		<input type = "hidden" name = "action" value = "editUser" />		
		<?php
		echo "<input type = \"hidden\" name = \"id\" value = \"{$data['id']}\">";
		?>
		<table>
			<tr>
				<td>User Name</td>
				<td><input type = "text" name = "username" value = "<?php echo escapeOutput($data['name']);?>"/></td>
			</tr>
			<tr>
				<td>Role</td>
				<td>
				<?php
					$roles = getAllRoles();
					foreach ($roles as $id => $name) {
						if ($data['role'] & $id) {
							echo "<input checked = \"checked\" type = \"checkbox\" name = \"role[]\" value = \"{$id}\">";
						} else {
							echo "<input type = \"checkbox\" name = \"role[]\" value = \"{$id}\">";
						}
						echo escapeOutput($name);
						echo "<br/>";
					}
				?>
				</td>
			</tr>
			<tr>
				<td>Password</td>
				<td>
					<input type = "text" name = "password"/>
					<br/>
					<span style = "color:green; font-size: 80%">A non-empty value will overwrite the old password</span>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Save"/></td>
			</tr>
		</table>
	</form>
</div>
		