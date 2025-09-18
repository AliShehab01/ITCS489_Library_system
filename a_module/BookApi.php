<?php

header("Content-Type: application/json");

$data = json_decode(file_get_contents('php://input'), true); // Parse JSON input

require_once "db_connOfAli.php";  //PDO
//get all users
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($data['title'], $data['author'], $data['isbn'])) {
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }
    try {
        $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, category, publisher, year) 
                               VALUES (:title, :author, :isbn, :category, :publisher, :year)");
        $title = $data['title'];
        $author = $data['author'];
        $isbn = $data['isbn'];
        $category = $data['category'];
        $publisher = $data['publisher'];
        $year = $data['year'];

        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":author", $author);
        $stmt->bindParam(":isbn", $isbn);
        $stmt->bindParam(":category", $category);
        $stmt->bindParam(":publisher", $publisher);
        $stmt->bindParam(":year", $year);
        $stmt->execute();

        echo json_encode(["success" => true, "message" => "Book added successfully"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}






if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    // UPDATE existing book by ISBN
    if (!isset($data['title'], $data['author'], $data['isbn'])) {
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }
    try {
        $stmt = $conn->prepare("UPDATE books set title=:title, author=:author,
        category = :category, publisher = :publisher, year = :year
                                WHERE isbn = :isbn");
        $title = $data['title'];
        $author = $data['author'];
        $isbn = $data['isbn'];
        $category = $data['category'];
        $publisher = $data['publisher'];
        $year = $data['year'];

        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":author", $author);
        $stmt->bindParam(":isbn", $isbn);
        $stmt->bindParam(":category", $category);
        $stmt->bindParam(":publisher", $publisher);
        $stmt->bindParam(":year", $year);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => true, "message" => "Book updated successfully"]);
        } else {
            echo json_encode(["error" => "No book found with this ISBN or no changes made"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}





//$users = $stmt->fetchAll(PDO::FETCH_ASSOC); // fetch all rows as an associative array