<?php

function addCategory($data)
{
	$sql = "INSERT INTO category (name, parent_category) VALUES (:name, :parent);";
	$parameters = array(':name' => $data['name'], ':parent' => $data['parent']);
	return executeDatabase($sql, $parameters);
}

function editCategory($id, $data)
{
	$sql = "UPDATE category SET name=:name, parent_category=:parentCategory WHERE category_id=:id;";
	$parameters = array(':name' => $data['name'], 
						':parentCategory' => $data['parent'],
						':id' => $id);
	return executeDatabase($sql, $parameters);
}