ALTER TABLE identity_providers
ADD COLUMN format VARCHAR(64);

UPDATE identity_providers
SET format = CONCAT(website, '%')
WHERE format IS NULL;
