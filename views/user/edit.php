<?php
include "functions/user.php";

$id = getUrlQuery('id');
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
				<td>Group</td>
				<td id="user-group-container">
					<?php
					$count = 0;
					foreach ($data['group'] as $groupId) {
						$button = '';
						$attributes = array();
						if ($count == 0) {
							$attributes = array('name' => 'group[]', 'selected' => $groupId, 'id' => 'initial-user-group');
							$button = '<img src ="images/add_group.png" id ="add-more-group-button"></img>'; 
						} else {
							$attributes = array('name' => 'group[]', 'selected' => $groupId);
							$button = '<img class = "delete-group-button" src = "images/delete_group.png"></img>';
						}
						echo '<div class="user-group-div">';
						echo userGroupSelectHTML($attributes);
						echo "\n";
						echo $button;
						echo '<div>';
						$count++;
					}
					?>
				</td>
			</tr>
			<tr>
				<td>User Name</td>
				<td><input type = "text" name = "name" value = "<?php echo escapeOutput($data['name']);?>"/></td>
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
<script>
	$(function(){
		$("#add-more-group-button").click(function(){
			var select = $('#initial-user-group').clone().removeAttr('id');
			select.children().removeAttr('selected');
			var container = $('#user-group-container').append('<div class = "user-group-div">');
			var button = '<img class = "delete-group-button" src = "images/delete_group.png"></img>';
			container.children().last().append(select).append("\n").append(button);
		});
		
		$(".delete-group-button").live('click', function(){
			$(this).parent('.user-group-div').remove();
		});
	});
</script>
		