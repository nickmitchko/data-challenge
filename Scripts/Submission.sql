USE `data-challenge`;

CREATE TABLE Submission (
  id           INT PRIMARY KEY AUTO_INCREMENT,
  user_id      INT           NOT NULL,
  challenge_id INT           NOT NULL,
  score        DOUBLE(10, 9) NOT NULL,
  accuracy     DOUBLE(10, 9) NOT NULL,
  submitted_at DATETIME      NOT NULL,
  FOREIGN KEY (user_id) REFERENCES User (id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  FOREIGN KEY (challenge_id) REFERENCES Challenge (id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);


DROP TABLE Submission;