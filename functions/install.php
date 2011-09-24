<?php

function installDatabase($parameters)
{
	$database = $parameters['dsnPrefix'];
	_writeConfigurationToFile($parameters);
	getDatabase();
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
	$out = array();
	$out[] = '<?php';
	$out[] = '$settings["Data Source Name Prefix"] = "' . $parameters['dsnPrefix'] . '";';
	$out[] = '$settings["Database Host"] = "' . $parameters['host'] . '";';
	$out[] = '$settings["Database Name"] = "' . $parameters['database'] . '";';
	$out[] = '$settings["Database User"] = "' . $parameters['user'] . '";';
	$out[] = '$settings["Database Password"] = "' . $parameters['password'] . '";';
	$out[] = '$settings["Time Zone"] = "Asia/Manila";';
	$out[] = '$settings["Login Page"] = "login.php";';
	$out[] = '$settings["User Page"] = "index.php";';
	
	$data = implode(PHP_EOL, $out);
	$result = file_put_contents("config/settings.php", $data);
	if (false === $result) {
		die('Unable to write settings to file.');
	}
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