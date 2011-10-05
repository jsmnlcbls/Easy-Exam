<?php
const VALIDATION_ERROR = 1000;

const MULTIPLE_CHOICE_QUESTION = 1;
const ESSAY_QUESTION = 2;
const TRUE_OR_FALSE_QUESTION = 3;
const OBJECTIVE_QUESTION = 4;

/**
 * Returns all settings in an associative array or a specific setting if key is
 * specified. 
 * @staticvar string $config
 * @param String $key
 * @param Mixed $default the default value to be returned if key is not found
 * @return Mixed
 */
function getSettings($key = null, $default = null) {
	static $config = null;
	
	if (null === $config) {
		@include "config/settings.php";
		if (isset($settings) && is_array($settings)) {
			$config = $settings;
		}
		if (isset($viewWhitelist) && is_array($viewWhitelist)) {
			$config['View Whitelist'] = $viewWhitelist;
		}
	}
	if ($key == null) {
		return $config;
	} elseif (isset($config[$key])) {
		return $config[$key];
	}
	return $default;
}

/**
 * Start session and initializes some PHP settings
 * @return void
 */
function initialize()
{
	session_start();
	$timeZone = getSettings('Time Zone', null);
	if (null != $timeZone) {
		date_default_timezone_set($timeZone);
	}
}

/**
 * Authenticate a user using the database accounts table. Returns the user id on
 * success, or false otherwise.
 * @param String $username
 * @param String $password
 * @return Mixed
 */
function authenticateUser($username, $password)
{
	$sql = "SELECT id, password, salt FROM accounts WHERE name = :username";
	$result = queryDatabase($sql, array(':username' => $username));
	$loginDetails = array_shift($result);
	if (is_array($loginDetails) && !empty($loginDetails)) {
		$passwordHash = _hashPassword($_POST['password'], $loginDetails['salt']);
		if ($passwordHash == $loginDetails['password']) {
			return $loginDetails['id'];
		}
	}
	return false;
}

/**
 * Issues a redirect and halts PHP execution if the user is not yet logged in.
 * @return void
 */
function allowLoggedInUserOnly()
{
	if (isset($_SESSION['user'])) {
		return true;
	}
	redirect(getSettings('Login Page'));
	die();
}

/**
 * Issues a redirect to install page if not yet installed.
 * @return void
 */
function allowOnlyIfInstalled()
{
	if (!isInstalled()) {
		redirect("install.php");
		die();
	}
}

function getLoggedInUser($key = null)
{
	if (null == $key && isset($_SESSION['user'])) {
		return $_SESSION['user'];
	}
	if (isset($_SESSION['user'][$key])) {
		return $_SESSION['user'][$key];
	}
}

/**
 * Returns the database connection or null on error.
 * @staticvar string $_database
 * @return Mixed
 */
function getDatabase()
{
	static $_database = null;
	
	if (null != $_database) {
		return $_database;
	}
	
	$dataSourceName = getSettings('Data Source Name Prefix') . ":dbname=" 
					. getSettings('Database Name') . ";host=" 
					. getSettings('Database Host');
	try {
		$_database = new PDO($dataSourceName, 
							getSettings('Database User'), 
							getSettings('Database Password'));
	} catch (PDOException $exception) {
		_setDbError('', $exception->getCode(), $exception->getMessage());
	}
	return $_database;
}

/**
 * Execute a SELECT SQL statement and return its results or false on failure.
 * @param String $sql
 * @param Array $parameters an array with key-value pair corresponding to the
 * parameter names and values respectively
 * @param type $index String the primary key column (if included in the query)
 * that will be used to index the results
 * @return Mix
 */
function queryDatabase($sql, $parameters = null, $index = "")
{
	$database = getDatabase();
	$statement = $database->prepare($sql);
	
	if (is_array($parameters)) {
		foreach ($parameters as $key => $value) {
			$statement->bindValue($key, $value);
		}
	}
	
	$result = $statement->execute();
	if ($result !== false) {
		return _fetchData($statement, $index);
	} else {
		$errorInfo = $statement->errorInfo();
		_setDbError($errorInfo[0], $errorInfo[1], $errorInfo[2]);
		return false;
	}
}

/**
 * Execute a non SELECT SQL statement. Returns true on success, or false on failure.
 * @param String $sql the sql statement
 * @param Array $parameters an array with key-value pair corresponding to the
 * parameter names and values respectively
 * @return boolean
 */
