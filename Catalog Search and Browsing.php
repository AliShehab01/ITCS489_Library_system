<?php 
header("Content-Type: application/json");

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = '489'; 

$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_errno) {
  http_response_code(500);
  die(json_encode(["status"=>"error","message"=>"DB connect failed: ".$conn->connect_error]));
}

$conn->set_charset('utf8mb4');

// Serve GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $q = "SELECT id, image_path, title, author, isbn, category, publisher, year, created_at FROM books";
  $r = $conn->query($q);

  if (!$r) {
    http_response_code(500);
    echo json_encode(["status"=>"error","message"=>"Query failed: ".$conn->error]);
    exit;
  }

  $out = [];
  while ($row = $r->fetch_assoc()) {
    $out[] = [
      "id"         => (int)$row["id"],
      "image_path" => $row["image_path"],
      "title"      => $row["title"],
      "author"     => $row["author"],
      "isbn"       => $row["isbn"],
      "category"   => $row["category"],
      "publisher"  => $row["publisher"],
      "year"       => is_null($row["year"]) ? null : (int)$row["year"],
      "created_at" => $row["created_at"]
    ];
  }
  echo json_encode($out);
  exit;
}

http_response_code(405);
echo json_encode(["status"=>"error","message"=>"Method not allowed"]);
