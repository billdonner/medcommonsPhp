/*
 * init.sql
 * Copyright(c) 2007, Medcommons, Inc.
 *
 * Global allocators seed table
 *
 * SQLite3 usage:
 * $ mkdir db
 * $ sqlite3 db/mc_seeds < init.sql
 *
 * MySQL usage:
 * $ mysql -u medcommons mcglobals < init.sql
 */
BEGIN;

CREATE TABLE alloc_numbers (
	`id` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,

	name VARCHAR(32),

	/* range and multiplier for final number */
	base DECIMAL(16),
	leap DECIMAL(16),

	/* iterations per allocation */
	iterations INTEGER,

	seed       DECIMAL(16)
);

CREATE TABLE `appliances` (
	`id` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
	`name` varchar(64) NOT NULL,
	`url` varchar(200) NOT NULL,
	`email` varchar(75) NOT NULL
);

CREATE TABLE `alloc_log` (
	`id` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
	`numbers_id` integer NOT NULL REFERENCES `alloc_numbers` (`id`),
	`seed` numeric(16, 0) NOT NULL,
	`datetime` datetime NOT NULL,
	`appliance_id` integer NOT NULL REFERENCES `appliances` (`id`),
	`ipaddr` char(15) NOT NULL,

	KEY (numbers_id, seed)
);

CREATE INDEX alloc_numbers_name ON `alloc_numbers` (`name`);

CREATE TABLE `appliance_users` (
	`name` VARCHAR(64) NOT NULL,
	`mcid` DECIMAL(16,0) NOT NULL DEFAULT '0',
	PRIMARY KEY (`name`)
) ENGINE=InnoDB;

GRANT INSERT, UPDATE, SELECT ON * TO 'mc_globals'@'localhost';

COMMIT;
