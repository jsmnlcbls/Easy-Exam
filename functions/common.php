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

function getCategoryData($id)
{
	$database = getDatabase();
	$statement =  $database->prepare("SELECT * FROM category WHERE category_id = :id");
	$statement->bindValue(":id", $id);
	$result = $statement->execute();
	
	return $result->fetchArray(SQLITE3_ASSOC);
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
	header("Location: $address");
}
