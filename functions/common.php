<?php
const MULTIPLE_CHOICE_QUESTION = 1;
const ESSAY_QUESTION = 2;
const TRUE_OR_FALSE_QUESTION = 3;
const OBJECTIVE_QUESTION = 4;

function getSettings($key = null) {
	static $settings = 
	array('Data Source Name Prefix' => 'mysql',
		  'Database Name' => 'easy_exam',
		  'Database Host' => 'localhost',
		  'Database User' => 'root',
		  'Database Password' => '',
		  'Time Zone' => "Asia/Manila",
		  'Login Page' => 'login.php',
		  'User Page' => 'index.php'
	);
	if ($key == null) {
		return $settings;
	} elseif (isset($settings[$key])) {
		return $settings[$key];
	}
}

function initialize()
{
	session_start();
	date_default_timezone_set (getSettings('Time Zone'));	
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

function getAvailableExams()
{
	$localDateTime = date("Y-m-d H:s");
	$sql = "SELECT * FROM exam WHERE :dateTime >= start_date_time AND :dateTime < end_date_time ORDER BY name";
	$parameters = array(':dateTime' => $localDateTime);
	return queryDatabase($sql, $parameters);
}

function getCategoryData($id)
{
	$sql = "SELECT * FROM category WHERE category_id = :id";
	$parameters = array(':id' => $id);
	$result = queryDatabase($sql, $parameters);
	return array_shift($result);
}

function getAllMenuCategories()
{
	$sql = "SELECT * FROM category WHERE menu_visibility = 1 ORDER BY name";
	return queryDatabase($sql, null, 'category_id');
}

function getAllCategories()
{
	$sql = "SELECT * FROM category ORDER BY name";
	return queryDatabase($sql);
}

function getAllQuestionTypes()
{
	$sql = "SELECT id, name FROM question_type ORDER BY id";
	return queryDatabase($sql);
}

function getCategoryHierarchy($parent = 0)
{
	function createTree(&$categories, $parent)
	{
		$tree = array();
		foreach ($categories as $key => $value) {
			if ($parent == $value['parent_category'] &&
				"" != $value['name']) {
				$categoryId = $value['category_id'];
				$tree[$categoryId] = createTree($categories, $categoryId);
				unset($categories[$key]);
			}
		}
		if (!empty($tree)) {
			return $tree;	
		}
	}
	
	$categories = getAllCategories();
	
	return createTree($categories, $parent);
}

function getSubCategories($parent)
{
	function searchSubCategories($hierarchy)
	{
		$subCategories = array();
		foreach ($hierarchy as $key => $value) {
			if (is_array($value)) {
				$subCategories[] = $key;
				$result = searchSubCategories($value);
				if (!empty($result)) {
					$subCategories = array_merge($result, $subCategories);
				}
			} else if (empty($value)) {
				$subCategories[] = $key;
			}
		}
		return $subCategories;
	}
	
	$hierarchy = getCategoryHierarchy($parent);
	if (!empty($hierarchy)) {
		return searchSubCategories($hierarchy);
	} else {
		return array();
	}
}

function getPOST($key = null, $default = null)
{
	return _getRequestValues("post", $key, $default);
}

function getQuery($key = null, $default = null)
{
	return _getRequestValues("get", $key, $default);
}

/**
 * Filters string using the built in PHP function
 * @param String $string
 * @return String
 */
function filterString($string)
{
	return filter_var($string, FILTER_SANITIZE_STRING);
}

function filterPOST($key, $default = null)
{
	if (is_array($key)) {
		$filteredValues = array();
		foreach ((getPOST($key, $default)) as $key => $value) {
			$filteredValues[$key] = filterString($value);
		}
		return $filteredValues;
	} elseif (is_string($key)) {
		return filterString(getPost($key, $default));
	} else {
		return $default;
	}
}

function filterGET($key, $default = null) 
{
	if (is_array($key)) {
		$filteredValues = array();
		foreach ((getQuery($key, $default)) as $key => $value) {
			$filteredValues[$key] = filterString($value);
		}
	} elseif (is_string($key)) {
		return filterString(getQuery($key, $default));
	} else {
		return $default;
	}
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

function getQuestionEditView($type)
{
	if ($type == MULTIPLE_CHOICE_QUESTION) {
		return "question-multiple-choice-edit";
	} elseif ($type == ESSAY_QUESTION) {
		return "question-essay-edit-edit";
	} elseif ($type == TRUE_OR_FALSE_QUESTION) {
		return "question-true-or-false-edit";
	} elseif ($type == OBJECTIVE_QUESTION) {
		return "question-objective-edit";
	}
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

//------------------------Internal functions-----------------------------------


function _viewIsInWhiteList($directory, $file)
{
	$views = array();
	switch ($directory) {
		case 'question':
			$views = array('category-add', 'category-edit', 'category-delete',
							'search', 'multiple-choice-add', 'esssay-add', 
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
