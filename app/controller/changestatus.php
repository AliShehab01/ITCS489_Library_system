<?php
header("Content-Type: application/json");

// Include DB connection
require_once __DIR__ . '/../models/db489.php';
$db = new Database();
$conn = $db->conn; // $conn is your PDO object

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['isbn']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(["error" => "ISBN and status are required"]);
    exit;
}
$isbn = trim($data['isbn']);
$status = trim($data['status']);

$allowedStatuses = ['available', 'unavailable'];

if (!in_array($status, $allowedStatuses)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid status value"]);
    exit;
}

try {
    $query = "UPDATE books SET status= :status where isbn= :isbn";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':isbn', $isbn);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo json_encode(['message' => "Book status updated successfully"]);
    } else {
        echo json_encode(["error" => "Book not found or status unchanged"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}