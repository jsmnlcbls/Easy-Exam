<div id = "list-user-group-panel">
	<span class = "panel-title">List Of User Groups</span>
	<?php
	include "functions/user.php";
	
	$groups = getAllUserGroups();
	$out = "<table><tr><th>Name</th><th>Actions</th></tr>";
	foreach ($groups as $value) {
		$value = escapeOutput($value);
		$script = $_SERVER['PHP_SELF'];
		$id = $value['group_id'];
		$editLink = "<a href = \"$script?view=user-group-edit&id={$id}\">Edit</a>";
		$deleteLink = "<a href = \"$script?view=user-group-delete&id={$id}\">Delete</a>";
		$out .= "<tr><td>{$value['name']}</td><td>$editLink | $deleteLink</td></tr>";
	}
	$out .= "</table>";
	echo $out;
	?>
</div>
		