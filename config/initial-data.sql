INSERT INTO `question_category` (`category_id`, `name`, `parent_category`) VALUES
(0, '', 0);

INSERT INTO `question_type` (`id`, `name`) VALUES
(2, 'Essay'),
(1, 'Multiple Choice'),
(4, 'Objective'),
(3, 'True Or False');

INSERT INTO `role` (`id`, `name`) VALUES
(1, 'Examinee'),
(2, 'Examiner');