-- dummy_seed_all.sql
SET NAMES utf8mb4;
START TRANSACTION;

-- === USERS (24 rows) ===
INSERT IGNORE INTO users (username, password, firstName, lastName, email, phoneNumber, currentNumOfBorrows, role) VALUES
  ('u01', 'pass123', 'Ava', 'Adams', 'ava.adams@example.com', '+1-202-555-1101', 1, 'Admin'),
  ('u02', 'pass123', 'Ben', 'Baker', 'ben.baker@example.com', '+1-202-555-1102', 2, 'Staff'),
  ('u03', 'pass123', 'Cara', 'Chen', 'cara.chen@example.com', '+1-202-555-1103', 0, 'VIPStudent'),
  ('u04', 'pass123', 'Dan', 'Diaz', 'dan.diaz@example.com', '+1-202-555-1104', 1, 'Student'),
  ('u05', 'pass123', 'Ella', 'Evans', 'ella.evans@example.com', '+1-202-555-1105', 2, 'Student'),
  ('u06', 'pass123', 'Finn', 'Foster', 'finn.foster@example.com', '+1-202-555-1106', 0, 'Student'),
  ('u07', 'pass123', 'Gio', 'Gupta', 'gio.gupta@example.com', '+1-202-555-1107', 1, 'Admin'),
  ('u08', 'pass123', 'Hana', 'Hughes', 'hana.hughes@example.com', '+1-202-555-1108', 2, 'Staff'),
  ('u09', 'pass123', 'Ian', 'Ivanov', 'ian.ivanov@example.com', '+1-202-555-1109', 0, 'VIPStudent'),
  ('u10', 'pass123', 'Jade', 'Jackson', 'jade.jackson@example.com', '+1-202-555-1110', 1, 'Student'),
  ('u11', 'pass123', 'Kian', 'Khan', 'kian.khan@example.com', '+1-202-555-1111', 2, 'Student'),
  ('u12', 'pass123', 'Lia', 'Lopez', 'lia.lopez@example.com', '+1-202-555-1112', 0, 'Student'),
  ('u13', 'pass123', 'Milo', 'Meyer', 'milo.meyer@example.com', '+1-202-555-1113', 1, 'Admin'),
  ('u14', 'pass123', 'Nia', 'Ng', 'nia.ng@example.com', '+1-202-555-1114', 2, 'Staff'),
  ('u15', 'pass123', 'Omar', 'Olsen', 'omar.olsen@example.com', '+1-202-555-1115', 0, 'VIPStudent'),
  ('u16', 'pass123', 'Pia', 'Park', 'pia.park@example.com', '+1-202-555-1116', 1, 'Student'),
  ('u17', 'pass123', 'Quinn', 'Quincy', 'quinn.quincy@example.com', '+1-202-555-1117', 2, 'Student'),
  ('u18', 'pass123', 'Rae', 'Reed', 'rae.reed@example.com', '+1-202-555-1118', 0, 'Student'),
  ('u19', 'pass123', 'Sami', 'Singh', 'sami.singh@example.com', '+1-202-555-1119', 1, 'Admin'),
  ('u20', 'pass123', 'Tess', 'Tran', 'tess.tran@example.com', '+1-202-555-1120', 2, 'Staff'),
  ('u21', 'pass123', 'Uma', 'Usman', 'uma.usman@example.com', '+1-202-555-1121', 0, 'VIPStudent'),
  ('u22', 'pass123', 'Vik', 'Vega', 'vik.vega@example.com', '+1-202-555-1122', 1, 'Student'),
  ('u23', 'pass123', 'Wren', 'Wong', 'wren.wong@example.com', '+1-202-555-1123', 2, 'Student'),
  ('u24', 'pass123', 'Yara', 'Yusuf', 'yara.yusuf@example.com', '+1-202-555-1124', 0, 'Student');

