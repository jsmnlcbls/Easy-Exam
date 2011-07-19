<?php

function addCategory($name, $parent = 0)
{
	$database = getDatabase();
	$statement = $database->prepare("INSERT INTO category (name, parent_category) VALUES (:name, :parent);");
	$statement->bindValue(":name", $name);
	$statement->bindValue(":parent", $parent);
	$result = @$statement->execute();
	if ($result === false) {
		return false;
	}
	return true;
}