<?php
include "functions/user.php";

$id = getUrlQuery('id');
$data = getUserData($id);
?>
<div id = "delete-user-panel">
	<span class = "panel-title">Confirm User Removal</span>
	<form method = "post" action = "admin.php" id = "delete-user-form">
		<input type = "hidden" name = "action" value = "deleteUser" />
		<?php
		echo "<input type =\"hidden\" name = \"id\" value =\"$id\">";
		?>
		<table>
			<tr>
				<td>User Name</td>
				<td><strong><?php echo escapeOutput($data['name']);?></strong></td>
			</tr>
			<tr>
				<td>Role</td>
				<td>
				<strong>
				<?php
					$roles = getAllRoles();
					foreach ($roles as $id => $name) {
						if ($data['role'] & $id) {
							echo escapeOutput($name);
							echo "<br/>";
						}
					}
				?>
				</strong>
				</td>
			</tr>
			<tr>
				<td></td>
				<td style = "color:red; font-size: 80%; font-style: italic">
					* Removing this user will also remove
					<br/>any data associated with this account.
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Delete"/></td>
			</tr>
		</table>
	</form>
</div>
		