-- === BOOKS (24 rows) ===
INSERT IGNORE INTO books (image_path, title, author, isbn, category, publisher, year, quantity, status) VALUES
  ('placeholder.jpg', 'Test Book 01', 'Author 01', '97810000000001', 'Science', 'Publisher 1', 2001, 1, 'available'),
  ('placeholder.jpg', 'Test Book 02', 'Author 02', '97810000000002', 'Engineering', 'Publisher 2', 2002, 2, 'reserved'),
  ('placeholder.jpg', 'Test Book 03', 'Author 03', '97810000000003', 'History', 'Publisher 3', 2003, 3, 'unavailable'),
  ('placeholder.jpg', 'Test Book 04', 'Author 04', '97810000000004', 'Literature', 'Publisher 4', 2004, 4, 'available'),
  ('placeholder.jpg', 'Test Book 05', 'Author 05', '97810000000005', 'Business', 'Publisher 5', 2005, 5, 'reserved'),
  ('placeholder.jpg', 'Test Book 06', 'Author 06', '97810000000006', 'Science', 'Publisher 6', 2006, 0, 'unavailable'),
  ('placeholder.jpg', 'Test Book 07', 'Author 07', '97810000000007', 'Engineering', 'Publisher 7', 2007, 1, 'available'),
  ('placeholder.jpg', 'Test Book 08', 'Author 08', '97810000000008', 'History', 'Publisher 1', 2008, 2, 'reserved'),
  ('placeholder.jpg', 'Test Book 09', 'Author 09', '97810000000009', 'Literature', 'Publisher 2', 2009, 3, 'unavailable'),
  ('placeholder.jpg', 'Test Book 10', 'Author 10', '97810000000010', 'Business', 'Publisher 3', 2010, 4, 'available'),
  ('placeholder.jpg', 'Test Book 11', 'Author 11', '97810000000011', 'Science', 'Publisher 4', 2011, 5, 'reserved'),
  ('placeholder.jpg', 'Test Book 12', 'Author 12', '97810000000012', 'Engineering', 'Publisher 5', 2012, 0, 'unavailable'),
  ('placeholder.jpg', 'Test Book 13', 'Author 13', '97810000000013', 'History', 'Publisher 6', 2013, 1, 'available'),
  ('placeholder.jpg', 'Test Book 14', 'Author 14', '97810000000014', 'Literature', 'Publisher 7', 2014, 2, 'reserved'),
  ('placeholder.jpg', 'Test Book 15', 'Author 15', '97810000000015', 'Business', 'Publisher 1', 2015, 3, 'unavailable'),
  ('placeholder.jpg', 'Test Book 16', 'Author 16', '97810000000016', 'Science', 'Publisher 2', 2016, 4, 'available'),
  ('placeholder.jpg', 'Test Book 17', 'Author 17', '97810000000017', 'Engineering', 'Publisher 3', 2017, 5, 'reserved'),
  ('placeholder.jpg', 'Test Book 18', 'Author 18', '97810000000018', 'History', 'Publisher 4', 2018, 0, 'unavailable'),
  ('placeholder.jpg', 'Test Book 19', 'Author 19', '97810000000019', 'Literature', 'Publisher 5', 2019, 1, 'available'),
  ('placeholder.jpg', 'Test Book 20', 'Author 20', '97810000000020', 'Business', 'Publisher 6', 2020, 2, 'reserved'),
  ('placeholder.jpg', 'Test Book 21', 'Author 21', '97810000000021', 'Science', 'Publisher 7', 2021, 3, 'unavailable'),
  ('placeholder.jpg', 'Test Book 22', 'Author 22', '97810000000022', 'Engineering', 'Publisher 1', 2022, 4, 'available'),
  ('placeholder.jpg', 'Test Book 23', 'Author 23', '97810000000023', 'History', 'Publisher 2', 2023, 5, 'reserved'),
  ('placeholder.jpg', 'Test Book 24', 'Author 24', '97810000000024', 'Literature', 'Publisher 3', 2000, 0, 'unavailable');

