<?php

$_SETTINGS = array();
$_SETTINGS['Database File'] = "database.sqlite";

/**
 * Returns the SQLite database
 * @global array $_SETTINGS
 * @return SQLite3 
 */
function getDatabase()
{
	global $_SETTINGS;
	return new SQLite3($_SETTINGS['Database File']);
}

function getAllCategories()
{
	$database = getDatabase();
	$result = $database->query("SELECT * FROM category ORDER BY name");
	
	$categories = array();
	while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
		$categories[$row['category_id']] = $row;
	}
	return $categories;
}

function getPOST($key, $default = null)
{
	if (isset($_POST[$key])) {
		return $_POST[$key];
	} else {
		return $default;
	}
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
	if (isset($_POST[$key])) {
		return filterString($_POST[$key]);
	} else {
		return $default;
	}
}

function filterGET($key, $default = null) 
{
	if (isset($_GET[$key])) {
		return filterString($_GET[$key]);
	} else {
		return $default;
	}
}

function displayResultNotification($success)
{
	$address = $_SERVER['REQUEST_URI'] . "?view=error";
	if ($success) {
		$address = $_SERVER['REQUEST_URI'] . "?view=success";
	}
	header("Location: $address");
}
