<?php

function addCategory($data)
{
	$name = $data['name'];
	$parent = $data['parent'];
	$menuVisibility = $data['showOnMenu'];
	
	$database = getDatabase();
	$statement = $database->prepare("INSERT INTO category (name, parent_category, menu_visibility) VALUES (:name, :parent, :menuVisibility);");
	$statement->bindValue(":name", $name);
	$statement->bindValue(":parent", $parent);
	$statement->bindValue(":menuVisibility", $menuVisibility);
	$result = @$statement->execute();
	if ($result === false) {
		return false;
	}
	return true;
}

function editCategory($id, $data)
{
	$name = $data['name'];
	$parent = $data['parent'];
	$menuVisibility = $data['menuVisibility'];
	
	$database = getDatabase();
	$statement = $database->prepare("UPDATE category SET name=:name, parent_category=:parentCategory, menu_visibility=:menuVisibility WHERE category_id=:id;");
	$statement->bindValue(":name", $name);
	$statement->bindValue(":parentCategory", $parent);
	$statement->bindValue(":id", $id);
	$statement->bindValue(":menuVisibility", $menuVisibility);
	
	$result = @$statement->execute();
	if ($result === false) {
		return false;
	}
	return true;
}