-- === BORROWS (24 rows) ===
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 0, DATE('2025-10-27'), 'false', u.id
FROM users u JOIN books b ON u.username='u01' AND b.isbn='97810000000001'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-10-27')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 0, DATE('2025-10-28'), 'false', u.id
FROM users u JOIN books b ON u.username='u02' AND b.isbn='97810000000002'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-10-28')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 0, DATE('2025-10-29'), 'false', u.id
FROM users u JOIN books b ON u.username='u03' AND b.isbn='97810000000003'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-10-29')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 5, DATE('2025-10-30'), 'false', u.id
FROM users u JOIN books b ON u.username='u04' AND b.isbn='97810000000004'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-10-30')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 2, 0, DATE('2025-10-31'), 'false', u.id
FROM users u JOIN books b ON u.username='u05' AND b.isbn='97810000000005'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-10-31')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 0, DATE('2025-11-01'), 'true', u.id
FROM users u JOIN books b ON u.username='u06' AND b.isbn='97810000000006'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-01')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 0, DATE('2025-11-02'), 'false', u.id
FROM users u JOIN books b ON u.username='u07' AND b.isbn='97810000000007'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-02')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 5, DATE('2025-11-03'), 'false', u.id
FROM users u JOIN books b ON u.username='u08' AND b.isbn='97810000000008'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-03')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 0, DATE('2025-11-04'), 'false', u.id
FROM users u JOIN books b ON u.username='u09' AND b.isbn='97810000000009'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-04')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 2, 0, DATE('2025-11-05'), 'false', u.id
FROM users u JOIN books b ON u.username='u10' AND b.isbn='97810000000010'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-05')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 0, DATE('2025-11-06'), 'false', u.id
FROM users u JOIN books b ON u.username='u11' AND b.isbn='97810000000011'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-06')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 5, DATE('2025-11-07'), 'true', u.id
FROM users u JOIN books b ON u.username='u12' AND b.isbn='97810000000012'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-07')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 0, DATE('2025-11-08'), 'false', u.id
FROM users u JOIN books b ON u.username='u13' AND b.isbn='97810000000013'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-08')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 0, DATE('2025-11-09'), 'false', u.id
FROM users u JOIN books b ON u.username='u14' AND b.isbn='97810000000014'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-09')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 2, 0, DATE('2025-11-10'), 'false', u.id
FROM users u JOIN books b ON u.username='u15' AND b.isbn='97810000000015'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-10')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 5, DATE('2025-11-11'), 'false', u.id
FROM users u JOIN books b ON u.username='u16' AND b.isbn='97810000000016'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-11')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 0, DATE('2025-11-12'), 'false', u.id
FROM users u JOIN books b ON u.username='u17' AND b.isbn='97810000000017'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-12')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 0, DATE('2025-11-13'), 'true', u.id
FROM users u JOIN books b ON u.username='u18' AND b.isbn='97810000000018'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-13')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 0, DATE('2025-11-14'), 'false', u.id
FROM users u JOIN books b ON u.username='u19' AND b.isbn='97810000000019'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-14')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 2, 5, DATE('2025-11-15'), 'false', u.id
FROM users u JOIN books b ON u.username='u20' AND b.isbn='97810000000020'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-15')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 0, DATE('2025-11-16'), 'false', u.id
FROM users u JOIN books b ON u.username='u21' AND b.isbn='97810000000021'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-16')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 0, DATE('2025-11-17'), 'false', u.id
FROM users u JOIN books b ON u.username='u22' AND b.isbn='97810000000022'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-17')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 0, DATE('2025-11-18'), 'false', u.id
FROM users u JOIN books b ON u.username='u23' AND b.isbn='97810000000023'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-18')
);
INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
SELECT b.id, 1, 5, DATE('2025-11-19'), 'true', u.id
FROM users u JOIN books b ON u.username='u24' AND b.isbn='97810000000024'
WHERE NOT EXISTS (
  SELECT 1 FROM borrows br WHERE br.user_id = u.id AND br.bookId = b.id AND br.dueDate = DATE('2025-11-19')
);

