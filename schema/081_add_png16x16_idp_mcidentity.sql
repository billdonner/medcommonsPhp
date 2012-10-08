ALTER TABLE identity_providers
DROP COLUMN logo;

ALTER TABLE identity_providers
ADD COLUMN png16x16 BLOB;
