<?php

header("Content-Type: application/json");

$data = json_decode(file_get_contents('php://input'), true); // Parse JSON input

require_once "db_connOfAli.php";  //PDO
//get all users
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Expect multipart form for image
    if (!isset($_POST['title'], $_POST['author'], $_POST['isbn'])) {
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    try {
        // 1️⃣ Insert book (without image)
        $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, category, publisher, year) 
                                VALUES (:title, :author, :isbn, :category, :publisher, :year)");
        $stmt->execute([
            ':title' => $_POST['title'],
            ':author' => $_POST['author'],
            ':isbn' => $_POST['isbn'],
            ':category' => $_POST['category'] ?? null,
            ':publisher' => $_POST['publisher'] ?? null,
            ':year' => $_POST['year'] ?? null
        ]);

        $bookId = $conn->lastInsertId();

        // 2️⃣ Handle image upload (if any)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = "uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = time() . "_" . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $filename;
            move_uploaded_file($_FILES['image']['tmp_name'], $targetPath);

            $stmt = $conn->prepare("INSERT INTO bookcover (book_id, image_path) VALUES (:book_id, :image_path)");
            $stmt->execute([
                ':book_id' => $bookId,
                ':image_path' => $targetPath
            ]);
        }

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
