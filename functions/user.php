<?php
const ACCOUNTS_TABLE = 'accounts';
const ROLE_TABLE = 'role';
const ACCOUNT_GROUP_TABLE = 'account_group';

function getAllRoles($includeAdministrator = false)
{
	$condition = !$includeAdministrator ? ' WHERE id <> 0' : '';
	$sql = 'SELECT * FROM ' . ROLE_TABLE . $condition;
	$database = getDatabase();
	$source = $database->query($sql);
	$data = array();
	while ($row = $source->fetch(PDO::FETCH_ASSOC)) {
		$data[$row['id']] = $row['name'];
	}
	return $data;
}

function getAllUsers($owner = 0)
{
	$columns = array('id', 'name', 'group', 'role');
	$clause = array();
	$clause['ORDER BY'] = 'name';
	if (!empty($owner)) {
		$clause['WHERE']['condition'] = 'owner=:owner';
		$clause['WHERE']['parameters'] = array(':owner' => $owner);
	} else {
		$clause['WHERE']['condition'] = 'id<>0';
	}
	
	$data = selectFromTable(ACCOUNTS_TABLE, $columns, $clause);
	if (!empty($data)) {
		foreach ($data as $key => $value) {
			$data[$key]['group'] = _decodeGroup($value['group']);
		}
	}
	return $data;
}

function getAllUserGroups($owner = 0)
{
	$table = ACCOUNT_GROUP_TABLE;
	if (empty($owner)) {
		$sql = "SELECT group_id, name FROM {$table} ORDER BY name ASC";
		return queryDatabase($sql, null, 'group_id');
	} else {
		$sql = "SELECT group_id, name FROM {$table} WHERE owner=:owner ORDER BY name ASC";
		return queryDatabase($sql, array(':owner' => $owner), 'group_id');
	}
}

function addUser($inputData)
{
	$data = getArrayValues($inputData, _getAccountsTableColumns());
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

function addUserGroup($inputData)
{
	$data = getArrayValues($inputData, _getAccountsGroupTableColumns());
	$result = validateAccountGroupData($data);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	_processAccountGroupData($data);
	return insertIntoTable(ACCOUNT_GROUP_TABLE, $data);
}

function getUserData($id, $columns = '*')
{
	$result = validateAccountsData($id, 'id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$clause = array('WHERE' => array('condition' => 'id=:id',
									 'parameters' => array(':id' => $id)));
	$result = selectFromTable(ACCOUNTS_TABLE, $columns, $clause);
	if (!empty($result) && is_array($result)) {
		$data = array_shift($result);
		if (isset($data['group'])) {
			$data['group'] = _decodeGroup($data['group']);
		}
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
	$sql = "SELECT * FROM $table WHERE group_id = :id";
	$result = queryDatabase($sql, array(':id' => $id));
	if (is_array($result)) {
		return array_shift($result);
	}
	return false;
}

function updateUser($inputData)
{
	$data = getArrayValues($inputData, _getAccountsTableColumns(true));
	$result = validateAccountsData($data);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$id = $data['id'];
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

function updateUserGroup($inputData)
{
	$data = getArrayValues($inputData, _getAccountsGroupTableColumns(true));
	$result = validateAccountGroupData($data);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$id = $data['group_id'];
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

function deleteUser($inputData)
{
	$id = $inputData['id'];
	$result = validateAccountsData($id, 'id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	return deleteFromTable(ACCOUNTS_TABLE, 'id=:id', array(':id' => $id));
}

function deleteUserGroup($inputData)
{
	$id = $inputData['group_id'];
	$result = validateAccountGroupData($id, 'group_id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	return deleteFromTable(ACCOUNT_GROUP_TABLE, 'group_id=:id', array(':id' => $id));
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


//------------------------------------------------------------------------------

function _getAccountsTableColumns($includePrimaryKeys = false)
{	
	$columns = array('role', 'name', 'password', 'group', 'owner', 'other_info');
	if ($includePrimaryKeys) {
		array_unshift($columns, 'id');
	}
	return $columns;
}

function _getAccountsGroupTableColumns($includePrimaryKeys = false)
{
	$columns = array('name', 'owner');
	if ($includePrimaryKeys) {
		array_unshift($columns, 'group_id');
	}
	return $columns;
}

function _validateAccountGroupValue($value, $key)
{
	$errors = array();
	if ($key == 'group_id' && !ctype_digit("$value")) {
		$errors[] = 'Invalid account group.';
	} elseif ($key == 'name' && '' == trim($value)) {
		$errors[] = 'Invalid account group name.';
	} elseif ($key == 'owner' && !ctype_digit("$value")) {
		$errors[] = 'Invalid owner id.';
	}
	return $errors;
}

function _validateAccountsValue($value, $key = null)
{
	$errors = array();
	if ($key == 'id' && !ctype_digit("$value")) {
		$errors[] = 'Invalid account id.';
	} elseif ($key == 'role' && !_isValidAccountRole($value)) {
		$errors[] = 'Invalid role id.';
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
	} elseif ($key == 'owner' && !ctype_digit("$value")) {
		$errors[] = 'Invalid owner id';
	} elseif ($key == 'other_info') {
		//no validation
	}
	return $errors;
}

function _isValidAccountRole($value) 
{
	if ($value == EXAMINEE_ROLE || $value == EXAMINER_ROLE) {
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
	if ($key == 'id' || $key == 'owner' || $key == 'role') {
		$value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
	} elseif ($key == 'group' && is_array($value)) {
		foreach ($value as $key => $item) {
			$value[$key] = filter_var($item, FILTER_SANITIZE_NUMBER_INT);
		}
		$value = _encodeGroup($value);
	} elseif ($key == 'name' || $key == 'other_info') {
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

function _encodeGroup($groupArray)
{
	return encodeArray($groupArray);
}

function _decodeGroup($group)
{
	return decodeArray($group);
}