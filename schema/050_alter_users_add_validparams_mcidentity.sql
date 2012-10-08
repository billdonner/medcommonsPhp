ALTER TABLE `users` ADD `validparams` VARCHAR(255) NOT NULL;
update  `users` set  `validparams` = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";