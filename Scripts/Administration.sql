CREATE TABLE Administration (
  id      INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  FOREIGN KEY (user_id) REFERENCES User (id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);