function executeDatabase($sql, $parameters = null)
{
	$database = getDatabase();
	$statement = $database->prepare($sql);
	if (is_array($parameters)) {
		foreach ($parameters as $key => $value) {
			$statement->bindValue($key, $value);
		}
	}
	$result = $statement->execute();
	
	if ($result === true) {
		return true;
	} else {
		$errorInfo = $statement->errorInfo();
		_setDbError($errorInfo[0], $errorInfo[1], $errorInfo[2]);		
		return false;
	}
}

/**
 * Returns an array containing the database error information or a specific
 * subset of that error info depending on key
 * @param type $key specific error key
 * @return Mixed 
 */
function getDatabaseError($key = "")
{
	$error = _dbError();
	if ($key == "") {
		return $error;
	} else if (isset($error[$key])) {
		return $error[$key];
	}
}

/**
 * Returns the ID of the last inserted row
 * @return String
 */
function getLastInsertedId()
{
	return getDatabase()->lastInsertId();
}

/**
 * Starts a database transaction operation
 * @return boolean
 */
function beginTransaction()
{
	return getDatabase()->beginTransaction();
}

/**
 * Commits the current database transaction
 * @return boolean
 */
function commitTransaction()
{
	return getDatabase()->commit();
}

/**
 * Inserts a new row into a table
 * @param String $tableName the tablename to insert to
 * @param Array $columnValues key value pairs of column names and values
 * @return boolean
 */
function insertIntoTable($tableName, $columnValues)
{
	$tableName = _escapeSqlIdentifier($tableName);
	$columns = _escapeSqlIdentifier(array_keys($columnValues));
	$columns = implode(", ", $columns);
	
	$parameters = _createParameterValues($columnValues);
	$values = implode(", ", array_keys($parameters));
	$sql = "INSERT INTO $tableName ($columns) VALUES ($values)";
	return executeDatabase($sql, $parameters);
}

/**
 * Update table row(s)
 * @param String $tableName
 * @param Array $columnValues key value pairs of column names and values
 * @param String $condition sql condition clause
 * @param Array $conditionParameters key value pairs of condition parameter names and values
 * @return boolean 
 */
function updateTable($tableName, $columnValues, $condition, $conditionParameters = array())
{
	$tableName = _escapeSqlIdentifier($tableName);
	
	$setString = _createUpdateSqlSetString(array_keys($columnValues));
	$parameters = array_merge(_createParameterValues($columnValues), $conditionParameters);
	$sql = "UPDATE $tableName SET $setString WHERE $condition";
	return executeDatabase($sql, $parameters);
}

/**
 * Delete row(s) from a database table
 * @param String $tableName
 * @param String $whereCondition
 * @param Array $whereParameterValues key value pairs of parameter names and values
 * @return boolean 
 */
function deleteFromTable($tableName, $whereCondition, $whereParameterValues = null)
{
	$tableName = _escapeSqlIdentifier($tableName);
	$sql = "DELETE FROM {$tableName} WHERE $whereCondition";
	return executeDatabase($sql, $whereParameterValues);
}

/**
 * Rollbacks the current database transaction
 * @return boolean
 */
function rollbackTransaction()
{
	return getDatabase()->rollBack();
}

/**
 * Returns all categories for questions.
 * @param boolean $includeRootCategory
 * @return Array 
 */
function getAllQuestionCategories($includeRootCategory = false)
{
	$sql = "SELECT * FROM question_category WHERE category_id <> 0 ORDER BY name;";
	if ($includeRootCategory) {
		$sql = "SELECT * FROM question_category ORDER BY name";
	}
	return queryDatabase($sql);
}

/**
 * Get POST values
 * @param String $key
 * @param Mixed $default the default value to return if key is not found
 * @return Mixed 
 */
function getPost($key = null, $default = null)
{
	return _getRequestValues("post", $key, $default);
}

/**
 * Get the values for the query portion of the URL
 * @param String $key
 * @param Mixed $default the default value to return if key is not found
 * @return Mixed
 */
function getUrlQuery($key = null, $default = null)
{
	return _getRequestValues("get", $key, $default);
}

/**
 * Returns the results of PHP evaluation of a given view. The view will be 
 * checked first against a whitelist to ensure that it exists in the views 
 * directory where it will be loaded from. If it is not found, an empty string is
 * returned.
 * @param String $view the name of the view
 * @param Array $arguments associative array of key values pairs that will be
 * extracted as local variables for PHP evaluation
 * @return String
 */
