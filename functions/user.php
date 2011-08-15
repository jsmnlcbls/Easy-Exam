<?php

function getAllRoles()
{
	$sql = "SELECT * FROM role";
	$database = getDatabase();
	$source = $database->query($sql);
	$data = array();
	while ($row = $source->fetch(PDO::FETCH_ASSOC)) {
		$data[$row['id']] = $row['name'];
	}
	return $data;
}

function getAllUsers()
{
	$sql = "SELECT id, name, role FROM accounts";
	return queryDatabase($sql);
}

function addUser($data)
{
	$role = 0;
	foreach ($data['role'] as $value) {
		$role |= $value;
	}
	
	$rand = mt_rand(100000, 999999);
	$salt = substr(md5($rand), 0, 16);
	$password = sha1($salt . $data['password']);
	$sql = "INSERT INTO accounts (name, role, password, salt) VALUES (:name, :role, :password, :salt)";
	$parameters = array(':name' => $data['name'], ':role' => $role, 
						':password' => $password, ':salt' => $salt);
	
	$result = executeDatabase($sql, $parameters);
	if ($result !== false) {
		return true;
	} else {
		return false;
	}
}