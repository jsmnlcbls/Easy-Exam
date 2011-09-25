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
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `role` (`role`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `exam` (
  `exam_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `start_date_time` datetime NOT NULL,
  `end_date_time` datetime NOT NULL,
  `time_limit` tinyint(4) NOT NULL,
  `passing_score` tinyint(4) NOT NULL,
  `questions_category` int(11) NOT NULL,
  PRIMARY KEY (`exam_id`),
  UNIQUE KEY `name` (`name`),
  KEY `questions_category` (`questions_category`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `multiple_choice` (
  `question_id` int(11) NOT NULL,
  `choiceA` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `choiceB` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `choiceC` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `choiceD` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `choiceE` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `answer` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `category` int(11) NOT NULL,
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
  PRIMARY KEY (`question_id`),
  KEY `category` (`category`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `question_category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `parent_category` int(11) NOT NULL,
  PRIMARY KEY (`category_id`),
  KEY `parent_category` (`parent_category`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `question_type` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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


ALTER TABLE `exam`
  ADD CONSTRAINT `exam_ibfk_1` FOREIGN KEY (`questions_category`) REFERENCES `question_category` (`category_id`) ON UPDATE CASCADE;

ALTER TABLE `multiple_choice`
  ADD CONSTRAINT `multiple_choice_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `multiple_choice_ibfk_2` FOREIGN KEY (`category`) REFERENCES `question_category` (`category_id`) ON UPDATE CASCADE;

ALTER TABLE `objective`
  ADD CONSTRAINT `objective_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `objective_ibfk_2` FOREIGN KEY (`category`) REFERENCES `question_category` (`category_id`) ON UPDATE CASCADE;

ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`category`) REFERENCES `question_category` (`category_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`type`) REFERENCES `question_type` (`id`) ON UPDATE CASCADE;

ALTER TABLE `question_category`
  ADD CONSTRAINT `question_category_ibfk_1` FOREIGN KEY (`parent_category`) REFERENCES `question_category` (`category_id`);

ALTER TABLE `true_or_false`
  ADD CONSTRAINT `true_or_false_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `true_or_false_ibfk_2` FOREIGN KEY (`category`) REFERENCES `question_category` (`category_id`) ON UPDATE CASCADE;
