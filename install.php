<?php
include "/functions/common.php";

$database = getDatabase();

$query = array();

$query[] = <<<QUERY
CREATE TABLE IF NOT EXISTS `category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `menu_visibility` tinyint(1) NOT NULL,
  `parent_category` int(11) NOT NULL,
  PRIMARY KEY (`category_id`),
  KEY `parent_category` (`parent_category`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;
QUERY;

$query[] = <<<QUERY
ALTER TABLE `category`
  ADD CONSTRAINT `category_ibfk_1` FOREIGN KEY (`parent_category`) 
  REFERENCES `category` (`category_id`);
QUERY;

$query[] = "SET foreign_key_checks = 0;";

$query[] = <<<QUERY
INSERT INTO `category` (`category_id`, `name`, `menu_visibility`, `parent_category`) 
VALUES (0, '', 0, 0);
QUERY;

//because mysql does not follow the category_id value set above and insist on it being 1
$query[] = <<<QUERY
UPDATE `category` SET category_id = 0;
QUERY;

$query[] = "SET foreign_key_checks = 1;";

$query[] = <<<QUERY
CREATE TABLE IF NOT EXISTS `exam` (
  `exam_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `start_date_time` datetime NOT NULL,
  `end_date_time` datetime NOT NULL,
  `time_limit` tinyint(4) NOT NULL,
  `passing_score` tinyint(4) NOT NULL,
  `questions_category` int(11) NOT NULL,
  PRIMARY KEY (`exam_id`),
  UNIQUE KEY `name` (`name`),
  KEY `questions_category` (`questions_category`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1
QUERY;

$query[] = <<<QUERY
CREATE TABLE IF NOT EXISTS `questions` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `question` text,
  `answer` char(1) DEFAULT NULL,
  `choiceA` varchar(256) DEFAULT NULL,
  `choiceB` varchar(256) DEFAULT NULL,
  `choiceC` varchar(256) DEFAULT NULL,
  `choiceD` varchar(256) DEFAULT NULL,
  `choiceE` varchar(256) DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `type` char(1) DEFAULT NULL,
  PRIMARY KEY (`question_id`),
  KEY `category` (`category`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
QUERY;

$query[] = <<<QUERY
ALTER TABLE `exam`
  ADD CONSTRAINT `exam_ibfk_1` FOREIGN KEY (`questions_category`) REFERENCES `category` (`category_id`) ON UPDATE CASCADE;
QUERY;

$query[] = <<<QUERY
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`category`) REFERENCES `category` (`category_id`);
QUERY;

$database->query("START TRANSACTION");
foreach ($query as $value) {
	$result = $database->query($value);
	if ($result === false) {
		echo 'Installation failed. ' . $database->errorCode();
		echo $value;
		$database->query("ROLLBACK");
		die();
	}
}
$database->query("COMMIT");
echo "Database tables successfully created";

