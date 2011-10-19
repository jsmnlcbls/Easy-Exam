<?php
const ADMINISTRATOR_ROLE = 0;
const EXAMINEE_ROLE = 1;
const EXAMINER_ROLE = 2;

const ACCOUNTS_TABLE = 'accounts';
const ROLE_TABLE = 'role';
const ACCOUNT_GROUP_TABLE = 'account_group';

function getAllRoles()
{
	$sql = 'SELECT * FROM ' . ROLE_TABLE . ' WHERE id <> 0';
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

function getAllUserGroups()
{
	static $cache = null;
	
	if (null === $cache) {
		$table = ACCOUNT_GROUP_TABLE;
		$sql = "SELECT group_id, name FROM {$table} ORDER BY name ASC";
		$cache = queryDatabase($sql, null, 'group_id');
	}
	return $cache;
}

function addUser($data)
{
	$result = validateAccountsData($data);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processAccountsData($data);
	$passwordData = _derivePassword($data['password']);
	$data['password'] = $passwordData['hash'];
	$data['salt'] = $passwordData['salt'];
	return insertIntoTable(ACCOUNTS_TABLE, $data);
}

function addUserGroup($data)
{
	$result = validateAccountGroupData($data);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processAccountGroupData($data);
	return insertIntoTable(ACCOUNT_GROUP_TABLE, $data);
}

function getUserData($id)
{
	$result = validateAccountsData($id, 'id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$table = ACCOUNTS_TABLE;
	$sql = "SELECT * FROM {$table} WHERE id = :id";
	$parameters = array(':id' => $id);
	$result = queryDatabase($sql, $parameters);
	if (is_array($result)) {
		$data = array_shift($result);
		$data['group'] = _decodeGroup($data['group']);
		$data['role'] = _decodeRole($data['role']);
		return $data;
	}
	return false;
}

function getUserGroupData($id)
{
	$result = validateAccountGroupData($id, 'group_id');
	if (isErrorMessage($result)) {
		return $result;
	}

	$table = ACCOUNT_GROUP_TABLE;
	$sql = "SELECT group_id, name FROM $table WHERE group_id = :id";
	$result = queryDatabase($sql, array(':id' => $id));
	if (is_array($result)) {
		return array_shift($result);
	}
	return false;
}

function updateUser($id, $data)
{
	$userData = array_merge(array('id' => $id), $data);
	$result = validateAccountsData($userData);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processAccountsData($data);
	if ($data['password'] != "") {
		$passwordData = _derivePassword($data['password']);
		$data['password'] = $passwordData['hash'];
		$data['salt'] = $passwordData['salt'];
		return updateTable(ACCOUNTS_TABLE, $data, "id = :id", array(':id' => $id));
	} else {
		unset ($data['password']);
		return updateTable(ACCOUNTS_TABLE, $data, "id = :id", array(':id' => $id));
	}
}

function updateUserGroup($id, $data)
{
	$groupData = array_merge($data, array('group_id' => $id));
	$result = validateAccountGroupData($groupData);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processAccountGroupData($data);
	return updateTable(ACCOUNT_GROUP_TABLE, $data, 'group_id = :id', array(':id' => $id));
}

function updateAdminCredentials($name, $password)
{
	$result = validateAccountsData($name, 'name');	
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processAccountsData($name, 'name');
	$passwordData = _derivePassword($password);
	$data = array();
	$data['password'] = $passwordData['hash'];
	$data['salt'] = $passwordData['salt'];
	$data['name'] = $name;
	
	return updateTable(ACCOUNTS_TABLE, $data, "id = 0");
}

function deleteUser($id)
{
	$result = validateAccountsData($id, 'id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	return deleteFromTable(ACCOUNTS_TABLE, 'id=:id', array(':id' => $id));
}

function deleteUserGroup($id)
{
	$result = validateAccountGroupData($id, 'group_id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	return deleteFromTable(ACCOUNT_GROUP_TABLE, 'group_id=:id', array(':id' => $id));
}

function getAccountsTableColumns($includePrimaryKeys = false)
{	
	if ($includePrimaryKeys) {
		return array('id', 'role', 'name', 'password', 'group');
	} else {
		return array('role', 'name', 'password', 'group');
	}
}

function validateAccountsData($value, $key = null)
{
	$validator = function ($value, $key) {
		return _validateAccountsValue($value, $key);
	};
	
	return validateInputData($validator, $value, $key);
}

function validateAccountGroupData($value, $key = null)
{
	$validator = function ($value, $key) {
		return _validateAccountGroupValue($value, $key);
	};

	return validateInputData($validator, $value, $key);
}

function _validateAccountGroupValue($value, $key)
{
	$errors = array();
	if ($key == 'group_id' && !ctype_digit("$value")) {
		$errors[] = 'Invalid account group.';
	} elseif ($key == 'name' && '' == trim($value)) {
		$errors[] = 'Invalid account group name.';
	}
	return $errors;
}

function _validateAccountsValue($value, $key = null)
{
	$errors = array();
	if ($key == 'id' && !ctype_digit("$value")) {
		$errors[] = 'Invalid account id.';
	} elseif ($key == 'role') {
		if (is_array($value)) {
			foreach ($value as $role) {
				if (!_isValidAccountRole($role)) {
					$errors[] = 'Invalid role id.';
				}
			}
		} else {
			$errors[] = 'Invalid role data.';
		}
	} elseif ($key == 'name' && "" == trim($value)) {
		$errors[] = 'Account name is empty.';
	} elseif ($key == 'password') {
		//no validation
	} elseif ($key == 'group') {
		if (is_array($value)) {
			foreach ($value as $group) {
				if (!_isValidAccountGroup($group)) {
					$errors[] = 'Invalid account group.';
				}
			}
		} else {
			$errors[] = 'Invalid account group data.';
		}
	}
	return $errors;
}

function _isValidAccountRole($value) 
{
	if (ctype_digit("$value") && $value < 4 && $value > 0) {
		return true;
	}
	return false;
}

function _isValidAccountGroup($value)
{
	$userGroups = getAllUserGroups();
	if (isset($userGroups[$value])) {
		return true;
	}
	return false;
}

function _processAccountGroupData(&$data, $key = null)
{
	$function = function (&$data, $key) {
		_processAccountGroupValue($data, $key);
	};
	processData($function, $data, $key);
}

function _processAccountGroupValue(&$value, $key = null)
{
	if ($key == 'group_id') {
		$value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
	} elseif ($key == 'name') {
		$value = trim($value);
	}
}

function _processAccountsData(&$data, $key = null)
{
	$function = function(&$data, $key) { _processAccountsValue($data, $key); };
	processData($function, $data, $key);
}

function _processAccountsValue(&$value, $key = null)
{
	if ($key == 'id') {
		$value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
	} elseif ($key == 'role' && is_array($value)) {
		foreach ($value as $key => $item) {
			$value[$key] = filter_var($item, FILTER_SANITIZE_NUMBER_INT);
		}
		$value = _encodeRole($value);
	} elseif ($key == 'group' && is_array($value)) {
		foreach ($value as $key => $item) {
			$value[$key] = filter_var($item, FILTER_SANITIZE_NUMBER_INT);
		}
		$value = _encodeGroup($value);
	} elseif ($key == 'name') {
		$value = trim($value);
	} elseif ($key == 'password') {
		//no processing
	}
}

function _derivePassword($plainText)
{
	$rand = mt_rand(100000, 999999);
	$salt = substr(md5($rand), 0, 16);
	$password = _hashPassword($plainText, $salt);
	return array('hash' => $password, 'salt' => $salt);
}

function _encodeRole($roleArray)
{
	$role = 0;
	foreach ($roleArray as $value) {
		$role |= $value;
	}
	return $role;
}

function _decodeRole($encodedRole)
{
	$roles = getAllRoles();
	$output = array();
	foreach ($roles as $id => $name) {
		if ($encodedRole & $id) {
			$output[$id] = $name;
		}
	}
	return $output;
}

function _encodeGroup($groupArray)
{
	return encodeArray($groupArray);
}

function _decodeGroup($group)
{
	return decodeArray($group);
}