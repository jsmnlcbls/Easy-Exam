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

function editCategory($id, $name, $parent)
{
	$database = getDatabase();
	$statement = $database->prepare("UPDATE category SET name=:name, parent_category=:parentCategory WHERE category_id=:id;");
	$statement->bindValue(":name", $name);
	$statement->bindValue(":parentCategory", $parent);
	$statement->bindValue(":id", $id);
	
	$result = @$statement->execute();
	if ($result === false) {
		return false;
	}
	return true;
}

function deleteCategory($id)
{
	$database = getDatabase();
	$statement = $database->prepare("DELETE FROM category WHERE category_id=:id;");
	$statement->bindValue(":id", $id);
	
	$result = @$statement->execute();
	if ($result === false) {
		return false;
	}
	return true;
}