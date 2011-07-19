<?php
include "/functions/common.php";

$database = getDatabase();

$query = "CREATE TABLE category (category_id INTEGER PRIMARY KEY, name TEXT UNIQUE, "
	   . "parent_category INTEGER, FOREIGN KEY (parent_category) REFERENCES "
	   . "category(category_id))";
$database->query($query);

$query = "CREATE TABLE IF NOT EXISTS questions (question_id INTEGER PRIMARY KEY, "
	   . "question TEXT, answer TEXT, choiceA TEXT, choiceB TEXT, choiceC TEXT, "
	   . "choiceD TEXT, choiceE TEXT, category INTEGER, FOREIGN KEY(category) "
	   . "REFERENCES category(category_id));";

$database->query($query);

$query = "INSERT INTO category (category_id, name) VALUES (0, '');";
$database->query($query);

echo "Installation Done";