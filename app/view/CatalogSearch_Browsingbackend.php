<?php
// Make API robust: suppress HTML error output and capture any stray output so JSON stays valid
ob_start();
ini_set('display_errors', '0');
error_reporting(0);
mysqli_report(MYSQLI_REPORT_OFF);

header('Content-Type: application/json; charset=utf-8');

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = 'library_system';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_errno) {
  // clear any buffered output
  ob_end_clean();
  http_response_code(500);
  echo json_encode(["status" => "error", "message" => "DB connect failed: " . $conn->connect_error]);
  exit;
}

$conn->set_charset('utf8mb4');

// Serve GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  // First check if publication_year column exists
  $checkColumn = $conn->query("SHOW COLUMNS FROM books LIKE 'publication_year'");
  $hasPublicationYear = $checkColumn && $checkColumn->num_rows > 0;

  // Build query based on available columns
  $columns = "id, image_path, title, author, isbn, category, status, quantity, publisher";
  if ($hasPublicationYear) {
    $columns .= ", publication_year";
  } else {
    $columns .= ", `year`";  // fallback to year column
  }
  $columns .= ", created_at";
  
  $q = "SELECT $columns FROM books";
  $r = $conn->query($q);

  if (!$r) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Query failed: " . $conn->error]);
    exit;
  }

  $out = [];
  while ($row = $r->fetch_assoc()) {
    // support both publication_year and year column names if present
    $pubYear = null;
    if (array_key_exists('publication_year', $row) && $row['publication_year'] !== null) {
      $pubYear = is_numeric($row['publication_year']) ? (int)$row['publication_year'] : null;
    } elseif (array_key_exists('year', $row) && $row['year'] !== null) {
      $pubYear = is_numeric($row['year']) ? (int)$row['year'] : null;
    }

    // Determine availability for backward compatibility
    $availabilityVal = null;
    if (array_key_exists('availability', $row) && $row['availability'] !== null && $row['availability'] !== '') {
      $availabilityVal = $row['availability'];
    } elseif (array_key_exists('status', $row) && $row['status'] !== null && $row['status'] !== '') {
      $availabilityVal = $row['status'];
    } elseif (array_key_exists('quantity', $row)) {
      $availabilityVal = ((int)$row['quantity'] > 0) ? 'available' : 'unavailable';
    } else {
      $availabilityVal = null;
    }

    $out[] = [
      "id"               => (int)$row["id"],
      "image_path"       => $row["image_path"],
      "title"            => $row["title"],
      "author"           => $row["author"],
      "isbn"             => $row["isbn"],
      "category"         => $row["category"],
      // include both 'status' (new) and 'availability' (legacy)
      "status"           => $row["status"],
      "availability"     => $availabilityVal,
      "quantity"         => array_key_exists('quantity', $row) ? (int)$row['quantity'] : null,
      "publisher"        => $row["publisher"],
      "publication_year" => $pubYear,
      "created_at"       => $row["created_at"],
    ];
  }

  // If any stray output (HTML/errors) was produced, include it in a debug field but still return JSON
  $extra = trim((string)ob_get_clean());
  $resp = ["data" => $out];
  if ($extra !== '') {
    $resp["debug_output"] = $extra;
  }

  echo json_encode($resp);
  exit;
}

ob_end_clean();
http_response_code(405);
echo json_encode(["status" => "error", "message" => "Method not allowed"]);
