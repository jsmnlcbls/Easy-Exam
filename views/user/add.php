
<div id = "add-user-panel">
	<span class = "panel-title">Add User</span>
	<form method = "post" action = "admin.php" id = "add-user-form">
		<table>
			<tr>
				<td>User Name</td>
				<td><input type = "text" name = "name" /></td>
			</tr>
			<tr>
				<td>Role</td>
				<td>
				<?php
					include "functions/user.php";
					$roles = getAllRoles();
					foreach ($roles as $id => $name) {
						echo "<input type = \"checkbox\" name = \"role[]\" value = \"{$id}\">";
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
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Add" name = "action"/></td>
			</tr>
		</table>
		<input type = "hidden" name = "action" value = "addUser" />
	</form>
</div>
		