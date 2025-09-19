<?php
header("Content-Type: application/json");

 $host='127.0.0.1';
 $user='root'; 
 $pass='';
 $db='library'; 
  

$conn = @new mysqli($host,$user,$pass,$db);

if($conn->connect_errno){
  http_response_code(500);
  die(json_encode(["status"=>"error","message"=>"DB connect failed: ".$conn->connect_error]));
}

$conn->set_charset('utf8mb4');

// GET ONLY
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $q = "SELECT id,title,author,category,availability,isbn,publication_year,created_at FROM books";
  $r = $conn->query($q);
  if (!$r) { http_response_code(500); echo json_encode(["status"=>"error","message"=>"Query failed: ".$conn->error]); exit; }

  $out = [];
  while ($row = $r->fetch_assoc()) {
    $out[] = [
      "id" => (int)$row["id"],
      "title" => $row["title"],
      "author" => $row["author"],
      "category" => $row["category"],
      "availability" => $row["availability"],
      "isbn" => $row["isbn"],
      "publication_year" => is_null($row["publication_year"]) ? null : (int)$row["publication_year"],
      "created_at" => $row["created_at"]
    ];
  }
  echo json_encode($out);
  exit;
}

http_response_code(405);
echo json_encode(["status"=>"error","message"=>"Method not allowed"]);
