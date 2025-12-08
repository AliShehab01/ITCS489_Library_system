<?php
session_start();
require_once __DIR__ . '/../../config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Library – Catalog</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Main theme -->
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css" />
  <?php if (!defined('STYLE_LOADED')) { define('STYLE_LOADED', true); } ?>
</head>

<body>

  <?php include __DIR__ . '/navbar.php'; ?>

  <main class="site-content">

    <!-- Header + Description -->
    <section class="py-4 bg-white border-bottom">
      <div class="container">
        <h1 class="h4 mb-1">Catalog Search &amp; Browsing</h1>
        <p class="text-muted mb-3">
          Search the library collection by title, author, ISBN, or category. Filter by availability and sort by
          publication year or date added.
        </p>

        <!-- Main search bar -->
        <div class="card shadow-custom">
          <div class="card-body">

  <!-- Search bar منفصل في الأعلى -->
  <div class="mb-3">
    <label for="search" class="form-label small mb-1">
      Search
    </label>
    <input id="search"
           type="text"
           class="form-control"
           placeholder="Search by title, author, or ISBN…" />
  </div>

  <!-- الفلاتر تحت البحث -->
  <div class="row g-3">
    <div class="col-12 col-md-4">
      
      <select class="form-select" id="sort">
        <option value="">Sort by</option>
        <option value="title-asc">Title (A → Z)</option>
        <option value="title-desc">Title (Z → A)</option>
        <option value="pub-asc">Oldest publication</option>
        <option value="pub-desc">Newest publication</option>
        <option value="added-desc">Recently added</option>
        <option value="added-asc">Oldest added</option>
      </select>
    </div>

    <div class="col-12 col-md-4">
     
      <select class="form-select" id="availability">
        <option value="">Availability</option>
        <option value="available">Available</option>
        <option value="unavailable">Unavailable</option>
        <option value="issued">Issued</option>
        <option value="reserved">Reserved</option>
      </select>
    </div>

    <div class="col-12 col-md-4">
      
      <select class="form-select" id="category">
        <option value="">Category</option>
        <option value="Science">Science</option>
        <option value="Engineering">Engineering</option>
        <option value="History">History</option>
        <option value="Literature">Literature</option>
        <option value="Business">Business</option>
        <option value="Other">Other</option>
      </select>
    </div>
  </div>

</div>

    </section>

    <!-- Search results / Books grid -->
    <section class="py-4">
      <div class="container">
        <div id="results" class="row g-3">
          <!-- CatalogSearch.js will populate this area with cards + pagination -->
        </div>
      </div>
    </section>

  </main>

  <!-- FOOTER -->
  <footer class="bg-dark text-white text-center py-3 mt-4">
    <div class="container">
      <small>© 2025 Library System. All rights reserved.</small>
    </div>
  </footer>

  <script>
    // Pass the API URL to CatalogSearch.js using BASE_URL for consistency
    window.CATALOG_API_URL = "<?= BASE_URL ?>app/controller/CatalogSearch_API.php";
  </script>
  <script src="<?= BASE_URL ?>public/js/CatalogSearch.js?v=<?= time(); ?>"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
