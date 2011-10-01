<?php

function installDatabase($parameters)
{
	$database = $parameters['dsnPrefix'];
	_writeConfigurationToFile($parameters);
	@getDatabase();
	$error = getDatabaseError();
	if (!empty($error)) {
		_deleteSettingsFile();
		$errorMessage = 'Database Error: ' . $error['ERROR_MESSAGE'];
		$helpMessage = "Possible Solution: Please check that the database 
						already exists, and that the username and password 
						used to connect are valid.";
		$message = errorMessage($error['SQL_STATE'], array($errorMessage, '', $helpMessage));
		return $message;
	}
	
	executeDatabase("START TRANSACTION");
	_installDatabaseStructure($database);
	executeDatabase("SET FOREIGN_KEY_CHECKS=0");
	_installInitialData();
	executeDatabase("SET FOREIGN_KEY_CHECKS=1");
	executeDatabase("COMMIT");
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
		die('Unable to write settings to file.');
	}
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

function _installDatabaseStructure($database)
{	
	$file = '';
	if ($database == "mysql") {
		$file = "config/mysql-structure.sql";
	} else {
		die ('Unsupported database: ' . $database);
	}
	_executeSqlFile($file);
}

function _installInitialData()
{
	_executeSqlFile("config/initial-data.sql");
}

function _executeSqlFile($file)
{
	$fileContents = file_get_contents($file);
	$sqlStatements = explode(PHP_EOL . PHP_EOL, $fileContents);
	
	foreach ($sqlStatements as $statement) {
		$result = executeDatabase($statement);
		if (false === $result) {
			die('Error: ' . getDatabaseError('ERROR_MESSAGE'));
		}
	}
}