-- === RESERVATIONS (24 rows) ===
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'active'
FROM users u JOIN books b ON u.username='u01' AND b.isbn='97810000000006'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'active'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'notified'
FROM users u JOIN books b ON u.username='u02' AND b.isbn='97810000000007'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'notified'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'fulfilled'
FROM users u JOIN books b ON u.username='u03' AND b.isbn='97810000000008'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'fulfilled'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'cancelled'
FROM users u JOIN books b ON u.username='u04' AND b.isbn='97810000000009'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'cancelled'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'active'
FROM users u JOIN books b ON u.username='u05' AND b.isbn='97810000000010'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'active'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'notified'
FROM users u JOIN books b ON u.username='u06' AND b.isbn='97810000000011'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'notified'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'fulfilled'
FROM users u JOIN books b ON u.username='u07' AND b.isbn='97810000000012'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'fulfilled'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'cancelled'
FROM users u JOIN books b ON u.username='u08' AND b.isbn='97810000000013'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'cancelled'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'active'
FROM users u JOIN books b ON u.username='u09' AND b.isbn='97810000000014'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'active'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'notified'
FROM users u JOIN books b ON u.username='u10' AND b.isbn='97810000000015'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'notified'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'fulfilled'
FROM users u JOIN books b ON u.username='u11' AND b.isbn='97810000000016'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'fulfilled'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'cancelled'
FROM users u JOIN books b ON u.username='u12' AND b.isbn='97810000000017'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'cancelled'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'active'
FROM users u JOIN books b ON u.username='u13' AND b.isbn='97810000000018'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'active'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'notified'
FROM users u JOIN books b ON u.username='u14' AND b.isbn='97810000000019'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'notified'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'fulfilled'
FROM users u JOIN books b ON u.username='u15' AND b.isbn='97810000000020'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'fulfilled'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'cancelled'
FROM users u JOIN books b ON u.username='u16' AND b.isbn='97810000000021'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'cancelled'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'active'
FROM users u JOIN books b ON u.username='u17' AND b.isbn='97810000000022'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'active'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'notified'
FROM users u JOIN books b ON u.username='u18' AND b.isbn='97810000000023'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'notified'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'fulfilled'
FROM users u JOIN books b ON u.username='u19' AND b.isbn='97810000000024'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'fulfilled'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'cancelled'
FROM users u JOIN books b ON u.username='u20' AND b.isbn='97810000000001'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'cancelled'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'active'
FROM users u JOIN books b ON u.username='u21' AND b.isbn='97810000000002'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'active'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'notified'
FROM users u JOIN books b ON u.username='u22' AND b.isbn='97810000000003'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'notified'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'fulfilled'
FROM users u JOIN books b ON u.username='u23' AND b.isbn='97810000000004'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'fulfilled'
);
INSERT INTO reservations (user_id, book_id, status)
SELECT u.id, b.id, 'cancelled'
FROM users u JOIN books b ON u.username='u24' AND b.isbn='97810000000005'
WHERE NOT EXISTS (
  SELECT 1 FROM reservations r WHERE r.user_id = u.id AND r.book_id = b.id AND r.status = 'cancelled'
);

-- === NOTIFICATIONS (>=24 rows) ===
INSERT INTO notifications (user_id, book_id, type, title, message, due_date, is_read, context_type, context_id)
SELECT u.id, b.id, 'due',
       CONCAT('Due soon: ', b.title),
       'Please return or renew before the due date.',
       DATE('2025-11-10'), 0, 'borrow', br.borrow_id
FROM users u
JOIN borrows br ON br.user_id = u.id
JOIN books b ON br.bookId = b.id
WHERE u.username='u15' AND b.isbn='97810000000015' AND br.dueDate = DATE('2025-11-10') AND br.isReturned='false'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='borrow' AND n.context_id=br.borrow_id AND n.type='due'
);
INSERT INTO notifications (user_id, book_id, type, title, message, due_date, is_read, context_type, context_id)
SELECT u.id, b.id, 'due',
       CONCAT('Due soon: ', b.title),
       'Please return or renew before the due date.',
       DATE('2025-11-11'), 0, 'borrow', br.borrow_id
FROM users u
JOIN borrows br ON br.user_id = u.id
JOIN books b ON br.bookId = b.id
WHERE u.username='u16' AND b.isbn='97810000000016' AND br.dueDate = DATE('2025-11-11') AND br.isReturned='false'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='borrow' AND n.context_id=br.borrow_id AND n.type='due'
);
INSERT INTO notifications (user_id, book_id, type, title, message, due_date, is_read, context_type, context_id)
SELECT u.id, b.id, 'due',
       CONCAT('Due soon: ', b.title),
       'Please return or renew before the due date.',
       DATE('2025-11-12'), 0, 'borrow', br.borrow_id
FROM users u
JOIN borrows br ON br.user_id = u.id
JOIN books b ON br.bookId = b.id
WHERE u.username='u17' AND b.isbn='97810000000017' AND br.dueDate = DATE('2025-11-12') AND br.isReturned='false'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='borrow' AND n.context_id=br.borrow_id AND n.type='due'
);
INSERT INTO notifications (user_id, book_id, type, title, message, due_date, is_read, context_type, context_id)
SELECT u.id, b.id, 'due',
       CONCAT('Due soon: ', b.title),
       'Please return or renew before the due date.',
       DATE('2025-11-14'), 0, 'borrow', br.borrow_id
