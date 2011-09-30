<?php
const ACCOUNTS_TABLE = 'accounts';
const ROLE_TABLE = 'role';

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
	$result = _validateAccountsData($data);
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$data = _sanitizeAccountsData($data);
	$data['role'] = _deriveRole($data['role']);
	$passwordData = _derivePassword($data['password']);
	$data['password'] = $passwordData['hash'];
	$data['salt'] = $passwordData['salt'];
	
	return insertIntoTable(ACCOUNTS_TABLE, $data);
}

function getUserData($id)
{
	$result = _validateAccountsData($id, 'id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$id = _sanitizeAccountsData($id, 'id');
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
	$result = array();
	$result[] = _validateAccountsData($id, 'id');
	$result[] = _validateAccountsData($data);
	foreach ($result as $value) {
		if (isErrorMessage($value)) {
			return $value;
		}
	}
	
	$id = _sanitizeAccountsData($id, 'id');
	$data = _sanitizeAccountsData($data);
	$data['role'] = _deriveRole($data['role']);
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

function deleteUser($id)
{
	$result = _validateAccountsData($id, 'id');
	if (isErrorMessage($result)) {
		return $result;
	}
	
	$id = _sanitizeAccountsData($id, 'id');
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

function _validateAccountsData($value, $key = null)
{
	$validatorFunction = function ($value, $key) {
		return _isValidAccountsValue($value, $key);
	};
	
	$errorMessageFunction = function ($key, $value) {
		return _getValidateAccountErrorMessage($key, $value);
	};
	
	$inputData = $value;
	if (!is_array($value) && is_string($key)) {
		$inputData = array($key => $value);
	}
	
	return validateData($inputData, $validatorFunction, $errorMessageFunction);
}


function _isValidAccountsValue($value, $key)
{
	if ($key == 'id' && ctype_digit("$value")) {
		return true;
	} elseif ($key == 'role' && is_array($value)) {
		foreach ($value as $role) {
			if (!_isValidAccountRole($role)) {
				return false;
			}
		}
		return true;
	} elseif ($key == 'role') {
		return _isValidAccountRole($value);
	} elseif ($key == 'name' && "" != trim($value) && (strlen($value) < 64)) {
		return true;
	} elseif ($key == 'password') {
		return true;
	}
	return false;
}

function _isValidAccountRole($value) 
{
	if (ctype_digit("$value") && $value < 4 && $value > 0) {
		return true;
	}
	return false;
}

function _getValidateAccountErrorMessage($key, $data)
{
	$message = "Invalid ";
	if ($key == 'id') {
		$message .= "account id: ";
	} elseif ($key == 'role') {
		$message .= "account role";
	} elseif ($key == 'name') {
		$message .= "account name: ";
	} else {
		$message .= "data: ";
	}
	if ($key != 'role' || is_array($key)) {
		return $message . "'$data'";
	} else {
		return $message;
	}
}

function _sanitizeAccountsData($rawData, $key = null)
{
	if (is_array($rawData)) {
		$sanitizedData = array();
		foreach ($rawData as $key => $value) {
			$sanitizedData[$key] = _sanitizeAccountsValue($value, $key);
		}
		return $sanitizedData;
	} elseif (is_string($key)) {
		return _sanitizeAccountsValue($rawData, $key);
	}
}

function _sanitizeAccountsValue($rawData, $key)
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
