<?php
const ACCOUNTS_TABLE = 'accounts';
const ROLE_TABLE = 'role';

function sanitizeAccountsData($rawData, $key = null)
{
	if (is_array($rawData)) {
		$sanitizedData = array();
		foreach ($rawData as $key => $value) {
			$sanitizedData[$key] = _sanitizeAccountsData($value, $key);
		}
		return $sanitizedData;
	} elseif (is_string($key)) {
		return _sanitizeAccountsData($rawData, $key);
	}
}

function getAllRoles()
{
	$sql = "SELECT * FROM " . ROLE_TABLE;
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
	$table = ACCOUNTS_TABLE;
	$sql = "SELECT id, name, role FROM {$table} ORDER BY name ASC";
	return queryDatabase($sql);
}

function addUser($data)
{
	$data = sanitizeAccountsData($data);
	$data['role'] = _deriveRole($data['role']);
	$passwordData = _derivePassword($data['password']);
	$data['password'] = $passwordData['hash'];
	$data['salt'] = $passwordData['salt'];
	
	return insertIntoTable(ACCOUNTS_TABLE, $data);
}

function getUserData($id)
{
	$id = sanitizeAccountsData($id, 'id');
	$table = ACCOUNTS_TABLE;
	$sql = "SELECT * FROM {$table} WHERE id = :id";
	$parameters = array(':id' => $id);
	$result = queryDatabase($sql, $parameters);
	if (is_array($result)) {
		return array_shift($result);
	}
	return false;
}

function updateUser($id, $data)
{
	$id = sanitizeAccountsData($id, 'id');
	$data = sanitizeAccountsData($data);
	$data['role'] = _deriveRole($data['role']);
	if ($data['password'] != "") {
		$passwordData = _derivePassword($data['password']);
		$data['password'] = $passwordData['hash'];
		$data['salt'] = $passwordData['salt'];
		return updateTable(ACCOUNTS_TABLE, $data, "id = :id", array(':id' => $id));
	} else {
		return updateTable(ACCOUNTS_TABLE, $data, "id = :id", array(':id' => $id));
	}
}

function deleteUser($id)
{
	$id = sanitizeAccountsData($id, 'id');
	$table = ACCOUNTS_TABLE;
	$sql = "DELETE FROM {$table} WHERE id = :id";
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

function _sanitizeAccountsData($rawData, $key)
{
	switch ($key) {
		case 'id':
			return intval($rawData);
		case 'role':
			if (is_array($rawData)) {
				$sanitized = array();
				foreach ($rawData as $value) {
					$sanitized[] = intval($value);
				}
				return $sanitized;
			} else {
				return intval($rawData);
			}
		case 'name':
			return trim($rawData);
		case 'password':
		default:
			return $rawData;
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
