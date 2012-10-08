CREATE TABLE cover (
  cover_id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  cover_account_id VARCHAR(20) NOT NULL,
  cover_notification VARCHAR(120) NULL,
  cover_encrypted_pin VARCHAR(64) NULL,
  primary KEY(cover_id)
);
