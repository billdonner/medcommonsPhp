/*
 * init.sql
 * Copyright(c) 2006, Medcommons, Inc.
 *
 * MCID seed table
 *
 * Usage:
 * $ mkdir db
 * $ sqlite3 db/mcids < init.sql
 */
CREATE TABLE mcids (
       name VARCHAR(32),
       type INTEGER,
       seed DECIMAL(16)
);

INSERT INTO mcids (name, type, seed)
VALUES('development', 1, 0);

INSERT INTO mcids (name, type, seed)
VALUES('test', 2, 0);

INSERT INTO mcids (name, type, seed)
VALUES('employee', 1, 0);

INSERT INTO mcids (name, type, seed)
VALUES('production', 4, 0);
