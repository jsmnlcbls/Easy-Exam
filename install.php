<?php

$database = new SQLite3("questions.sqlite");

$query = "CREATE TABLE category (category_id INTEGER PRIMARY KEY, name TEXT)";

$query = "CREATE TABLE IF NOT EXISTS questions (question_id INTEGER PRIMARY KEY, "
	   . "question TEXT, answer TEXT, choiceA TEXT, choiceB TEXT, choiceC TEXT, "
	   . "choiceD TEXT, category INTEGER, parent_category INTEGER)";

$database->query($query);

echo "Installation Done";