<?php
session_start();
require_once __DIR__ . '/../../config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Library — Catalog</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css" />
  <?php if (!defined('STYLE_LOADED')) { define('STYLE_LOADED', true); } ?>
</head>

<body>

  <?php include __DIR__ . '/navbar.php'; ?>

  <main class="page-shell">
    <!-- Search bar -->
    <section class="searchBar">
      <div class="container">
        <input type="text" id="search" class="form-control" placeholder="Search by title, author, ISBN…" />
      </div>
    </section>

    <!-- Filters & sort -->
    <section class="filtersArea">
      <div class="container">
        <div class="d-flex flex-wrap align-items-center gap-3 justify-content-between">
          <h2 class="m-0 flex-grow-1">Catalog Search and Browsing</h2>

          <!-- Sort-->
          <div class="sortWrap">
            <label class="form-label mb-1">Sort</label>
            <select class="form-select" id="sort">
              <option value="">Sort by</option>
              <option value="title-asc">Title Ascending</option>
              <option value="title-desc">Title descending</option>
              <option value="pub-asc">Oldest Publication</option>
              <option value="pub-desc">Newest Publication</option>
              <option value="added-desc">Recently Added</option>
              <option value="added-asc">Oldest Added</option>
            </select>
          </div>

          <!-- Filter -->
          <div class="filtersWrap d-flex flex-wrap gap-2">
            <div class="filterBlock">
              <label class="form-label mb-1">Availability</label>
              <select class="form-select" id="availability">
                <option value="">Any</option>
                <option value="available">Available</option>
                <option value="unavailable">Unavailable</option>
                <option value="issued">Issued</option>
                <option value="reserved">Reserved</option>
              </select>
            </div>
            <div class="filterBlock">
              <label class="form-label mb-1">Category</label>
              <select class="form-select" id="category">
                <option value="">All</option>
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
      </div>
    </section>

    <!-- Results / catalog grid -->
    <section class="catalog">
      <div class="container">
        <div id="results" class="row g-3">
          <!-- Example book card -->
          <div class="col-12 col-sm-6 col-lg-4">
            <div class="bookCard">
              <div class="bookHead">
                <strong class="bookTitle">Introduction to Algorithms</strong>
                <span class="badge bg-success">Available</span>
              </div>
              <div class="bookMeta">
                Author: Cormen et al.<br />
                Category: Engineering / CS<br />
                ISBN: 978-0262046305
              </div>
              <!-- bookActions removed per request -->
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="app-footer text-center">
    <small>© 2025 Library System. All rights reserved.</small>
  </footer>

  <script src="<?= BASE_URL ?>public/js/CatalogSearch.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
