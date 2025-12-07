<?php
// Prevent any output before JSON
ob_start();

// Disable error display in output
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Set JSON header immediately
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'library_system';

// Clear any buffered output
ob_clean();

try {
  // Create connection
  $conn = @new mysqli($host, $user, $pass, $dbname);

  if ($conn->connect_errno) {
    throw new Exception("Database connection failed: " . $conn->connect_error);
  }

  $conn->set_charset('utf8mb4');

  // Check if books table exists
  $tableCheck = $conn->query("SHOW TABLES LIKE 'books'");
  if (!$tableCheck || $tableCheck->num_rows === 0) {
    // Return empty array if table doesn't exist
    echo json_encode(["data" => [], "message" => "No books table found"]);
    exit;
  }

  // Get all books
  $sql = "SELECT id, title, author, isbn, category, status, quantity, publisher, year, image_path, created_at FROM books ORDER BY id DESC";
  $result = $conn->query($sql);

  if (!$result) {
    throw new Exception("Query failed: " . $conn->error);
  }

  $books = [];
  while ($row = $result->fetch_assoc()) {
    $books[] = [
      "id" => (int)$row["id"],
      "title" => $row["title"] ?? '',
      "author" => $row["author"] ?? '',
      "isbn" => $row["isbn"] ?? '',
      "category" => $row["category"] ?? '',
      "status" => $row["status"] ?? 'available',
      "quantity" => (int)($row["quantity"] ?? 0),
      "publisher" => $row["publisher"] ?? '',
      "publication_year" => $row["year"] ? (int)$row["year"] : null,
      "image_path" => $row["image_path"] ?? null,
      "created_at" => $row["created_at"] ?? null,
    ];
  }

  $conn->close();

  // Output JSON
  echo json_encode(["data" => $books]);
} catch (Exception $e) {
  // Return error as JSON
  http_response_code(500);
  echo json_encode([
    "status" => "error",
    "message" => $e->getMessage(),
    "data" => []
  ]);
}

ob_end_flush();
exit;