function renderView($view, $arguments = array())
{
	if (_viewIsInWhiteList($view)) {
		$viewFile = _getViewFile($view);
		return renderViewFile($viewFile, $arguments);
	}
	return '';
}

/**
 * Returns the results of PHP evaluation of a given view file under the views 
 * directory. The only allowed characters in filepath are:
 *	lowercase letters a-z
 *  '/' as the directory separator
 *  '.php' as the required file extension in the end
 * @param String $filepath
 * @param String $arguments
 * @return String 
 */
function renderViewFile($filepath, $arguments = array())
{
	ob_start();
	if (count($arguments) > 0) {
		extract($arguments);
	}
	
	$pattern = '/^[a-z-\\/]+(.)php$/';
	if (preg_match($pattern, $filepath)) {
		include 'views/' . $filepath;
	}
	$render = ob_get_contents();
	ob_end_clean();
	return $render;
}

function getChoicesLetterColumns()
{
	$choices = array();
	foreach (range('A', 'E') as $letter) {
		$choices[$letter] = "choice{$letter}";
	}
	return $choices;
}

/**
 * Sanitize data so that it is safe for output in HTML
 * @param Array|String $output
 * @return Mixed
 */
function escapeOutput($output)
{
	if (is_string($output)) {
		return htmlentities($output, ENT_QUOTES);
	} elseif (is_array($output)) {
		$sanitizedValues = array();
		foreach ($output as $key => $value) {
			$sanitizedValues[$key] = htmlentities($value, ENT_QUOTES);
		}
		return $sanitizedValues;
	}
}

/**
 * Issue a redirect header at a location
 * @param String $location 
 */
function redirect($location)
{
	header("Location: $location");
}

/**
 * Set some data about the current user.
 * @param int $id
 * @param int $role
 * @param String $name 
 */
function setLoggedInUser($id, $role, $name = '')
{
	$_SESSION['user'] = array('id' => $id, 'role' => $role, 'name' => $name);
}

/**
 * Logout the user from the system
 */
function logoutUser()
{
	unset($_SESSION['user']);
	session_destroy();
}

/**
 * Returns the values from an input array that matches the keys given in
 * another array or returns all the array values if keys is null.
 * @param Array $inputArray
 * @param Array $keys
 * @return Array
 */
function getArrayValues($inputArray, $keys = null)
{
	if ($keys == null) {
		return array_values($inputArray);
	} else if (is_array($keys)) {
		$values = array();
		foreach ($keys as $keyVal) {
			if (isset($inputArray[$keyVal])) {
				$values[$keyVal] = $inputArray[$keyVal];
			} else {
				$values[$keyVal] = null;
			}
		}
		return $values;
	}
}

/**
 * Validate a given data using supplied validator and error message function.
 * Returns true on success or an error message in failure.
 * The validator function should accept a value and key as arguments and return
 * true on success or false on validation failure.
 * The error message function should accept a key and value as arguments and 
 * return a corresponding message for the validation failure on that given key.
 * @param Array $data the data to be validated
 * @param Closure $validatorFunction
 * @param Closure $errorMessageFunction
 * @return Mixed
 */
function validateData($data, $validatorFunction, $errorMessageFunction)
{
	$errorMessages = array();
	foreach ($data as $key => $value) {
		if (!$validatorFunction($value, $key)) {
			$errorMessages[] = $errorMessageFunction($key, $value);
		}
	}
	if (empty($errorMessages)) {
		return true;
	} else {
		return errorMessage(VALIDATION_ERROR, $errorMessages);
	}
}

/**
 * Creates and returns an ERROR message in a "standard" format.
 * @param int $code the error code
 * @param String $text the error message
 * @return String
 */
function errorMessage($code, $text)
{
	$errorText = $text;
	if (is_array($text)) {
		$errorText = implode(PHP_EOL, $text);
	}
	$message = array('ERROR' => array('code' => $code, 'text' => $errorText));
	return json_encode($message);
}

/**
 * Creates and returns an OK message in a "standard" format.
 * This signifies that no error occured during processing and that a task was
 * successfully completed.
 * @param String $text the message
 * @return String
 */
function okMessage($text)
{
	$message = array('OK' => array('text' => $text));
	return json_encode($message);
}

/**
 * Returns true if argument is an ERROR message as created by the errorMessage
 * function. False otherwise.
 * @param String $message
 * @return Boolean 
 */
