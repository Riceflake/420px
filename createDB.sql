DROP DATABASE IF EXISTS mti_db;

FLUSH PRIVILEGES;
GRANT USAGE ON *.* TO 'zhang_f'@localhost;
DROP USER 'zhang_f'@localhost;

CREATE DATABASE mti_db;
USE mti_db;

GRANT SELECT, INSERT, UPDATE, DELETE ON mti_db.* TO 'zhang_f'@'localhost' IDENTIFIED BY 'password';

CREATE TABLE t_user (
  email                VARCHAR(128)         NOT NULL UNIQUE,
  password             VARCHAR(128)         NOT NULL,

  PRIMARY KEY(email)
);

CREATE TABLE t_image (
  id                   SERIAL               NOT NULL UNIQUE,
  email                VARCHAR(128)         NOT NULL,
  extension            VARCHAR(128)         NOT NULL,
  dominant             VARCHAR(128)         NOT NULL,

  FOREIGN KEY(email)                  REFERENCES t_user(email)
);

INSERT INTO `t_user`(`email`, `password`) VALUES ("email","password");
INSERT INTO `t_user`(`email`, `password`) VALUES ("a@a.com","a");