FROM users u
JOIN borrows br ON br.user_id = u.id
JOIN books b ON br.bookId = b.id
WHERE u.username='u19' AND b.isbn='97810000000019' AND br.dueDate = DATE('2025-11-14') AND br.isReturned='false'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='borrow' AND n.context_id=br.borrow_id AND n.type='due'
);
INSERT INTO notifications (user_id, book_id, type, title, message, due_date, is_read, context_type, context_id)
SELECT u.id, b.id, 'due',
       CONCAT('Due soon: ', b.title),
       'Please return or renew before the due date.',
       DATE('2025-11-15'), 0, 'borrow', br.borrow_id
FROM users u
JOIN borrows br ON br.user_id = u.id
JOIN books b ON br.bookId = b.id
WHERE u.username='u20' AND b.isbn='97810000000020' AND br.dueDate = DATE('2025-11-15') AND br.isReturned='false'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='borrow' AND n.context_id=br.borrow_id AND n.type='due'
);
INSERT INTO notifications (user_id, book_id, type, title, message, due_date, is_read, context_type, context_id)
SELECT u.id, b.id, 'due',
       CONCAT('Due soon: ', b.title),
       'Please return or renew before the due date.',
       DATE('2025-11-16'), 0, 'borrow', br.borrow_id
FROM users u
JOIN borrows br ON br.user_id = u.id
JOIN books b ON br.bookId = b.id
WHERE u.username='u21' AND b.isbn='97810000000021' AND br.dueDate = DATE('2025-11-16') AND br.isReturned='false'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='borrow' AND n.context_id=br.borrow_id AND n.type='due'
);
INSERT INTO notifications (user_id, book_id, type, title, message, due_date, is_read, context_type, context_id)
SELECT u.id, b.id, 'due',
       CONCAT('Due soon: ', b.title),
       'Please return or renew before the due date.',
       DATE('2025-11-17'), 0, 'borrow', br.borrow_id
FROM users u
JOIN borrows br ON br.user_id = u.id
JOIN books b ON br.bookId = b.id
WHERE u.username='u22' AND b.isbn='97810000000022' AND br.dueDate = DATE('2025-11-17') AND br.isReturned='false'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='borrow' AND n.context_id=br.borrow_id AND n.type='due'
);
INSERT INTO notifications (user_id, book_id, type, title, message, due_date, is_read, context_type, context_id)
SELECT u.id, b.id, 'due',
       CONCAT('Due soon: ', b.title),
       'Please return or renew before the due date.',
       DATE('2025-11-18'), 0, 'borrow', br.borrow_id
FROM users u
JOIN borrows br ON br.user_id = u.id
JOIN books b ON br.bookId = b.id
WHERE u.username='u23' AND b.isbn='97810000000023' AND br.dueDate = DATE('2025-11-18') AND br.isReturned='false'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='borrow' AND n.context_id=br.borrow_id AND n.type='due'
);
INSERT INTO notifications (user_id, book_id, type, title, message, due_date, is_read, context_type, context_id)
SELECT u.id, b.id, 'overdue',
       CONCAT('Overdue: ', b.title),
       'This item is overdue. Return immediately to avoid fees.',
       DATE('2025-10-27'), 0, 'borrow', br.borrow_id
FROM users u
JOIN borrows br ON br.user_id = u.id
JOIN books b ON br.bookId = b.id
WHERE u.username='u01' AND b.isbn='97810000000001' AND br.dueDate = DATE('2025-10-27') AND br.isReturned='false'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='borrow' AND n.context_id=br.borrow_id AND n.type='overdue'
);
INSERT INTO notifications (user_id, book_id, type, title, message, due_date, is_read, context_type, context_id)
SELECT u.id, b.id, 'overdue',
       CONCAT('Overdue: ', b.title),
       'This item is overdue. Return immediately to avoid fees.',
       DATE('2025-10-28'), 0, 'borrow', br.borrow_id
FROM users u
JOIN borrows br ON br.user_id = u.id
JOIN books b ON br.bookId = b.id
WHERE u.username='u02' AND b.isbn='97810000000002' AND br.dueDate = DATE('2025-10-28') AND br.isReturned='false'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='borrow' AND n.context_id=br.borrow_id AND n.type='overdue'
);
INSERT INTO notifications (user_id, book_id, type, title, message, due_date, is_read, context_type, context_id)
SELECT u.id, b.id, 'overdue',
       CONCAT('Overdue: ', b.title),
       'This item is overdue. Return immediately to avoid fees.',
       DATE('2025-10-29'), 0, 'borrow', br.borrow_id
