USE `data-challenge`;

CREATE TABLE User (
  id              INT PRIMARY KEY AUTO_INCREMENT,
  name            VARCHAR(255) NOT NULL,
  hashed_password VARCHAR(255) NOT NULL,
  email           VARCHAR(255) NOT NULL,
  created_at      DATETIME     NOT NULL,
  last_login      DATETIME        DEFAULT NULL
);

SELECT *
FROM User;