ALTER TABLE ccrlog DROP COLUMN samplidp;
ALTER TABLE ccrlog ADD idp varchar(255) NOT NULL;
