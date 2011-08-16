<?php

$_SETTINGS = array();
$_SETTINGS['Data Source Name'] = "mysql:dbname=easy_exam;host=localhost";
$_SETTINGS['Database User'] = 'root';
$_SETTINGS['Database Password'] = "";
$_SETTINGS['Time Zone'] = "Asia/Manila";

date_default_timezone_set ($_SETTINGS['Time Zone']);


function getDatabase()
{
	try {
		global $_SETTINGS;
		$database = new PDO($_SETTINGS['Data Source Name'], 
							$_SETTINGS['Database User'], 
							$_SETTINGS['Database Password']);
		return $database;
	} catch (PDOException $exception) {
		throw $exception;
	}
}

//For SELECT sql
function queryDatabase($sql, $parameters = null, $index = "")
{
	if (is_array($parameters)) {
		$database = getDatabase();
		$statement = $database->prepare($sql);
		foreach ($parameters as $key => $value) {
			$statement->bindValue($key, $value);
		}
		$statement->execute();
		return fetchData($statement, $index);
	} else if (null == $parameters) {
		$database = getDatabase();
		$statement = $database->query($sql);
		return fetchData($statement, $index);
	}
	return false;
}

//For INSERT, UPDATE, DELETE sql
function executeDatabase($sql, $parameters = null)
{
	if (is_array($parameters)) {
		$database = getDatabase();
		$statement = $database->prepare($sql);
		foreach ($parameters as $key => $value) {
			$statement->bindValue($key, $value);
		}
		return $statement->execute();
	} else if (null == $parameters) {
		$database = getDatabase();
		return $database->query($sql);
	}
	return false;
}

function fetchData(&$source, $index)
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

function getExamData($id)
{
	$sql = "SELECT * FROM exam WHERE exam_id=:id";
	$parameters = array(':id' => $id);
	$result = queryDatabase($sql, $parameters);
	return array_shift($result);
}

function getAllExams()
{
	$sql = "SELECT * FROM exam";
	return queryDatabase($sql);
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
	redirect($address);
}

function redirect($location)
{
	header("Location: $location");
}