FROM users u
JOIN borrows br ON br.user_id = u.id
JOIN books b ON br.bookId = b.id
WHERE u.username='u03' AND b.isbn='97810000000003' AND br.dueDate = DATE('2025-10-29') AND br.isReturned='false'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='borrow' AND n.context_id=br.borrow_id AND n.type='overdue'
);
INSERT INTO notifications (user_id, book_id, type, title, message, due_date, is_read, context_type, context_id)
SELECT u.id, b.id, 'overdue',
       CONCAT('Overdue: ', b.title),
       'This item is overdue. Return immediately to avoid fees.',
       DATE('2025-10-30'), 0, 'borrow', br.borrow_id
FROM users u
JOIN borrows br ON br.user_id = u.id
JOIN books b ON br.bookId = b.id
WHERE u.username='u04' AND b.isbn='97810000000004' AND br.dueDate = DATE('2025-10-30') AND br.isReturned='false'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='borrow' AND n.context_id=br.borrow_id AND n.type='overdue'
);
INSERT INTO notifications (user_id, book_id, type, title, message, due_date, is_read, context_type, context_id)
SELECT u.id, b.id, 'overdue',
       CONCAT('Overdue: ', b.title),
       'This item is overdue. Return immediately to avoid fees.',
       DATE('2025-10-31'), 0, 'borrow', br.borrow_id
FROM users u
JOIN borrows br ON br.user_id = u.id
JOIN books b ON br.bookId = b.id
WHERE u.username='u05' AND b.isbn='97810000000005' AND br.dueDate = DATE('2025-10-31') AND br.isReturned='false'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='borrow' AND n.context_id=br.borrow_id AND n.type='overdue'
);
INSERT INTO notifications (user_id, book_id, type, title, message, due_date, is_read, context_type, context_id)
SELECT u.id, b.id, 'overdue',
       CONCAT('Overdue: ', b.title),
       'This item is overdue. Return immediately to avoid fees.',
       DATE('2025-11-02'), 0, 'borrow', br.borrow_id
FROM users u
JOIN borrows br ON br.user_id = u.id
JOIN books b ON br.bookId = b.id
WHERE u.username='u07' AND b.isbn='97810000000007' AND br.dueDate = DATE('2025-11-02') AND br.isReturned='false'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='borrow' AND n.context_id=br.borrow_id AND n.type='overdue'
);
INSERT INTO notifications (user_id, book_id, type, title, message, due_date, is_read, context_type, context_id)
SELECT u.id, b.id, 'overdue',
       CONCAT('Overdue: ', b.title),
       'This item is overdue. Return immediately to avoid fees.',
       DATE('2025-11-03'), 0, 'borrow', br.borrow_id
FROM users u
JOIN borrows br ON br.user_id = u.id
JOIN books b ON br.bookId = b.id
WHERE u.username='u08' AND b.isbn='97810000000008' AND br.dueDate = DATE('2025-11-03') AND br.isReturned='false'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='borrow' AND n.context_id=br.borrow_id AND n.type='overdue'
);
INSERT INTO notifications (user_id, book_id, type, title, message, due_date, is_read, context_type, context_id)
SELECT u.id, b.id, 'overdue',
       CONCAT('Overdue: ', b.title),
       'This item is overdue. Return immediately to avoid fees.',
       DATE('2025-11-04'), 0, 'borrow', br.borrow_id
FROM users u
JOIN borrows br ON br.user_id = u.id
JOIN books b ON br.bookId = b.id
WHERE u.username='u09' AND b.isbn='97810000000009' AND br.dueDate = DATE('2025-11-04') AND br.isReturned='false'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='borrow' AND n.context_id=br.borrow_id AND n.type='overdue'
);
INSERT INTO notifications (user_id, book_id, type, title, message, is_read, context_type, context_id)
SELECT u.id, b.id, 'reservation',
       CONCAT('Ready for pickup: ', b.title),
       'Your reserved title is now available.',
       0, 'reservation', r.reservation_id
