<?php
include "functions/user.php";

$id = getUrlQuery('id');
$data = getUserData($id);
$role = getLoggedInUser('role');
?>
<div id = "edit-user-panel">
	<span class = "panel-title">Edit User</span>
	<form method = "post" action = "admin.php" id = "edit-user-form">
		<input type = "hidden" name = "action" value = "editUser" />
		<input type = "hidden" name = "owner" value = "<?php echo $data['owner']; ?>" />
		<?php
		echo "<input type = \"hidden\" name = \"id\" value = \"{$data['id']}\">";
		?>
		<table>
			<?php
			if ($role == ADMINISTRATOR_ROLE) {
				$owner = getUserData($data['owner'], array('name'));
				echo "<tr><td>Owner</td><td>{$owner['name']}</td></tr>";
			}
			?>
			<tr>
				<td>Group</td>
				<td>
					<?php
					foreach ($data['group'] as $groupId) {
						$button = '';
						$attributes = array('name' => 'group[]', 'selected' => $groupId);
						echo userGroupSelectHTML($attributes, $data['owner']);
						echo "\n";
						echo $button;
					}
					?>
					<script>
						$("select[name='group[]']").userGroupChoice();
					</script>
				</td>
			</tr>
			<tr>
				<td>User Name</td>
				<td><input type = "text" name = "name" value = "<?php echo escapeOutput($data['name']);?>"/></td>
			</tr>
			<?php 
			
			if ($role == ADMINISTRATOR_ROLE) {
				echo '<tr>';
				echo '<td>Role</td>';
				echo '<td>';		
				$roles = getAllRoles();
				foreach ($roles as $id => $name) {
					if ($id == $data['role']) {
						echo "<input checked=\"checked\" type = \"radio\" name = \"role\" value = \"{$id}\">";
					} else {
						echo "<input type = \"radio\" name = \"role\" value = \"{$id}\">";
					}
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
					<br/>
					<span style = "color:green; font-size: 80%">A non-empty value will overwrite the old password</span>
				</td>
			</tr>
			<tr>
				<td>Other Info</td>
				<td>
					<textarea name="other_info"><?php echo $data['other_info']; ?></textarea>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Save"/></td>
			</tr>
		</table>
	</form>
</div>
		