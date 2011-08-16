<?php

function addCategory($data)
{
	$sql = "INSERT INTO category (name, parent_category, menu_visibility) VALUES (:name, :parent, :menuVisibility);";
	$parameters = array(':name' => $data['name'], ':parent' => $data['parent'], 
						':menuVisibility' => $data['showOnMenu']);
	return executeDatabase($sql, $parameters);
}

function editCategory($id, $data)
{
	$sql = "UPDATE category SET name=:name, parent_category=:parentCategory, menu_visibility=:menuVisibility WHERE category_id=:id;";
	$parameters = array(':name' => $data['name'], 
						':parentCategory' => $data['parent'],
						':menuVisibility' => $data['menuVisibility'],
						':id' => $id);
	return executeDatabase($sql, $parameters);
}