FROM reservations r
JOIN users u ON r.user_id = u.id
JOIN books b ON r.book_id = b.id
WHERE u.username='u02' AND b.isbn='97810000000007' AND r.status='notified'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='reservation' AND n.context_id=r.reservation_id AND n.type='reservation'
);
INSERT INTO notifications (user_id, book_id, type, title, message, is_read, context_type, context_id)
SELECT u.id, b.id, 'reservation',
       CONCAT('Ready for pickup: ', b.title),
       'Your reserved title is now available.',
       0, 'reservation', r.reservation_id
FROM reservations r
JOIN users u ON r.user_id = u.id
JOIN books b ON r.book_id = b.id
WHERE u.username='u06' AND b.isbn='97810000000011' AND r.status='notified'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='reservation' AND n.context_id=r.reservation_id AND n.type='reservation'
);
INSERT INTO notifications (user_id, book_id, type, title, message, is_read, context_type, context_id)
SELECT u.id, b.id, 'reservation',
       CONCAT('Ready for pickup: ', b.title),
       'Your reserved title is now available.',
       0, 'reservation', r.reservation_id
FROM reservations r
JOIN users u ON r.user_id = u.id
JOIN books b ON r.book_id = b.id
WHERE u.username='u10' AND b.isbn='97810000000015' AND r.status='notified'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='reservation' AND n.context_id=r.reservation_id AND n.type='reservation'
);
INSERT INTO notifications (user_id, book_id, type, title, message, is_read, context_type, context_id)
SELECT u.id, b.id, 'reservation',
       CONCAT('Ready for pickup: ', b.title),
       'Your reserved title is now available.',
       0, 'reservation', r.reservation_id
FROM reservations r
JOIN users u ON r.user_id = u.id
JOIN books b ON r.book_id = b.id
WHERE u.username='u14' AND b.isbn='97810000000019' AND r.status='notified'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='reservation' AND n.context_id=r.reservation_id AND n.type='reservation'
);
INSERT INTO notifications (user_id, book_id, type, title, message, is_read, context_type, context_id)
SELECT u.id, b.id, 'reservation',
       CONCAT('Ready for pickup: ', b.title),
       'Your reserved title is now available.',
       0, 'reservation', r.reservation_id
FROM reservations r
JOIN users u ON r.user_id = u.id
JOIN books b ON r.book_id = b.id
WHERE u.username='u18' AND b.isbn='97810000000023' AND r.status='notified'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='reservation' AND n.context_id=r.reservation_id AND n.type='reservation'
);
INSERT INTO notifications (user_id, book_id, type, title, message, is_read, context_type, context_id)
SELECT u.id, b.id, 'reservation',
       CONCAT('Reservation fulfilled: ', b.title),
       'This is a record that your reservation has been fulfilled.',
       1, 'reservation', r.reservation_id
FROM reservations r
JOIN users u ON r.user_id = u.id
JOIN books b ON r.book_id = b.id
WHERE u.username='u03' AND b.isbn='97810000000008' AND r.status='fulfilled'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='reservation' AND n.context_id=r.reservation_id AND n.type='reservation'
);
INSERT INTO notifications (user_id, book_id, type, title, message, is_read, context_type, context_id)
SELECT u.id, b.id, 'reservation',
       CONCAT('Reservation fulfilled: ', b.title),
       'This is a record that your reservation has been fulfilled.',
       1, 'reservation', r.reservation_id
FROM reservations r
JOIN users u ON r.user_id = u.id
JOIN books b ON r.book_id = b.id
WHERE u.username='u07' AND b.isbn='97810000000012' AND r.status='fulfilled'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='reservation' AND n.context_id=r.reservation_id AND n.type='reservation'
);
INSERT INTO notifications (user_id, book_id, type, title, message, is_read, context_type, context_id)
SELECT u.id, b.id, 'reservation',
       CONCAT('Reservation fulfilled: ', b.title),
       'This is a record that your reservation has been fulfilled.',
       1, 'reservation', r.reservation_id
FROM reservations r
JOIN users u ON r.user_id = u.id
JOIN books b ON r.book_id = b.id
WHERE u.username='u11' AND b.isbn='97810000000016' AND r.status='fulfilled'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='reservation' AND n.context_id=r.reservation_id AND n.type='reservation'
);
INSERT INTO notifications (user_id, book_id, type, title, message, is_read, context_type, context_id)
SELECT u.id, b.id, 'reservation',
       CONCAT('Reservation fulfilled: ', b.title),
       'This is a record that your reservation has been fulfilled.',
       1, 'reservation', r.reservation_id
