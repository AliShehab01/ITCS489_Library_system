

-- Create DB IF NOT EXISTS
CREATE DATABASE IF NOT EXISTS `library`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `library`;

-- Create table IF NOT EXISTS
CREATE TABLE IF NOT EXISTS `books` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `author` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `availability` ENUM('available','issued','reserved') DEFAULT 'available',
  `isbn` VARCHAR(50) DEFAULT NULL,
  `publication_year` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE INDEX IF NOT EXISTS `idx_title`   ON `books` (`title`);
CREATE INDEX IF NOT EXISTS `idx_author`  ON `books` (`author`);
CREATE INDEX IF NOT EXISTS `idx_isbn`    ON `books` (`isbn`);
CREATE INDEX IF NOT EXISTS `idx_category`ON `books` (`category`);
CREATE INDEX IF NOT EXISTS `idx_pubyear` ON `books` (`publication_year`);
CREATE INDEX IF NOT EXISTS `idx_created` ON `books` (`created_at`);

-- insert data to the table 


INSERT INTO `books` (`title`,`author`,`category`,`availability`,`isbn`,`publication_year`) VALUES
('Introduction to Algorithms','Thomas H. Cormen','Engineering','available','9780262046305',2009),
('Clean Code','Robert C. Martin','Engineering','available','9780132350884',2008),
('Design Patterns','Erich Gamma','Engineering','issued','9780201633610',1994),
('Artificial Intelligence: A Modern Approach','Stuart Russell','Engineering','reserved','9780134610993',2020),
('Operating System Concepts','Abraham Silberschatz','Engineering','available','9781119800361',2021),
('Computer Networks','Andrew S. Tanenbaum','Engineering','available','9780132126953',2010),
('The Pragmatic Programmer','Andrew Hunt','Engineering','issued','9780135957059',2019),
('Database System Concepts','Abraham Silberschatz','Engineering','available','9781259921433',2019),
('Modern Operating Systems','Andrew S. Tanenbaum','Engineering','reserved','9780133591620',2014),
('Deep Learning','Ian Goodfellow','Engineering','available','9780262035613',2016),
('A Brief History of Time','Stephen Hawking','History','available','9780553380163',1988),
('Sapiens','Yuval Noah Harari','History','issued','9780062316097',2015),
('Guns, Germs, and Steel','Jared Diamond','History','available','9780393317558',1999),
('Pride and Prejudice','Jane Austen','Literature','available','9781503290563',1813),
('1984','George Orwell','Literature','issued','9780451524935',1949),
('To Kill a Mockingbird','Harper Lee','Literature','reserved','9780061120084',1960),
('Principles','Ray Dalio','Business','available','9781501124020',2017),
('Zero to One','Peter Thiel','Business','available','9780804139298',2014),
('The Lean Startup','Eric Ries','Business','issued','9780307887894',2011),
('Good to Great','Jim Collins','Business','reserved','9780066620992',2001),
('The Mythical Man-Month','Frederick P. Brooks Jr.','Engineering','available','9780201835953',1995),
('Refactoring','Martin Fowler','Engineering','available','9780201485677',1999);


