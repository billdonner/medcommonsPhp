CREATE TABLE document_type (
  dt_id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  dt_account_id VARCHAR(20) NOT NULL,
  dt_type varchar(30) NOT NULL,
  dt_tracking_number VARCHAR(20) NOT NULL,
  dt_privacy_level varchar(30) NOT NULL,
  primary KEY(dt_id)
);
CREATE INDEX idx_dt_account_id ON document_type (dt_account_id ASC);
CREATE INDEX idx_dt_tracking_number ON document_type (dt_tracking_number ASC);
CREATE INDEX idx_dt_type ON document_type (dt_type ASC);
