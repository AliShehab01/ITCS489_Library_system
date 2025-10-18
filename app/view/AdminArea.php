<?php
session_start();
require 'checkifadmin.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <title>Admin Area</title>
</head>

<body class="bg-light">
<?php include 'navbar.php'; ?>

<div class="container my-5">
  <h1 class="mb-4">Admin Control Panel</h1>

  <div class="row g-4">
    <!-- User Management -->
    <div class="col-md-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h5 class="card-title">User Management</h5>
          <p class="card-text">Add, edit, or remove users, assign roles, manage borrowing limits.</p>
          <a href="Managingusers.php" class="btn btn-primary">Manage Users</a>
        </div>
      </div>
    </div>

    <!-- Book Management -->
    <div class="col-md-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Book Management</h5>
          <p class="card-text">Add, update, remove books, track availability, categorize books via database.</p>
          <a href="bookPage.php" class="btn btn-primary">Manage Books</a>
        </div>
      </div>
    </div>

    <!-- Catalog Search -->
    <div class="col-md-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Catalog Search</h5>
          <p class="card-text">Search and browse books by title, author, category, or filters.</p>
          <a href="Catalog%20Search%20and%20Browsing-EN.html" class="btn btn-primary">Search Catalog</a>
        </div>
      </div>
    </div>

    <!-- Borrowing & Returning -->
    <div class="col-md-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Borrow / Return</h5>
          <p class="card-text">Issue and return books, handle due dates, renewals, and fines.</p>
          <a href="BorrowBook.php" class="btn btn-primary">Manage Borrowing</a>
          <a href="bookReturnAndRenew.php" class="btn btn-outline-secondary mt-2">Return / Renew</a>
        </div>
      </div>
    </div>

    <!-- Reservations -->
    <div class="col-md-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Reservations</h5>
          <p class="card-text">Allow users to reserve books currently on loan. Manage queues and notifications.</p>
          <a href="reservations.php" class="btn btn-primary">Manage Reservations</a>
        </div>
      </div>
    </div>

    <!-- Notifications -->
    <div class="col-md-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Notifications</h5>
          <p class="card-text">View and manage notifications sent to users when books become available.</p>
          <a href="notifications.php" class="btn btn-primary">Manage Notifications</a>
        </div>
      </div>
    </div>

    <!-- Reports -->
    <div class="col-md-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Reports & Tracking</h5>
          <p class="card-text">Generate reports on borrowed, overdue, and reserved books, fines, and statistics.</p>
          <a href="reports.php" class="btn btn-primary">View Reports</a>
        </div>
      </div>
    </div>

    <!-- System Administration -->
    <div class="col-md-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h5 class="card-title">System Administration</h5>
          <p class="card-text">Manage staff access, configure policies, backup data, and monitor system usage.</p>
          <a href="adminSettings.php" class="btn btn-primary">Admin Settings</a>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
