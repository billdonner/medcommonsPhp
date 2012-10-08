BEGIN;
CREATE TABLE `auth_message` (
    `id` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    `user_id` integer NOT NULL,
    `message` longtext NOT NULL
);
CREATE TABLE `auth_group` (
    `id` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    `name` varchar(80) NOT NULL UNIQUE
);
CREATE TABLE `auth_user` (
    `id` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    `username` varchar(30) NOT NULL UNIQUE,
    `first_name` varchar(30) NOT NULL,
    `last_name` varchar(30) NOT NULL,
    `email` varchar(75) NOT NULL,
    `password` varchar(128) NOT NULL,
    `is_staff` bool NOT NULL,
    `is_active` bool NOT NULL,
    `is_superuser` bool NOT NULL,
    `last_login` datetime NOT NULL,
    `date_joined` datetime NOT NULL
);
ALTER TABLE `auth_message` ADD CONSTRAINT user_id_refs_id_650f49a6 FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`);
CREATE TABLE `auth_permission` (
    `id` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    `name` varchar(50) NOT NULL,
    `content_type_id` integer NOT NULL REFERENCES `django_content_type` (`id`),
    `codename` varchar(100) NOT NULL,
    UNIQUE (`content_type_id`, `codename`)
);
CREATE TABLE `auth_group_permissions` (
    `id` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    `group_id` integer NOT NULL REFERENCES `auth_group` (`id`),
    `permission_id` integer NOT NULL REFERENCES `auth_permission` (`id`),
    UNIQUE (`group_id`, `permission_id`)
);
CREATE TABLE `auth_user_groups` (
    `id` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    `user_id` integer NOT NULL REFERENCES `auth_user` (`id`),
    `group_id` integer NOT NULL REFERENCES `auth_group` (`id`),
    UNIQUE (`user_id`, `group_id`)
);
CREATE TABLE `auth_user_user_permissions` (
    `id` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    `user_id` integer NOT NULL REFERENCES `auth_user` (`id`),
    `permission_id` integer NOT NULL REFERENCES `auth_permission` (`id`),
    UNIQUE (`user_id`, `permission_id`)
);
CREATE INDEX auth_message_user_id ON `auth_message` (`user_id`);
CREATE INDEX auth_permission_content_type_id ON `auth_permission` (`content_type_id`);
CREATE TABLE `django_content_type` (
    `id` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    `name` varchar(100) NOT NULL,
    `app_label` varchar(100) NOT NULL,
    `model` varchar(100) NOT NULL,
    UNIQUE (`app_label`, `model`)
);
CREATE TABLE `django_session` (
    `session_key` varchar(40) NOT NULL PRIMARY KEY,
    `session_data` longtext NOT NULL,
    `expire_date` datetime NOT NULL
);
CREATE TABLE `django_site` (
    `id` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    `domain` varchar(100) NOT NULL,
    `name` varchar(50) NOT NULL
);
CREATE TABLE `django_admin_log` (
    `id` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    `action_time` datetime NOT NULL,
    `user_id` integer NOT NULL REFERENCES `auth_user` (`id`),
    `content_type_id` integer NULL REFERENCES `django_content_type` (`id`),
    `object_id` longtext NULL,
    `object_repr` varchar(200) NOT NULL,
    `action_flag` smallint UNSIGNED NOT NULL,
    `change_message` longtext NOT NULL
);
CREATE INDEX django_admin_log_user_id ON `django_admin_log` (`user_id`);
CREATE INDEX django_admin_log_content_type_id ON `django_admin_log` (`content_type_id`);
CREATE TABLE `log_sources` (
    `id` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    `name` varchar(16) NOT NULL,
    `path` varchar(256) NOT NULL
);
CREATE TABLE `log_entries` (
    `id` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    `datetime` datetime NOT NULL,
    `source_id` integer NOT NULL REFERENCES `log_sources` (`id`),
    `severity` varchar(1) NOT NULL,
    `message` varchar(256) NOT NULL
);
CREATE INDEX log_sources_name ON `log_sources` (`name`);
CREATE INDEX log_entries_datetime ON `log_entries` (`datetime`);
CREATE INDEX log_entries_source_id ON `log_entries` (`source_id`);
CREATE TABLE `security_certificate` (
    `id` integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    `issued` datetime NOT NULL,
    `CN` varchar(64) NOT NULL,
    `C` varchar(2) NOT NULL,
    `ST` varchar(64) NOT NULL,
    `L` varchar(64) NOT NULL,
    `O` varchar(64) NOT NULL,
    `OU` varchar(64) NOT NULL,
    `key` longtext NOT NULL,
    `csr` longtext NOT NULL,
    `crt` longtext NULL
);
COMMIT;
