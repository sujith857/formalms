ALTER TABLE `learning_forumthread` ADD COLUMN `privateThread` TINYINT(1) NOT NULL DEFAULT 0 AFTER `rilevantForum`;