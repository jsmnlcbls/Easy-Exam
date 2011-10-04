<?php
include "functions/user.php";
?>
<div id = "add-user-panel">
	<span class = "panel-title">Add User</span>
	<form method = "post" action = "admin.php" id = "add-user-form">
		<table>
			<tr>
				<td>Group</td>
				<td id="user-group-container">
					<div class="user-group-div">
					<?php
					$attributes = array('name' => 'group[]', 'id' => 'initial-user-group');
					echo userGroupSelectHTML($attributes); 
					?>
					<img src ="images/add_group.png" id ="add-more-group-button"></img>
					</div>
				</td>
			</tr>
			<tr>
				<td>User Name</td>
				<td><input type = "text" name = "name" /></td>
			</tr>
			<tr>
				<td>Role</td>
				<td>
				<?php
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
<script>
	$(function(){
		$("#add-more-group-button").click(function(){
			var select = $('#initial-user-group').clone().removeAttr('id');
			var container = $('#user-group-container').append('<div class = "user-group-div">');
			var button = '<img class = "delete-group-button" src = "images/delete_group.png"></img>';
			container.children().last().append(select).append("\n").append(button);
		});
		
		$(".delete-group-button").live('click', function(){
			$(this).parent('.user-group-div').remove();
		});
	});
</script>