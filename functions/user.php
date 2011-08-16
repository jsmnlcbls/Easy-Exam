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
	$sql = "SELECT id, name, role FROM accounts ORDER BY name ASC";
	return queryDatabase($sql);
}

function addUser($data)
{
	$role = _deriveRole($data['role']);
	$passwordData = _derivePassword($data['password']);
	$sql = "INSERT INTO accounts (name, role, password, salt) VALUES (:name, :role, :password, :salt)";
	$parameters = array(':name' => $data['name'], ':role' => $role, 
						':password' => $passwordData['hash'], ':salt' => $passwordData['salt']);
	
	$result = executeDatabase($sql, $parameters);
	if ($result !== false) {
		return true;
	} else {
		return false;
	}
}

function getUserData($id)
{
	$sql = "SELECT * FROM accounts WHERE id = :id";
	$parameters = array(':id' => $id);
	$result = queryDatabase($sql, $parameters);
	if (is_array($result)) {
		return array_shift($result);
	}
	return false;
}

function updateUser($id, $data)
{
	$sql = "";
	$role = _deriveRole($data['role']);
	$passwordData = _derivePassword($data['password']);
	$parameters = array(':id' => $id, ':name' => $data['name'], ':role' => $role);
	if ($data['password'] != "") {
		$sql = "UPDATE accounts SET name = :name, role = :role, password = :password, salt = :salt WHERE id = :id";
		$parameters[':password'] = $passwordData['hash'];
		$parameters[':salt'] = $passwordData['salt'];
	} else {
		$sql = "UPDATE accounts SET name = :name, role = :role WHERE id = :id";
	}
	$result = executeDatabase($sql, $parameters);
	if ($result !== false) {
		return true;
	}
	return false;
}

function deleteUser($id)
{
	$sql = "DELETE FROM accounts WHERE id = :id";
	$parameters = array(':id' => $id);
	$result = executeDatabase($sql, $parameters);
	if ($result !== false) {
		return true;
	}
	return false;
}

function _deriveRole($roleArray)
{
	$role = 0;
	foreach ($roleArray as $value) {
		$role |= $value;
	}
	return $role;
}

function _derivePassword($clearText)
{
	$rand = mt_rand(100000, 999999);
	$salt = substr(md5($rand), 0, 16);
	$password = sha1($salt . $clearText);
	return array('hash' => $password, 'salt' => $salt);
}