<div id = "list-user-panel">
	<span class = "panel-title">List Of Users</span>
	<?php
	include "functions/user.php";
	
	$role = getLoggedInUser('role');
	$userId = getLoggedInUser('id');
	$rolesList = getAllRoles();
	$userGroups = getAllUserGroups($userId);
	$usersList = getAllUsers($userId);
	$usersCount = count($usersList);
	if ($usersCount > 0) {
		$out = '';
		if ($role == ADMINISTRATOR_ROLE) {
			$out = "<table><tr><th>Name</th><th>Role</th><th>Group</th><th></th></tr>";
		} elseif ($role == EXAMINER_ROLE) {
			$out = "<table><tr><th>Name</th><th>Group</th><th></th></tr>";
		}
		foreach ($usersList as $value) {
			$group = array();
			foreach ($value['group'] as $id) {
				if (isset($userGroups[$id]['name'])) {
					$group[] = $userGroups[$id]['name'];
				}
			}
			$name = $value['name'];
			$groupNames = implode(', ', $group);
			$script = $_SERVER['PHP_SELF'];
			$editLink = "<a href = \"$script?view=user-edit&id={$value['id']}\">Edit</a>";
			$deleteLink = "<a href = \"$script?view=user-delete&id={$value['id']}\">Delete</a>";
			
			if ($role == ADMINISTRATOR_ROLE) {
				$roleName = $rolesList[$value['role']];
				$out .= "<tr><td>{$name}</td><td>{$roleName}</td><td>{$groupNames}</td><td>$editLink | $deleteLink</td></tr>";
			} elseif ($role == EXAMINER_ROLE) {
				$out .= "<tr><td>{$name}</td><td>{$groupNames}</td><td>$editLink | $deleteLink</td></tr>";
			}
		}
		$out .= "</table>";
		echo $out;
	}
	?>
</div>
		