<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../models/dbconnect.php';

$input = json_decode(file_get_contents('php://input'), true);
$isbn = $input['isbn'] ?? '';

if (empty($isbn)) {
    echo json_encode(['error' => 'ISBN is required']);
    exit;
}

$db = new Database();
$conn = $db->conn;

try {
    $stmt = $conn->prepare("DELETE FROM books WHERE isbn = :isbn");
    $stmt->execute([':isbn' => $isbn]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => 'Book deleted successfully']);
    } else {
        echo json_encode(['error' => 'Book not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Failed to delete book']);
}
