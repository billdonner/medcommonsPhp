DROP DATABASE IF EXISTS mcxgood;
DROP DATABASE IF EXISTS mcxtest;

CREATE DATABASE mcxgood;
CREATE DATABASE mcxtest;

GRANT ALL ON mcxgood.* TO 'medcommons'@'localhost';
GRANT ALL ON mcxtest.* TO 'medcommons'@'localhost';
