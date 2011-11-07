SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` tinyint(4) NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `password` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `salt` char(16) COLLATE utf8_unicode_ci NOT NULL,
  `group` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `owner` int(11) NOT NULL,
  `other_info` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `role` (`role`),
  KEY `owner` (`owner`),
  KEY `name` (`name`),
  KEY `group` (`group`(255))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `account_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `owner` int(11) NOT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `name` (`name`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `exam` (
  `exam_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `group` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `total_questions` tinyint(3) NOT NULL,
  `total_points` int(11) NOT NULL,
  `start_date_time` datetime NOT NULL,
  `end_date_time` datetime NOT NULL,
  `time_limit` decimal(4,2) NOT NULL,
  `questions_category` int(11) NOT NULL,
  `default_points` tinyint(4) NOT NULL,
  `passing_score` tinyint(3) NOT NULL,
  `question_display` tinyint(3) NOT NULL,
  `recorded` tinyint(1) NOT NULL,
  `randomize` tinyint(1) NOT NULL,
  `max_take` tinyint(4) NOT NULL,
  `score_is_percentage` tinyint(1) NOT NULL,
  `revision` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `owner` int(11) NOT NULL,
  PRIMARY KEY (`exam_id`),
  UNIQUE KEY `name` (`name`),
  KEY `questions_category` (`questions_category`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `exam_archives` (
  `exam_id` int(11) NOT NULL,
  `revision` int(11) NOT NULL DEFAULT '0',
  `properties` text NOT NULL,
  `questions` mediumtext NOT NULL,
  `answer_key` text NOT NULL,
  `is_taken` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`exam_id`,`revision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `multiple_choice` (
  `question_id` int(11) NOT NULL,
  `answer` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `category` int(11) NOT NULL,
  `choices` text COLLATE utf8_unicode_ci NOT NULL,
  `randomize` tinyint(1) NOT NULL,
  PRIMARY KEY (`question_id`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `objective` (
  `question_id` int(11) NOT NULL,
  `answer` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `category` int(11) NOT NULL,
  PRIMARY KEY (`question_id`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `question` text COLLATE utf8_unicode_ci NOT NULL,
  `category` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `owner` int(11) NOT NULL,
  PRIMARY KEY (`question_id`),
  KEY `category` (`category`),
  KEY `type` (`type`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `question_category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `parent_category` int(11) NOT NULL,
  `owner` int(11) NOT NULL,
  PRIMARY KEY (`category_id`),
  KEY `parent_category` (`parent_category`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `question_type` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `recorded_exams` (
  `exam_id` int(11) NOT NULL,
  `revision` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `questions` text NOT NULL,
  `answers` text NOT NULL,
  `correct_items` int(11) NOT NULL,
  `total_points` int(11) NOT NULL,
  `time_started` datetime NOT NULL,
  `time_ended` datetime NOT NULL,
  `take_count` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL,
  `other_data` text NOT NULL,
  PRIMARY KEY (`exam_id`,`revision`,`account_id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `role` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `true_or_false` (
  `question_id` int(11) NOT NULL,
  `answer` tinyint(1) NOT NULL,
  `category` int(11) NOT NULL,
  PRIMARY KEY (`question_id`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `accounts` (`id`) ON UPDATE CASCADE;

ALTER TABLE `account_group`
  ADD CONSTRAINT `account_group_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `accounts` (`id`) ON UPDATE CASCADE;

ALTER TABLE `exam`
  ADD CONSTRAINT `exam_ibfk_1` FOREIGN KEY (`questions_category`) REFERENCES `question_category` (`category_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `exam_ibfk_2` FOREIGN KEY (`owner`) REFERENCES `accounts` (`id`) ON UPDATE CASCADE;

ALTER TABLE `exam_archives`
  ADD CONSTRAINT `exam_archives_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exam` (`exam_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `multiple_choice`
  ADD CONSTRAINT `multiple_choice_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `multiple_choice_ibfk_2` FOREIGN KEY (`category`) REFERENCES `question_category` (`category_id`) ON UPDATE CASCADE;

ALTER TABLE `objective`
  ADD CONSTRAINT `objective_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `objective_ibfk_2` FOREIGN KEY (`category`) REFERENCES `question_category` (`category_id`) ON UPDATE CASCADE;

ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`category`) REFERENCES `question_category` (`category_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`type`) REFERENCES `question_type` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `questions_ibfk_3` FOREIGN KEY (`owner`) REFERENCES `accounts` (`id`) ON UPDATE CASCADE;

ALTER TABLE `question_category`
  ADD CONSTRAINT `question_category_ibfk_1` FOREIGN KEY (`parent_category`) REFERENCES `question_category` (`category_id`),
  ADD CONSTRAINT `question_category_ibfk_2` FOREIGN KEY (`owner`) REFERENCES `accounts` (`id`) ON UPDATE CASCADE;

ALTER TABLE `recorded_exams`
  ADD CONSTRAINT `recorded_exams_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exam_archives` (`exam_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `recorded_exams_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `true_or_false`
  ADD CONSTRAINT `true_or_false_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `true_or_false_ibfk_2` FOREIGN KEY (`category`) REFERENCES `question_category` (`category_id`) ON UPDATE CASCADE;
