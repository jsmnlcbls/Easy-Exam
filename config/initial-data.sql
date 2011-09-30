INSERT INTO `accounts` (`id`, `role`, `name`, `password`, `salt`) VALUES
(0, 0, 'Admin', '82ab5995c439c128be950e1a81bb6cde3067b1bebe8bf3d8bf165f1089589f21', '90cded7407eaac42');

INSERT INTO `question_category` (`category_id`, `name`, `parent_category`) VALUES
(0, '', 0);

INSERT INTO `question_type` (`id`, `name`) VALUES
(2, 'Essay'),
(1, 'Multiple Choice'),
(4, 'Objective'),
(3, 'True Or False');

INSERT INTO `role` (`id`, `name`) VALUES
(0, 'Administrator'),
(1, 'Examinee'),
(2, 'Examiner');