<?php
include "/functions/common.php";

$database = getDatabase();

$query = "CREATE TABLE IF NOT EXISTS category (category_id INTEGER PRIMARY KEY, "
	   . "name TEXT UNIQUE, menu_visibility INTEGER, parent_category INTEGER, "
	   . "FOREIGN KEY (parent_category) REFERENCES category(category_id))";
$database->query($query);

$query = "CREATE TABLE IF NOT EXISTS questions (question_id INTEGER PRIMARY KEY, "
	   . "question TEXT, answer TEXT, choiceA TEXT, choiceB TEXT, choiceC TEXT, "
	   . "choiceD TEXT, choiceE TEXT, category INTEGER, type TEXT, "
	   . "FOREIGN KEY(category) REFERENCES category(category_id));";
$database->query($query);

$query = "INSERT INTO category (category_id, name) VALUES (0, '');";
$success = @$database->query($query);

$query = "CREATE TABLE IF NOT EXISTS exam (exam_id INTEGER PRIMARY KEY, name TEXT, "
	   . "start_date TEXT, end_date TEXT, questions TEXT);";
$database->query($query);

if ($success) {
	echo "Installation Done";
} else {
	echo "Installation Failed";
}