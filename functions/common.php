<?php
const VALIDATION_ERROR = 1000;

const MULTIPLE_CHOICE_QUESTION = 1;
const ESSAY_QUESTION = 2;
const TRUE_OR_FALSE_QUESTION = 3;
const OBJECTIVE_QUESTION = 4;

function getSettings($key = null, $default = null) {
	static $config = null;
	
	if (null === $config) {
		@include "config/settings.php";
		if (isset($settings) && is_array($settings)) {
			$config = $settings;
		}
	}
	if ($key == null) {
		return $config;
	} elseif (isset($config[$key])) {
		return $config[$key];
	}
	return $default;
}

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
 * Returns the database connection or false on error.
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
 * @return Mixed
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
 */
function insertIntoTable($tableName, $columnValues)
{
	$columns = implode(", ", array_keys($columnValues));
	$parameters = _createParameterValues($columnValues);
	$values = implode(", ", array_keys($parameters));
	$sql = "INSERT INTO $tableName ($columns) VALUES ($values)";
	return executeDatabase($sql, $parameters);
}

function updateTable($tableName, $columnValues, $condition, $conditionParameters = array())
{
	$setString = _createUpdateSqlSetString(array_keys($columnValues));
	$parameters = array_merge(_createParameterValues($columnValues), $conditionParameters);
	$sql = "UPDATE $tableName SET $setString WHERE $condition";
	return executeDatabase($sql, $parameters);
}

/**
 * Rollbacks the current database transaction
 * @return boolean
 */
function rollbackTransaction()
{
	return getDatabase()->rollBack();
}

function getAllQuestionCategories()
{
	$sql = "SELECT * FROM question_category ORDER BY name";
	return queryDatabase($sql);
}


function getPost($key = null, $default = null)
{
	return _getRequestValues("post", $key, $default);
}

function getUrlQuery($key = null, $default = null)
{
	return _getRequestValues("get", $key, $default);
}

function renderView($view, $arguments = array(), $escapeString = false)
{
	$viewFile = _getViewFile($view);
	return renderViewFile($viewFile, $arguments, $escapeString);
}

function renderViewFile($filename, $arguments = array(), $escapeStrings = false)
{
	ob_start();
	if (count($arguments) > 0) {
		if ($escapeStrings) {
			$arguments = escapeOutput($arguments);
		}
		extract($arguments);
	}
	include $filename;
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

function _getViewFile($view)
{
	$viewFileHierarchy = explode ('-', $view);
	$directory = array_shift($viewFileHierarchy);
	$file = implode('-', $viewFileHierarchy);
	if (_viewIsInWhiteList($directory, $file)) {
		$viewFile = 'views' . DIRECTORY_SEPARATOR . $directory 
							. DIRECTORY_SEPARATOR . $file . '.php'; 
		return $viewFile;
	}
	return '';
}

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

function redirect($location)
{
	header("Location: $location");
}

function setLoggedInUser($id)
{
	$_SESSION['user'] = array('id' => $id);
}

function logoutUser()
{
	unset($_SESSION['user']);
	session_destroy();
}

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
	$errorText = '';
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

//------------------------Internal functions-----------------------------------

function _viewIsInWhiteList($directory, $file)
{
	$views = array();
	switch ($directory) {
		case 'question':
			$views = array('category-add', 'category-edit', 'category-delete',
							'search', 'multiple-choice-add', 'essay-add', 
							'objective-add', 'true-or-false-add', 'delete',
							'search-results', 'multiple-choice-edit', 'essay-edit', 
							'objective-edit', 'true-or-false-edit', 'category-update');
			break;
		case 'exam':
			$views = array('add', 'edit', 'delete', 'edit-properties', 'edit-questions');
			break;
		case 'user':
			$views = array('add', 'list', 'delete', 'edit', 'index', 'login', 'questions');
			break;
		case 'admin':
			$views = array('main', 'menu', 'install');
			break;
		default:
			break;
	}
	if (in_array($file, $views)) {
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
		$output[] = "$name = :$name";
	}
	return implode(", ", $output);
}
