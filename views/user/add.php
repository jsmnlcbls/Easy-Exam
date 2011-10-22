<?php
include "functions/user.php";
?>
<div id = "add-user-panel">
	<span class = "panel-title">Add User</span>
	<form method = "post" action = "admin.php" id = "add-user-form">
		<table>
			<tr>
				<td>Group</td>
				<td>
					<?php
					$attributes = array('name' => 'group[]', 'id' => 'initial-user-group');
					echo userGroupSelectHTML($attributes, getLoggedInUser('id')); 
					?>
					<script>
						$('#initial-user-group').userGroupChoice();
					</script>
				</td>
			</tr>
			<tr>
				<td>User Name</td>
				<td><input type = "text" name = "name" /></td>
			</tr>
			<?php 
			$role = getLoggedInUser('role');
			if ($role == ADMINISTRATOR_ROLE) {
				echo '<tr>';
				echo '<td>Role</td>';
				echo '<td>';		
				$roles = getAllRoles();
				foreach ($roles as $id => $name) {
						echo "<input type = \"radio\" name = \"role\" value = \"{$id}\">";
						echo escapeOutput($name);
						echo "<br/>";
				}
				echo '</td>';
				echo '</tr>';
			}
			?>
			<tr>
				<td>Password</td>
				<td>
					<input type = "text" name = "password"/>
				</td>
			</tr>
			<tr>
				<td>Other Info</td>
				<td>
					<textarea name="other_info"></textarea>
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