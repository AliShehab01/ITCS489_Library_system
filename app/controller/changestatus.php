<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../models/dbconnect.php';

$input = json_decode(file_get_contents('php://input'), true);
$isbn = $input['isbn'] ?? '';
$status = $input['status'] ?? '';

if (empty($isbn) || empty($status)) {
    echo json_encode(['error' => 'ISBN and status are required']);
    exit;
}

if (!in_array($status, ['available', 'unavailable', 'reserved'])) {
    echo json_encode(['error' => 'Invalid status']);
    exit;
}

$db = new Database();
$conn = $db->conn;

try {
    $stmt = $conn->prepare("UPDATE books SET status = :status WHERE isbn = :isbn");
    $stmt->execute([':status' => $status, ':isbn' => $isbn]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['message' => 'Status updated successfully']);
    } else {
        echo json_encode(['error' => 'Book not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Failed to update status']);
}
