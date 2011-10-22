<?php
const INSTALLATION_ERROR = 100;

function install($configuration, $credentials)
{
	$steps = array();
	$steps[] = function() use ($configuration) {
					return _writeConfigurationToFile($configuration);
			   };
	$steps[] = function() {
					return _checkDatabaseConnection();
				};
	$steps[] = function() use ($configuration) {
					return _installDatabaseStructure($configuration);
				};
	$steps[] = function() {
					return _installInitialData();
				};
	$steps[] = function() use ($credentials) {
					return _setAdminCredentials($credentials);
				};
	
	foreach ($steps as $function) {
		$result = $function();
		if (isErrorMessage($result)) {
			return $result;
		}
	}
	return true;
}

function _setAdminCredentials($credentials)
{
	if ($credentials['adminPassword'] != $credentials['adminPasswordConfirmation']) {
		return errorMessage(INSTALLATION_ERROR, 'Passwords do not match.');
	}
	include "functions/user.php";	
	
	return updateAdminCredentials($credentials['adminUsername'], $credentials['adminPassword']);
}

function _installDatabaseStructure($parameters)
{
	$result = _validateParameters($parameters);
	if (is_array($result) && !empty($result)) {
		return errorMessage(INSTALLATION_ERROR, $result);
	}

	$database = $parameters['dsnPrefix'];
	$file = "config/{$database}-structure.sql";
	beginTransaction();
	$result = _executeSqlFile($file);
	if (!isErrorMessage($result)) {
		commitTransaction();
		return true;
	}
	rollbackTransaction();
	return $result;
}

function _installInitialData()
{
	$database = getSettings('Data Source Name Prefix');
	$result = array();
	
	$result[] = beginTransaction();
	
	$data = array('id' => 0, 'role' => 0, 'name' => 'Admin', 'password' => '', 
				  'salt' => '', 'owner' => '0', 'other_info' => 'Root account.');
	$result[] = insertIntoTable('accounts', $data);
	
	$data = array('category_id' => 0, 'name' => '', 'parent_category' => 0);
	if ($database == 'mysql') {
		$result[] = executeDatabase('SET FOREIGN_KEY_CHECKS = 0;');
		$result[] = insertIntoTable('question_category', $data);
		$result[] = executeDatabase('SET FOREIGN_KEY_CHECKS = 1;');
	}
	
	$data = array();
	$data[] = array('id' => 1, 'name' => 'Multiple Choice');
	$data[] = array('id' => 2, 'name' => 'Essay');
	$data[] = array('id' => 3, 'name' => 'True Or False');
	$data[] = array('id' => 4, 'name' => 'Objective');
	foreach ($data as $row) {
		$result[] = insertIntoTable('question_type', $row);
	}
	
	$data = array();
	$data[] = array('id' => 0, 'name' => 'Administrator');
	$data[] = array('id' => 1, 'name' => 'Examinee');
	$data[] = array('id' => 2, 'name' => 'Examiner');
	foreach ($data as $row) {
		$result[] = insertIntoTable('role', $row);
	}
	
	foreach ($result as $value) {
		if (!$value) {
			rollbackTransaction();
			return errorMessage(INSTALLATION_ERROR, 'Failed to populate database.');
		}
	}
	commitTransaction();
	return true;
}

function _validateParameters($parameters)
{
	$errors = array();
	if ($parameters['dsnPrefix'] != 'mysql') {
		$errors[] = 'Invalid database software.';
	}
	if (empty($parameters['host'])) {
		$errors[] = 'No database host specified.';
	}
	if (empty($parameters['database'])) {
		$errors[] = 'No database name specified.';
	}
	if (empty($parameters['user'])) {
		$errors[] = 'No database user specified.';
	}
	if (empty($errors)) {
		return true;
	}
	return $errors;
}

function _checkDatabaseConnection()
{
	@getDatabase();
	$error = getDatabaseError();
	if (!empty($error)) {
		_deleteSettingsFile();
		$errorMessage = 'Database Error: ' . $error['ERROR_MESSAGE'];
		$helpMessage =	"Possible Solution: Please check that the database "
					 .	"already exists, and that the username and password "
					 .	"used to connect are valid.";
		$message = errorMessage(INSTALLATION_ERROR, array($errorMessage, '', $helpMessage));
		return $message;
	}
	return true;
}

function _writeConfigurationToFile($parameters)
{
	$iniValues = parse_ini_file("config/initial-settings.ini");
	$settings = array('Data Source Name Prefix' => $parameters['dsnPrefix'],
					  'Database Host' => $parameters['host'],
					  'Database Name' => $parameters['database'],
					  'Database User' => $parameters['user'],
					  'Database Password' => $parameters['password'],
					  'Time Zone' => $iniValues['timeZone'],
					  'Login Page' => $iniValues['loginPage'],
					  'User Page' => $iniValues['userPage'],
					  'Admin Page' => $iniValues['adminPage']
					);
	
	$data = array();
	$data[] = '<?php';
	$data[] = _createSettingsInPhpCode($settings);
	$data[] = '';
	$data[] = _createViewWhitelistInPhpCode();
	$fileContents = implode(PHP_EOL, $data);
	$result = file_put_contents("config/settings.php", $fileContents);
	if (false === $result) {
		return errorMessage(INSTALLATION_ERROR, 'Unable to write settings to file.');
	}
	return true;
}

function _createSettingsInPhpCode($settings)
{
	$out = array();
	$out[] = '$settings = array();';
	foreach ($settings as $key => $value) {
		$out[] = '$settings["' . $key . '"] = "' . $value . '";';
	}
	return implode(PHP_EOL, $out);
}

function _createViewWhitelistInPhpCode()
{
	$dir = new RecursiveDirectoryIterator("views");
	$files = new RecursiveIteratorIterator($dir);
	$out = array();
	$values = array();
	$out[] = '$viewWhitelist = array(';
	foreach ($files as $value) {
		$view = str_replace("views" . DIRECTORY_SEPARATOR, "", $value);
		$view = str_replace(DIRECTORY_SEPARATOR, '-', $view);
		$view = str_replace('.php', '', $view);
		$values[] = "'$view'";
	}
	$out[] = implode(',' . PHP_EOL, $values);
	$out[] = ');';
	return implode(PHP_EOL, $out);
}

function _deleteSettingsFile()
{
	rename("config/settings.php", "config/must-be-gone.php");
	unlink("config/must-be-gone.php");
}

function _executeSqlFile($file)
{
	$fileContents = file_get_contents($file);
	$sqlStatements = explode(PHP_EOL . PHP_EOL, $fileContents);
	
	foreach ($sqlStatements as $statement) {
		$result = executeDatabase($statement);
		if (false === $result) {
			$message = 'Error: ' . getDatabaseError('ERROR_MESSAGE');
			return errorMessage(INSTALLATION_ERROR, $message);
		}
	}
}