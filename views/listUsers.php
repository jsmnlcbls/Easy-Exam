
<div id = "list-user-panel">
	<span class = "panel-title">List Of Users</span>
	<?php
	include "functions/user.php";
	
	$usersList = getAllUsers();
	$rolesList = getAllRoles();
	$usersCount = count($usersList);
	if ($usersCount > 0) {
		$out = "<table><tr><th>Name</th><th>Role</th><th>Actions</th></tr>";
		foreach ($usersList as $value) {
			$userRole = $value['role'];
			$role = array();
			foreach ($rolesList as $roleId => $roleName) {
				if ($roleId & $userRole) {
					$role[] = $roleName;
				}
			}
			$script = $_SERVER['PHP_SELF'];
			$editLink = "<a href = \"$script?view=editUser&id={$value['id']}\">Edit</a>";
			$deleteLink = "<a href = \"$script?view=deleteUser&id={$value['id']}\">Delete</a>";
			$out .= "<tr><td>{$value['name']}</td><td>".implode(", ", $role)."</td><td>$editLink | $deleteLink</td></tr>";
		}
		$out .= "</table>";
		echo $out;
	}
	?>
</div>
		