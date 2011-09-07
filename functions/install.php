<?php
$query = array();

$query[] = "CREATE DATABASE " .getSettings('Database Name') . " DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
$query[] = "USE " . getSettings("Database Name");


$query[] = <<<QUERY
CREATE TABLE IF NOT EXISTS `category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `parent_category` int(11) NOT NULL,
  PRIMARY KEY (`category_id`),
  KEY `parent_category` (`parent_category`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
QUERY;

$query[] = <<<QUERY
ALTER TABLE `category`
  ADD CONSTRAINT `category_ibfk_1` FOREIGN KEY (`parent_category`) 
  REFERENCES `category` (`category_id`);
QUERY;

//temporarily disable
$query[] = "SET foreign_key_checks = 0;";

$query[] = <<<QUERY
INSERT INTO `category` (`category_id`, `name`, `parent_category`) 
VALUES (0, '', 0);
QUERY;

//because mysql does not follow the category_id value set above and insist on it being 1
$query[] = <<<QUERY
UPDATE `category` SET category_id = 0;
QUERY;

$query[] = "SET foreign_key_checks = 1;";

$query[] = <<<QUERY
CREATE TABLE IF NOT EXISTS `exam` (
  `exam_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `start_date_time` datetime NOT NULL,
  `end_date_time` datetime NOT NULL,
  `time_limit` tinyint(4) NOT NULL,
  `passing_score` tinyint(4) NOT NULL,
  `questions_category` int(11) NOT NULL,
  PRIMARY KEY (`exam_id`),
  UNIQUE KEY `name` (`name`),
  KEY `questions_category` (`questions_category`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
QUERY;

$query[] = <<<QUERY
CREATE TABLE IF NOT EXISTS `question_type` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;
QUERY;

$query[] = <<<QUERY
INSERT INTO `question_type` (`id`, `name`) VALUES
(1, 'Unassigned'),
(2, 'Exam Question'),
(3, 'Review Question');
QUERY;

$query[] = <<<QUERY
CREATE TABLE IF NOT EXISTS `questions` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `question` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `answer` char(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `choiceA` varchar(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `choiceB` varchar(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `choiceC` varchar(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `choiceD` varchar(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `choiceE` varchar(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`question_id`),
  KEY `category` (`category`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
QUERY;

$query[] = <<<QUERY
CREATE TABLE IF NOT EXISTS `role` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
QUERY;

$query[] = <<<QUERY
INSERT INTO `role` (`id`, `name`) VALUES
(1, 'Examinee'),
(2, 'Reviewer'),
(4, 'Examiner');
QUERY;

$query[] = <<<QUERY
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` tinyint(4) NOT NULL,
  `name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` char(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `salt` char(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `role` (`role`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
QUERY;

$query[] = <<<QUERY
ALTER TABLE `exam`
  ADD CONSTRAINT `exam_ibfk_1` FOREIGN KEY (`questions_category`) REFERENCES `category` (`category_id`) ON UPDATE CASCADE;
QUERY;

$query[] = <<<QUERY
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`type`) REFERENCES `question_type` (`id`),
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`category`) REFERENCES `category` (`category_id`);
QUERY;

function installDatabase($host, $dbUser, $dbPassword)
{
	global $query;
	$database = _connectDb($host, $dbUser, $dbPassword);
	$database->query("START TRANSACTION");
	
	foreach ($query as $value) {
		$result = $database->query($value);
		if ($result === false) {
			$database->query("ROLLBACK");
			return $database->errorInfo();
		}
	}
	$database->query("COMMIT");
	return true;
}

function _connectDb($host, $user, $password)
{
	$dataSourceName = "mysql:host=$host";
	$database = null;
	try {
		$database = new PDO($dataSourceName, 
							getSettings('Database User'), 
							getSettings('Database Password'));
	} catch (PDOException $exception) {
		echo "Database Error: " . $exception->getMessage();
		die();
	}
	return $database;
}