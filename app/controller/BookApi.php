<?php

header("Content-Type: application/json");

$data = json_decode(file_get_contents('php://input'), true); // Parse JSON input

require_once __DIR__ . '/../models/dbconnect.php';
$db = new Database();
$conn = $db->conn; // $conn is your PDO object


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['title'], $_POST['author'], $_POST['isbn'])) {
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    try {
        // 1️⃣ Handle image upload
        $imagePath = 'placeholder.jpg';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = "uploads/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $filename = time() . "_" . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = $targetPath;
            }
        }

        // 2️⃣ Prepare insert with bindParam
        $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, category, publisher, year,quantity, image_path)
                                VALUES (:title, :author, :isbn, :category, :publisher, :year, :quantity, :image_path)");

        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':author', $author, PDO::PARAM_STR);
        $stmt->bindParam(':isbn', $isbn, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':publisher', $publisher, PDO::PARAM_STR);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':image_path', $imagePath, PDO::PARAM_STR);

        // Assign values
        $title = $_POST['title'];
        $author = $_POST['author'];
        $isbn = $_POST['isbn'];
        $category = $_POST['category'] ?? null;
        $publisher = $_POST['publisher'] ?? null;
        $year = $_POST['year'] ?? null;
        $quantity = $_POST['quantity'] ?? null;

        $stmt->execute();

        echo json_encode(["success" => true, "message" => "Book added successfully"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    if (!isset($data['title'], $data['author'], $data['isbn'])) {
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE books SET title=:title, author=:author, 
                                category=:category, publisher=:publisher, year=:year, quantity=:quantity
                                WHERE isbn=:isbn");

        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':author', $author, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':publisher', $publisher, PDO::PARAM_STR);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->bindParam(':isbn', $isbn, PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_STR);

        // Assign values
        $title = $data['title'];
        $author = $data['author'];
        $category = $data['category'] ?? null;
        $publisher = $data['publisher'] ?? null;
        $year = $data['year'] ?? null;
        $isbn = $data['isbn'];
        $quantity = $data['quantity'];

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
