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
      font-size: 0.85rem;
      color: #6c757d;
      margin-bottom: 10px;
      line-height: 1.5;
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

  <!-- Inline catalog script to avoid path issues -->
  <script>
    window.userLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
    window.userRole = '<?= htmlspecialchars($userRole) ?>';

    document.addEventListener('DOMContentLoaded', function() {
      const resultsEl = document.getElementById("results");
      const searchInput = document.getElementById("search");
      const sortSelect = document.getElementById("sort");
      const availSel = document.getElementById("availability");
      const catSel = document.getElementById("category");

      let books = [];
      let currentPage = 1;
      const perPage = 9;
      const isLoggedIn = window.userLoggedIn;

      // API URL - relative to current page
      const API_URL = './CatalogSearch_Browsingbackend.php';

      async function fetchBooks() {
        console.log('Fetching from:', API_URL);
        try {
          const res = await fetch(API_URL);
          console.log('Response status:', res.status);

          if (!res.ok) throw new Error('HTTP ' + res.status);

          const text = await res.text();
          console.log('Response:', text.substring(0, 300));

          const json = JSON.parse(text);
          books = Array.isArray(json) ? json : (json.data || []);
          console.log('Books loaded:', books.length);

          if (books.length === 0) {
            resultsEl.innerHTML = '<div class="col-12"><div class="alert alert-info">No books in catalog. ' +
              (isLoggedIn ? '<a href="bookPage.php">Add books</a>' : '<a href="login.php">Login</a>') + '</div></div>';
            return;
          }
          render();
        } catch (err) {
          console.error('Fetch error:', err);
          resultsEl.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error: ' + err.message +
            '<br><button onclick="location.reload()" class="btn btn-sm btn-primary mt-2">Retry</button></div></div>';
        }
      }

      function render() {
        let filtered = books;
        const q = (searchInput?.value || '').toLowerCase().trim();
        if (q) {
          filtered = filtered.filter(b =>
            (b.title || '').toLowerCase().includes(q) ||
            (b.author || '').toLowerCase().includes(q) ||
            (b.isbn || '').toLowerCase().includes(q)
          );
        }
        if (availSel?.value) {
          const wanted = availSel.value.toLowerCase();
          filtered = filtered.filter(b => {
            const status = (b.status || '').toLowerCase();
            const qty = parseInt(b.quantity) || 0;
            if (wanted === 'available') return status === 'available' || qty > 0;
            if (wanted === 'unavailable') return status === 'unavailable' || qty === 0;
            return true;
          });
        }
        if (catSel?.value) {
          filtered = filtered.filter(b => b.category === catSel.value);
        }

        // Sort
        const sortVal = sortSelect?.value || '';
        if (sortVal === 'title-asc') filtered.sort((a, b) => (a.title || '').localeCompare(b.title || ''));
        if (sortVal === 'title-desc') filtered.sort((a, b) => (b.title || '').localeCompare(a.title || ''));
        if (sortVal === 'added-desc') filtered.sort((a, b) => (b.id || 0) - (a.id || 0));

        // Paginate
        const start = (currentPage - 1) * perPage;
        const pageData = filtered.slice(start, start + perPage);

        // Render cards
        let html = pageData.map(b => {
          const qty = parseInt(b.quantity) || 0;
          const avail = qty > 0 ? 'available' : 'unavailable';
          const badge = avail === 'available' ?
            '<span class="badge bg-success">Available</span>' :
            '<span class="badge bg-danger">Unavailable</span>';

          let actionHtml = '';
          if (isLoggedIn && avail === 'available') {
            const today = new Date().toISOString().split('T')[0];
            const due = new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            actionHtml = `
              <form action="BorrowBook.php?bookid=${b.id}" method="post" class="borrowForm">
                <div class="borrowFormRow"><label>Qty:</label><input type="number" name="QuantityWanted" min="1" max="${Math.min(5,qty)}" value="1" class="borrowInput"></div>
                <div class="borrowFormRow"><label>Due:</label><input type="date" name="dueDate" min="${today}" value="${due}" class="borrowInput"></div>
                <button type="submit" class="btn btn-primary btn-sm w-100">Borrow</button>
              </form>`;
          } else if (!isLoggedIn && avail === 'available') {
            actionHtml = '<a href="login.php" class="btn btn-outline-primary btn-sm w-100 mt-2">Login to Borrow</a>';
          } else if (isLoggedIn && avail === 'unavailable') {
            actionHtml = `<a href="reservations.php?book_id=${b.id}" class="btn btn-outline-warning btn-sm w-100 mt-2">Reserve</a>`;
          }

          return `
            <div class="col-12 col-sm-6 col-lg-4">
              <div class="bookCard">
                <div class="bookHead"><strong class="bookTitle">${escapeHtml(b.title||'Untitled')}</strong>${badge}</div>
                <div class="bookMeta">
                  Author: ${escapeHtml(b.author||'Unknown')}<br>
                  Category: ${escapeHtml(b.category||'—')}<br>
                  ISBN: ${escapeHtml(b.isbn||'—')}<br>
                  Available: ${qty} copies
                </div>
                ${actionHtml}
              </div>
            </div>`;
        }).join('');

        // Pager
        const pageCount = Math.ceil(filtered.length / perPage) || 1;
        let pagerHtml = '<div class="col-12 mt-3">';
        for (let i = 1; i <= pageCount; i++) {
          pagerHtml += `<button class="btn btn-sm ${i===currentPage?'btn-primary':'btn-outline-primary'} me-1 mb-1" data-page="${i}">${i}</button>`;
        }
        pagerHtml += '</div>';

        resultsEl.innerHTML = html + pagerHtml;

        // Wire pager
        resultsEl.querySelectorAll('[data-page]').forEach(btn => {
          btn.onclick = () => {
            currentPage = parseInt(btn.dataset.page);
            render();
          };
        });
      }

      function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, m => ({
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#39;'
        } [m]));
      }

      // Events
      searchInput?.addEventListener('input', () => {
        currentPage = 1;
        render();
      });
      sortSelect?.addEventListener('change', () => {
        currentPage = 1;
        render();
      });
      availSel?.addEventListener('change', () => {
        currentPage = 1;
        render();
      });
      catSel?.addEventListener('change', () => {
        currentPage = 1;
        render();
      });

      // Start
      fetchBooks();
    });
  </script>
</body>

</html>