<?php
include "functions/user.php";

$id = getUrlQuery('id');
$data = getUserData($id);
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
			<tr>
				<td>Group</td>
				<td>
					<?php
					foreach ($data['group'] as $groupId) {
						$button = '';
						$attributes = array('name' => 'group[]', 'selected' => $groupId);
						echo userGroupSelectHTML($attributes);
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
			$role = getLoggedInUser('role');
			if (isset($role[ADMINISTRATOR_ROLE])) {
				echo '<tr>';
				echo '<td>Role</td>';
				echo '<td>';		
				$roles = getAllRoles();
				foreach ($roles as $id => $name) {
						echo "<input type = \"checkbox\" name = \"role[]\" value = \"{$id}\">";
						echo escapeOutput($name);
						echo "<br/>";
				}
				echo '</td>';
				echo '</tr>';
			} elseif(isset($role[EXAMINER_ROLE])) {
				echo '<input type="hidden" name="role[]" value="' . EXAMINEE_ROLE . '"/>';
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
		