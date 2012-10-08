-- As we add components, each component may be given a separate
-- MySQL user account.  We should limit the access that this account
-- has.
--
-- Keep all accounts, all users, all components, here.
--
-- Contents:
--   1. The 'medcommons' user : PHP
--   2. The 'backup' user: mc_backups
--   3. The 'gateway' user: gateway/router JDBC
--   4. The 'console' user: django
--   5. The 'phpMyAdmin' user

--------------------------------
--------------------------------
-- 1. The 'medcommons' user: PHP
--
-- Apache/PHP PDO and mysql_ library
--

GRANT INSERT, UPDATE, SELECT, DELETE
ON    mcx.*
TO    'medcommons'@'localhost';

--
-- ...the 'medcommons' user: PHP
--------------------------------
--------------------------------

-----------------------------------
-----------------------------------
-- 2. The 'backup' user: mc_backups
--
-- Python/MySQLdb
--
-- If you change this here, you may want to edit
--     073_add_backupqueue_table.sql
--
GRANT SELECT
ON    mcx.*
TO    'backup'@'localhost';

GRANT SELECT, INSERT, UPDATE, DELETE
ON    mcgateway.*
TO    'backup'@'localhost';

--
-- ...the 'backup' user: mc_backups
-----------------------------------
-----------------------------------

------------------------------------------------
------------------------------------------------
-- 3. The 'gateway' user: gateway/router JDBC...
--
-- only used for inserting into the backup queue.
--
-- Java/JDBC comes in on TCP sockets, so use 'localhost.localdomain'
--
GRANT INSERT ON mcgateway.* TO 'gateway'@'localhost.localdomain';

--
-- ...the 'gateway' user: gateway/router JDBC
---------------------------------------------
---------------------------------------------

-----------------------------------
-----------------------------------
-- 4. The 'console' user: django...
--
GRANT INSERT, UPDATE, SELECT, DELETE
ON    mcx.*
TO    'console'@'localhost';

GRANT SELECT
ON    mcgateway.*
TO    'console'@'localhost';

GRANT REPLICATION CLIENT
ON    *.*
TO    'console'@'localhost';

-- and also allow 'python manage.py' to create tables and etc.
GRANT CREATE, ALTER, DELETE, DROP, INDEX, INSERT, SELECT, UPDATE
ON    mcx.*
TO    'console_setup'@'localhost';

GRANT SELECT
ON    mcgateway.*
TO    'console_setup'@'localhost';
--
-- ...the 'console' user: django
-----------------------------------
-----------------------------------

------------------------------
------------------------------
-- 5. The 'phpMyAdmin' user...
--
GRANT ALL ON *.* TO 'phpMyAdmin'@'localhost';

-- ...the 'phpMyAdmin' user
------------------------------
------------------------------

