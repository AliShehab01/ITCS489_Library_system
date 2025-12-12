<?php
session_start();
require_once __DIR__ . '/../../config.php';

$isLoggedIn = isset($_SESSION['username']);
$userRole = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Library – Catalog</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      padding-top: 80px;
      background: #f8f9fa;
    }

    .searchBar {
      padding: 20px 0;
      background: #fff;
      border-bottom: 1px solid #dee2e6;
    }

    .filtersArea {
      padding: 15px 0;
      background: #fff;
      border-bottom: 1px solid #dee2e6;
    }

    .bookCard {
      background: #fff;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 15px;
      height: 100%;
    }

    .bookCard:hover {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .bookCoverWrap {
      text-align: center;
      margin-bottom: 12px;
    }

    .book-cover-img {
      width: 100%;
      max-width: 120px;
      height: 160px;
      object-fit: cover;
      border-radius: 4px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .book-cover-placeholder {
      width: 120px;
      height: 160px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 4px;
      color: white;
      font-size: 3rem;
      margin: 0 auto;
    }

    .bookHead {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 10px;
      gap: 10px;
    }

    .bookTitle {
      font-size: 1rem;
      color: #212529;
      font-weight: 600;
    }

    .bookMeta {
      font-size: 0.8rem;
      color: #6c757d;
      margin-bottom: 10px;
      line-height: 1.4;
    }

    .borrowForm {
      border-top: 1px solid #eee;
      padding-top: 10px;
      margin-top: 10px;
    }

    .borrowFormRow {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 8px;
    }

    .borrowFormRow label {
      min-width: 35px;
      font-size: 0.85rem;
      color: #495057;
    }

    .borrowInput {
      flex: 1;
      padding: 6px 10px;
      border: 1px solid #ced4da;
      border-radius: 4px;
      font-size: 0.9rem;
    }

    .navbar .nav-link {
      color: rgba(255, 255, 255, 0.85) !important;
    }

    .navbar .nav-link:hover {
      color: #3b82f6 !important;
    }

    .dropdown-menu-dark {
      background-color: #1f2937;
    }

    .dropdown-menu-dark .dropdown-item {
      color: #f3f4f6;
    }

    .dropdown-menu-dark .dropdown-item:hover {
      background-color: #374151;
      color: #3b82f6;
    }
  </style>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>

  <!-- Search bar -->
  <section class="searchBar">
    <div class="container">
      <input type="text" id="search" class="form-control form-control-lg" placeholder="Search by title, author, ISBN…" />
    </div>
  </section>

  <!-- Filters & sort -->
  <section class="filtersArea">
    <div class="container">
      <div class="d-flex flex-wrap align-items-center gap-3 justify-content-between">
        <h2 class="h4 m-0">Catalog</h2>
        <div class="d-flex flex-wrap gap-2">
          <select class="form-select form-select-sm" id="sort" style="width: auto;">
            <option value="">Sort by</option>
            <option value="title-asc">Title A-Z</option>
            <option value="title-desc">Title Z-A</option>
            <option value="added-desc">Recently Added</option>
          </select>
          <select class="form-select form-select-sm" id="availability" style="width: auto;">
            <option value="">All Status</option>
            <option value="available">Available</option>
            <option value="unavailable">Unavailable</option>
          </select>
          <select class="form-select form-select-sm" id="category" style="width: auto;">
            <option value="">All Categories</option>
            <option value="Science">Science</option>
            <option value="Engineering">Engineering</option>
            <option value="History">History</option>
            <option value="Literature">Literature</option>
            <option value="Business">Business</option>
          </select>
        </div>
      </div>
    </div>
  </section>

  <?php if (!$isLoggedIn): ?>
    <div class="container mt-3">
      <div class="alert alert-info mb-0">
        <a href="<?= BASE_URL ?>view/login.php">Login</a> or <a href="<?= BASE_URL ?>view/signup.php">Sign up</a> to borrow books.
      </div>
    </div>
  <?php endif; ?>

  <!-- Results -->
  <main class="py-4">
    <div class="container">
      <div id="results" class="row g-3">
        <div class="col-12 text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 text-muted">Loading catalog...</p>
        </div>
      </div>
    </div>
  </main>

  <footer class="bg-dark text-white text-center py-3 mt-auto">
    <small>© 2025 Library System</small>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    window.userLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
    window.userRole = '<?= htmlspecialchars($userRole) ?>';
  </script>
  <script src="<?= PUBLIC_URL ?>js/CatalogSearch.js"></script>
</body>

</html>
