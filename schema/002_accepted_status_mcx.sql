ALTER TABLE rights DROP COLUMN accepted_time;
ALTER TABLE rights ADD accepted_status VARCHAR(30);
