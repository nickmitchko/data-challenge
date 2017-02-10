CREATE TABLE Challenge (
  id          INT PRIMARY KEY AUTO_INCREMENT,
  creator_id  INT                NOT NULL,
  name        VARCHAR(255)       NOT NULL,
  answer_file VARCHAR(255)       NOT NULL,
  created_at  DATETIME           NOT NULL,
  enabled     BOOL DEFAULT FALSE NOT NULL,
  about       VARCHAR(255),
  FOREIGN KEY (creator_id) REFERENCES User (id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);
USE `data-challenge`;
SELECT *
FROM Challenge;
SELECT *
FROM User;

DELETE FROM Challenge
WHERE 1 = 1;
DROP TABLE Challenge;