<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../models/dbconnect.php';

$db = new Database();
$conn = $db->conn;

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Add new book
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $isbn = trim($_POST['isbn'] ?? '');
        $category = $_POST['category'] ?? null;
        $publisher = trim($_POST['publisher'] ?? '');
        $year = (int)($_POST['year'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if (empty($title) || empty($author) || empty($isbn)) {
            echo json_encode(['error' => 'Title, author, and ISBN are required']);
            exit;
        }

        // Handle image upload
        $imagePath = 'placeholder.jpg';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = uniqid() . '_' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $imagePath = 'public/uploads/' . $filename;
            }
        }

        try {
            $stmt = $conn->prepare("INSERT INTO books (image_path, title, author, isbn, category, publisher, year, quantity, status) VALUES (:img, :title, :author, :isbn, :cat, :pub, :year, :qty, 'available')");
            $stmt->execute([
                ':img' => $imagePath,
                ':title' => $title,
                ':author' => $author,
                ':isbn' => $isbn,
                ':cat' => $category,
                ':pub' => $publisher,
                ':year' => $year,
                ':qty' => $quantity
            ]);
            echo json_encode(['message' => 'Book added successfully', 'id' => $conn->lastInsertId()]);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Failed to add book: ' . $e->getMessage()]);
        }
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $isbn = $input['isbn'] ?? '';

        if (empty($isbn)) {
            echo json_encode(['error' => 'ISBN is required']);
            exit;
        }

        $fields = [];
        $params = [':isbn' => $isbn];

        if (!empty($input['title'])) {
            $fields[] = "title = :title";
            $params[':title'] = $input['title'];
        }
        if (!empty($input['author'])) {
            $fields[] = "author = :author";
            $params[':author'] = $input['author'];
        }
        if (!empty($input['category'])) {
            $fields[] = "category = :category";
            $params[':category'] = $input['category'];
        }
        if (!empty($input['publisher'])) {
            $fields[] = "publisher = :publisher";
            $params[':publisher'] = $input['publisher'];
        }
        if (isset($input['year'])) {
            $fields[] = "year = :year";
            $params[':year'] = (int)$input['year'];
        }

        if (empty($fields)) {
            echo json_encode(['error' => 'No fields to update']);
            exit;
        }

        try {
            $sql = "UPDATE books SET " . implode(', ', $fields) . " WHERE isbn = :isbn";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['message' => 'Book updated successfully']);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Failed to update book']);
        }
        break;

    default:
        echo json_encode(['error' => 'Method not allowed']);
}