FROM reservations r
JOIN users u ON r.user_id = u.id
JOIN books b ON r.book_id = b.id
WHERE u.username='u15' AND b.isbn='97810000000020' AND r.status='fulfilled'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='reservation' AND n.context_id=r.reservation_id AND n.type='reservation'
);
INSERT INTO notifications (user_id, book_id, type, title, message, is_read, context_type, context_id)
SELECT u.id, b.id, 'reservation',
       CONCAT('Reservation fulfilled: ', b.title),
       'This is a record that your reservation has been fulfilled.',
       1, 'reservation', r.reservation_id
FROM reservations r
JOIN users u ON r.user_id = u.id
JOIN books b ON r.book_id = b.id
WHERE u.username='u19' AND b.isbn='97810000000024' AND r.status='fulfilled'
AND NOT EXISTS (
  SELECT 1 FROM notifications n WHERE n.context_type='reservation' AND n.context_id=r.reservation_id AND n.type='reservation'
);
INSERT INTO notifications (user_id, book_id, type, title, message, is_read)
VALUES (NULL, NULL, 'announcement', 'System Announcement 1', 'This is announcement #1.', 0);
INSERT INTO notifications (user_id, book_id, type, title, message, is_read)
VALUES (NULL, NULL, 'announcement', 'System Announcement 2', 'This is announcement #2.', 0);
INSERT INTO notifications (user_id, book_id, type, title, message, is_read)
VALUES (NULL, NULL, 'announcement', 'System Announcement 3', 'This is announcement #3.', 0);
INSERT INTO notifications (user_id, book_id, type, title, message, is_read)
VALUES (NULL, NULL, 'announcement', 'System Announcement 4', 'This is announcement #4.', 0);
INSERT INTO notifications (user_id, book_id, type, title, message, is_read)
VALUES (NULL, NULL, 'announcement', 'System Announcement 5', 'This is announcement #5.', 0);
INSERT INTO notifications (user_id, book_id, type, title, message, is_read)
VALUES (NULL, NULL, 'announcement', 'System Announcement 6', 'This is announcement #6.', 0);

-- === SYSTEM CONFIG (default policies) ===
INSERT IGNORE INTO system_config (config_key, config_value, description) VALUES
  ('loan_days_student', '14', 'Default loan duration for Students (days)'),
  ('loan_days_vipstudent', '30', 'Default loan duration for VIP Students (days)'),
  ('loan_days_staff', '60', 'Default loan duration for Staff (days)'),
  ('loan_days_admin', '60', 'Default loan duration for Admin (days)'),
  ('borrow_limit_student', '3', 'Max books a Student can borrow'),
  ('borrow_limit_vipstudent', '7', 'Max books a VIP Student can borrow'),
  ('borrow_limit_staff', '10', 'Max books Staff can borrow'),
  ('borrow_limit_admin', '10', 'Max books Admin can borrow'),
  ('fine_rate_per_day', '1.00', 'Fine rate per day for overdue books ($)'),
  ('max_renewals', '2', 'Maximum number of renewals allowed'),
  ('reservation_limit', '5', 'Max active reservations per user'),
  ('reservation_expiry_days', '3', 'Days before a notified reservation expires');

-- === SAMPLE AUDIT LOGS ===
INSERT INTO audit_logs (user_id, username, action, entity_type, entity_id, details, ip_address) VALUES
  (1, 'u01', 'LOGIN', 'user', 1, 'User logged in successfully', '127.0.0.1'),
  (1, 'u01', 'BORROW_BOOK', 'book', 1, 'Borrowed Test Book 01', '127.0.0.1'),
  (2, 'u02', 'LOGIN', 'user', 2, 'User logged in successfully', '127.0.0.1'),
  (7, 'u07', 'UPDATE_USER', 'user', 4, 'Changed role from Student to VIPStudent', '127.0.0.1'),
  (1, 'u01', 'CREATE_BACKUP', 'system', NULL, 'Created system backup', '127.0.0.1'),
  (7, 'u07', 'UPDATE_POLICIES', 'system_config', NULL, 'Updated 3 policy settings', '127.0.0.1');

COMMIT;