<?php

require_once "db_connOfAli.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents('php://input'), true); // Parse JSON input

$isbn = $data['isbn'] ?? null;

try {
    $query = "DELETE FROM books where isbn= :isbn";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':isbn', $isbn);

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => "Book deleted successfully"]);
    } else {
        echo json_encode(["error" => "No book found with this ISBN"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
