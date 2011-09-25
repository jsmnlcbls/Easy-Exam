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
	$data['role'] = _deriveRole($data['role']);
	$passwordData = _derivePassword($data['password']);
	$data['password'] = $passwordData['hash'];
	$data['salt'] = $passwordData['salt'];
	
	return insertIntoTable('accounts', $data);
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
	$data['role'] = _deriveRole($data['role']);
	if ($data['password'] != "") {
		$passwordData = _derivePassword($data['password']);
		$data['password'] = $passwordData['hash'];
		$data['salt'] = $passwordData['salt'];
		return updateTable('accounts', $data, "id = :id", array(':id' => $id));
	} else {
		return updateTable('accounts', $data, "id = :id", array(':id' => $id));
	}
}

function deleteUser($id)
{
	$sql = "DELETE FROM accounts WHERE id = :id";
	$parameters = array(':id' => $id);
	return executeDatabase($sql, $parameters);
}

function getAccountsTableColumns($includePrimaryKeys = false)
{	
	if ($includePrimaryKeys) {
		return array('id', 'role', 'name', 'password');
	} else {
		return array('role', 'name', 'password');
	}
}

function _deriveRole($roleArray)
{
	$role = 0;
	foreach ($roleArray as $value) {
		$role |= $value;
	}
	return $role;
}

function _derivePassword($plainText)
{
	$rand = mt_rand(100000, 999999);
	$salt = substr(md5($rand), 0, 16);
	$password = _hashPassword($plainText, $salt);
	return array('hash' => $password, 'salt' => $salt);
}