function isErrorMessage($message)
{
	if (is_string($message)) {
		$message = json_decode($message, true);
		if (is_array($message) && 
			isset($message['ERROR']) && 
			isset($message['ERROR']['code']) &&
			isset($message['ERROR']['text'])) {
			return true;
		}
	}
	return false;
}

/**
 * Returns true if argument is an OK message as created by the okMessage function. 
 * False otherwise.
 * @param String $message
 * @return type 
 */
function isOkMessage($message) 
{
	if (is_string($message)) {
		$message = json_decode($message, true);
		if (is_array($message) && 
			isset($message['OK']) && 
			isset($message['OK']['text'])) {
			return true;
		}
	}
	return false;	
}

/**
 * Parse the error message into its component parts and returns either:
 * An associative array containing all message parts if part argument is null.
 * The error code if called with argument part as 'code'.
 * The text of the error if called with argument part as 'text'.
 * @param String $message the error message
 * @param String $part
 * @return Mixed 
 */
function parseErrorMessage($message, $part = null)
{
	if (isErrorMessage($message)) {
		$message = json_decode($message, true);
		if (null == $part) {
			return $message['ERROR'];
		} elseif (isset($message['ERROR'][$part])) {
			if ($part == 'code') {
				return intval($message['ERROR'][$part]);
			}
			return $message['ERROR'][$part];
		}
	}
	return null;
}

/**
 * A very simple check if easy exam is installed.
 * @return boolean
 */
function isInstalled()
{
	if (file_exists("config/settings.php")) {
		return true;
	}
	return false;
}

//------------------------Internal functions-----------------------------------


function _getViewFile($view)
{
	$viewFileHierarchy = explode ('-', $view);
	$directory = array_shift($viewFileHierarchy);
	$file = implode('-', $viewFileHierarchy);

	return "$directory/$file.php";
}

function _viewIsInWhiteList($view)
{
	$whitelist = getSettings('View Whitelist', array());
	if (in_array($view, $whitelist)) {
		return true;
	} 
	
	return false;
}

function _fetchData(&$source, $index = '')
{
	$data = array();
	if ($index != "") {
		while ($row = $source->fetch(PDO::FETCH_ASSOC)) {
			$data[$row[$index]] = $row;
		}
	} else {
		while ($row = $source->fetch(PDO::FETCH_ASSOC)) {
			$data[] = $row;
		}
	}
	return $data;
}


function _setDbError($sqlState, $dbErrorCode, $message)
{
	$error = array('SQL_STATE' => $sqlState, 
					'DB_ERROR_CODE' => $dbErrorCode,
					'ERROR_MESSAGE' => $message);
	_dbError($error);
}

function _dbError($error = null)
{
	static $databaseError = array();
	
	if (null == $error) {
		return $databaseError;
	} elseif(is_array($error)) {
		$databaseError = $error;
	}
	
}

function _hashPassword($password, $salt)
{
	return hash("sha256", $salt . $password);
}

function _getRequestValues($requestType, $key, $default)
{
	$requestType = strtolower($requestType);
	$requestArray = null;
	if ($requestType == "post") {
		$requestArray = $_POST;
	} elseif ($requestType == "get") {
		$requestArray = $_GET;
	}
	
	if (is_string($key) && isset($requestArray[$key])) {
		return $requestArray[$key];
	} elseif (is_array($key)) {
		return getArrayValues($requestArray, $key);
	} elseif (null == $key) {
		return $requestArray;
	} else {
		return $default;
	}
}

function _createParameterValues($columnValues)
{
	$parameterValues = array();
	foreach ($columnValues as $column => $value) {
		$parameterValues[":{$column}"] = $value;
	}
	return $parameterValues;
}

function _createUpdateSqlSetString($columns)
{
	$output = array();
	foreach ($columns as $name) {
		$output[] = _escapeSqlIdentifier($name) . " = :$name";
	}
	return implode(", ", $output);
}

function _escapeSqlIdentifier($identifier)
{
	$dsnPrefix = getSettings('Data Source Name Prefix');
	if ($dsnPrefix == 'mysql') {
		return _escapeMysqlIdentifier($identifier);
	}
	return $identifier;
}

function _escapeMysqlIdentifier($identifier)
{
	if (is_array($identifier)) {
		array_walk($identifier, function(&$identifier){
					$identifier = "`$identifier`";
				});
		return $identifier;
	} elseif (is_string($identifier)) {
		return "`$identifier`";
	}
}