<?php
session_start();

include '../view/navbar.php';
require '../models/dbconnect.php';
require '../models/CreateDefaultDBTables.php';

$userid = $_SESSION['user_id'] ?? null;

if (!$userid) {
    echo "User not logged in.";
    exit;
}

// Prepare the PDO statement
$stmt = $conn->prepare('SELECT * FROM borrows WHERE user_id = :userid');
$stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
$stmt->execute();

$borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Dashboard</title>
    <style>
    .borrowsInfo {
        margin-bottom: 20px;
    }

    table {
        border-collapse: collapse;
        width: 50%;
    }

    th,
    td {
        border: 1px solid black;
        padding: 8px;
        text-align: left;
    }
    </style>
</head>

<body>

    <?php
foreach ($borrows as $row) {
    $currentDate = strtotime(date("Y-m-d"));
    $dueDate = strtotime($row["dueDate"]);
    $datediff = round(($currentDate - $dueDate) / (60 * 60 * 24));
    
    echo "<div class='borrowsInfo'>";
    echo '<form action="bookReturnAndRenew.php" method="post">';
    echo '<input type="hidden" name="borrow_id" value="' . htmlspecialchars($row['borrow_id']) . '">';
    echo '<input type="hidden" name="book_id" value="' . htmlspecialchars($row['bookId']) . '">';
    
    echo "<table>";
    echo '<tr><th>Borrow ID: ' . htmlspecialchars($row['borrow_id']) . '</th></tr>';
    echo '<tr><td>Book ID: ' . htmlspecialchars($row['bookId']) . '</td></tr>';
    echo '<tr><td>Number of copies: ' . htmlspecialchars($row['quantity']) . '</td></tr>';
    echo '<tr><td>Price: ' . htmlspecialchars($row['price']) . " BD</td></tr>";

    $fines = 0;
    if ($datediff > 0) {
        $fines = $datediff * $row["quantity"];
        echo '<tr><td>Fines: ' . $fines . ' BD (1 BD per day)</td></tr>';
    }

    $totalPrice = $row['price'] + $fines;
    echo '<tr><td>Total price: ' . $totalPrice . " BD</td></tr>";

    if ($row["isReturned"] === "false") {
        echo '<tr><td>Due date: ' . htmlspecialchars($row['dueDate']) . '</td></tr>';
        echo '<tr><td><input type="date" name="newDueDate"></td></tr>';
        echo '<tr><td>
                <input type="submit" name="RenewReturnAction" value="Renew">
                <input type="submit" name="RenewReturnAction" value="Return book">
              </td></tr>';
        echo '<tr><td style="color: gray"><b>Borrow status: Issued</b></td></tr>';
    } else {
        echo '<tr><td style="color: green"><b>Borrow status: Returned</b></td></tr>';
    }

    echo "</table>";
    echo "</form>";
    echo "</div>";
}
?>

</body>

</html>