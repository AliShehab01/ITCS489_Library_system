// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function () {

  let books = [];
  let currentPage = 1;
  const perPage = 9;

  const searchInput = document.getElementById("search");
  const sortSelect = document.getElementById("sort");
  const availSel = document.getElementById("availability");
  const catSel = document.getElementById("category");
  const resultsEl = document.getElementById("results");

  // Check if results element exists
  if (!resultsEl) {
    console.error('Results element not found!');
    return;
  }

  // Check if user is logged in (set by PHP)
  const isLoggedIn = typeof window.userLoggedIn !== 'undefined' ? window.userLoggedIn : false;

  // Ensure "Unavailable" option exists in availability filter
  if (availSel) {
    const opts = Array.from(availSel.options || []);
    const hasUnavail = opts.some(
      (o) => String(o.value).toLowerCase() === "unavailable"
    );
    if (!hasUnavail) {
      const opt = document.createElement("option");
      opt.value = "unavailable";
      const lang = (
        document.documentElement.getAttribute("lang") || ""
      ).toLowerCase();
      opt.textContent = lang.startsWith("ar") ? "غير متاح" : "Unavailable";
      const idxAvailable = opts.findIndex(
        (o) => String(o.value).toLowerCase() === "available"
      );
      if (idxAvailable >= 0 && idxAvailable < availSel.options.length) {
        availSel.add(opt, idxAvailable + 1);
      } else {
        availSel.add(opt); // append
      }
    }
  }

  // Determine API URL based on current page location
  function getApiUrl() {
    // Get the base path from the current URL
    const path = window.location.pathname;

    // Try relative path first if we're in the view folder
    if (path.includes('/app/view/')) {
      return './CatalogSearch_Browsingbackend.php';
    }

    // Try different possible paths
    return '/app/view/CatalogSearch_Browsingbackend.php';
  }

  const API_URL = getApiUrl();
  console.log('Using API URL:', API_URL);

  // Detect page language
  const pageLang = (
    document.documentElement.getAttribute("lang") || "en"
  ).toLowerCase();

  // Arabic labels (minimal)
  const i18n = pageLang.startsWith("ar")
    ? {
      author: "المؤلف",
      category: "التصنيف",
      isbn: "ISBN",
      publication: "نشر",
      added: "أضيفت",
      untitled: "بدون عنوان",
      unknown: "غير معروف",
    }
    : null;

  // Category labels (AR)
  const categoryMap = pageLang.startsWith("ar")
    ? {
      Science: "علوم",
      Engineering: "هندسة",
      History: "تاريخ",
      Literature: "أدب",
      Business: "أعمال",
      Other: "أخرى",
    }
    : {};

  // Fetch books
  async function fetchBooks() {
    try {
      console.log('Fetching from:', API_URL);
      const res = await fetch(API_URL, {
        method: "GET",
        cache: "no-store",
        headers: { 'Accept': 'application/json' }
      });

      console.log('Response status:', res.status);

      if (!res.ok) {
        throw new Error(`API responded ${res.status} ${res.statusText}`);
      }

      const text = await res.text();
      console.log('Raw response length:', text.length);

      let response;
      try {
        response = JSON.parse(text);
      } catch (parseErr) {
        console.error('JSON parse error:', parseErr, 'Text:', text.substring(0, 200));
        throw new Error('Invalid JSON response');
      }

      books = Array.isArray(response) ? response : response.data || [];
      console.log('Books loaded:', books.length);

      if (books.length === 0) {
        resultsEl.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info">
                        No books found in the catalog.
                        ${isLoggedIn ? ' <a href="/app/view/bookPage.php">Add books</a>' : ' <a href="/app/view/login.php">Login</a> to add books.'}
                    </div>
                </div>`;
        return;
      }

      render();
    } catch (err) {
      console.error("Failed to fetch books:", err);
      resultsEl.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">
                    <strong>Failed to load catalog.</strong>
                    <div class="small mt-2">${escapeHtml(String(err.message || err))}</div>
                    <div class="mt-2">
                        <button onclick="location.reload()" class="btn btn-sm btn-outline-primary">Reload</button>
                    </div>
                </div>
            </div>`;
    }
  }

  // Filters
  // Filters
  function applyFilters(list) {
    let out = list;

    // Search (title/author/ISBN)
    const q = (searchInput?.value || "").trim().toLowerCase();
    if (q) {
      out = out.filter(
        (b) =>
          String(b.title || "")
            .toLowerCase()
            .includes(q) ||
          String(b.author || "")
            .toLowerCase()
            .includes(q) ||
          String(b.isbn || "")
            .toLowerCase()
            .includes(q)
      );
    }

    // Availability — strict match (supports either `status` or legacy `availability`)
    if (availSel && availSel.value) {
      const wanted = String(availSel.value).trim().toLowerCase(); // available | issued | reserved | unavailable
      out = out.filter((b) => getAvailability(b) === wanted);
    }

    // Category (exact value as in <option value="...">)
    if (catSel && catSel.value) {
      out = out.filter((b) => String(b.category || "") === catSel.value);
    }

    return out;
  }

  // Sorting
  function applySort(list) {
    const idVal = (b) => (typeof b.id === "number" ? b.id : Number(b.id) || 0);

    const v = sortSelect?.value || "";

    // Title comparators
    const byTitleAsc = (a, b) =>
      String(a.title || "").localeCompare(String(b.title || ""), pageLang, {
        sensitivity: "base",
      });
    const byTitleDesc = (a, b) =>
      String(b.title || "").localeCompare(String(a.title || ""), pageLang, {
        sensitivity: "base",
      });

    if (v === "added-asc") return [...list].sort((a, b) => idVal(a) - idVal(b)); // الأقدم أولاً (by id)
    if (v === "added-desc") return [...list].sort((a, b) => idVal(b) - idVal(a)); // الأحدث أولاً (by id)

    // get comparable value for publication date or added date
    const pubVal = (b) => {
      if (typeof b.publication_year === "number") return b.publication_year;
      // fallback: try parse if publication_year as string
      if (b.publication_year) {
        const n = Number(b.publication_year);
        if (!Number.isNaN(n)) return n;
      }
      return -Infinity;
    };

    const addedVal = (b) => {
      if (b.created_at) {
        const t = Date.parse(b.created_at);
        if (!Number.isNaN(t)) return t;
      }
      return -Infinity;
    };

    if (v === "title-asc") return [...list].sort(byTitleAsc);
    if (v === "title-desc") return [...list].sort(byTitleDesc);
    if (v === "pub-asc") return [...list].sort((a, b) => pubVal(a) - pubVal(b));
    if (v === "pub-desc") return [...list].sort((a, b) => pubVal(b) - pubVal(a));
    // (added-asc/desc handled above with created_at fallback to id)

    return list;
  }

  // Pagination
  function paginate(list, page, per) {
    const start = (page - 1) * per;
    return list.slice(start, start + per);
  }

  function renderPager(total) {
    const pageCount = Math.ceil(total / perPage) || 1;
    let html = "";
    for (let i = 1; i <= pageCount; i++) {
      html += `<button class="btn btn-sm ${i === currentPage ? "btn-primary" : "btn-outline-primary"
        } me-1 mb-2" data-page="${i}">${i}</button>`;
    }
    return `<div class="col-12 d-flex flex-wrap align-items-center mt-2">${html}</div>`;
  }

  // Render
  function render() {
    const filtered = applyFilters(books);
    const sorted = applySort(filtered);
    const pageData = paginate(sorted, currentPage, perPage);

    // Book cards
    const cards = pageData.map((b) => bookCard(b)).join("");

    // Pager
    const pager = renderPager(sorted.length);

    resultsEl.innerHTML = cards + pager;

    // Wire pager buttons
    resultsEl.querySelectorAll("button[data-page]").forEach((btn) => {
      btn.addEventListener("click", () => {
        currentPage = parseInt(btn.getAttribute("data-page"), 10);
        render();
      });
    });
  }

  // Card template
  function bookCard(b) {
    const title = escapeHtml(b.title || (i18n ? i18n.untitled : "Untitled"));
    const author = escapeHtml(b.author || (i18n ? i18n.unknown : "Unknown"));
    const catRaw = b.category || "";
    const cat = escapeHtml(
      categoryMap && categoryMap[catRaw] ? categoryMap[catRaw] : catRaw || "—"
    );
    const isbn = escapeHtml(b.isbn || "—");
    const qty = typeof b.quantity === 'number' ? b.quantity : (parseInt(b.quantity) || 0);

    // Book cover image
    const imagePath = b.image_path ? '/' + escapeHtml(b.image_path) : null;
    const coverHtml = imagePath
      ? `<img src="${imagePath}" class="book-cover-img" alt="${title}">`
      : `<div class="book-cover-placeholder">📕</div>`;

    // Build meta lines, filtering out empty ones
    const dateInfo = dateLine(b);
    const metaLines = [
      `${i18n ? i18n.author : "Author"}: ${author}`,
      `${i18n ? i18n.category : "Category"}: ${cat}`,
      `${i18n ? i18n.isbn : "ISBN"}: ${isbn}`,
      `Available: ${qty} copies`,
      dateInfo
    ].filter(line => line && line.trim() !== '').join("<br/>");

    const avail = getAvailability(b);
    const badge = makeBadge(avail);

    // Show borrow form only if logged in and book is available
    if (isLoggedIn && avail === "available" && qty > 0) {
      const maxQty = Math.min(5, qty);
      const defaultDue = new Date();
      defaultDue.setDate(defaultDue.getDate() + 14);
      const dueDateStr = defaultDue.toISOString().split('T')[0];
      const todayStr = new Date().toISOString().split('T')[0];

      return `
  <div class="col-12 col-sm-6 col-lg-4">
    <div class="bookCard" data-book-id="${b.id}">
      <div class="bookCoverWrap">${coverHtml}</div>
      <div class="bookHead">
        <strong class="bookTitle">${title}</strong>
        ${badge}
      </div>
      <div class="bookMeta">${metaLines}</div>
      
      <form action="/app/view/BorrowBook.php?bookid=${b.id}" method="post" class="borrowForm">
        <div class="borrowFormRow">
          <label>Qty:</label>
          <input type="number" name="QuantityWanted" min="1" max="${maxQty}" value="1" class="borrowInput">
        </div>
        <div class="borrowFormRow">
          <label>Due:</label>
          <input type="date" name="dueDate" min="${todayStr}" value="${dueDateStr}" class="borrowInput">
        </div>
        <button type="submit" class="btn btn-primary btn-sm w-100">Borrow</button>
      </form>
    </div>
  </div>
`;
    }

    // Show login prompt if not logged in and book is available
    if (!isLoggedIn && avail === "available" && qty > 0) {
      return `
  <div class="col-12 col-sm-6 col-lg-4">
    <div class="bookCard" data-book-id="${b.id}">
      <div class="bookCoverWrap">${coverHtml}</div>
      <div class="bookHead">
        <strong class="bookTitle">${title}</strong>
        ${badge}
      </div>
      <div class="bookMeta">${metaLines}</div>
      <a href="/app/view/login.php" class="btn btn-outline-primary btn-sm w-100 mt-2">Login to Borrow</a>
    </div>
  </div>
`;
    }

    // Show reserve option if unavailable
    const reserveBtn = isLoggedIn && (avail === "unavailable" || qty === 0)
      ? `<a href="/app/view/reservations.php?book_id=${b.id}" class="btn btn-outline-warning btn-sm w-100 mt-2">Reserve</a>`
      : '';

    return `
  <div class="col-12 col-sm-6 col-lg-4">
    <div class="bookCard" data-book-id="${b.id}">
      <div class="bookCoverWrap">${coverHtml}</div>
      <div class="bookHead">
        <strong class="bookTitle">${title}</strong>
        ${badge}
      </div>
      <div class="bookMeta">${metaLines}</div>
      ${reserveBtn}
    </div>
  </div>
`;
  }

  function makeBadge(av) {
    if (!av) {
      return `<span class="badge bg-light">—</span>`;
    }

    const normalized = String(av).toLowerCase();

    const classes = {
      available: "success",
      issued: "secondary",
      reserved: "warning",
      unavailable: "danger",
    };

    const labelsAr = {
      available: "متاح",
      issued: "مستعار",
      reserved: "محجوز",
      unavailable: "غير متاح",
    };

    const cls = classes[normalized] || "light";

    let label;
    if (pageLang.startsWith("ar")) {
      label = labelsAr[normalized] || av;
    } else {
      label = av.charAt(0).toUpperCase() + av.slice(1);
    }

    return `<span class="badge bg-${cls}">${label}</span>`;
  }

  // Helper: normalize availability/status from backend
  function getAvailability(b) {
    if (!b) return '';
    if (b.status && String(b.status).trim() !== '') return String(b.status).trim().toLowerCase();
    if (typeof b.quantity === 'number') return b.quantity > 0 ? 'available' : 'unavailable';
    if (b.quantity) {
      const n = Number(b.quantity);
      if (!Number.isNaN(n)) return n > 0 ? 'available' : 'unavailable';
    }
    return '';
  }

  function dateLine(b) {
    const pubLabel = i18n ? i18n.publication : "Year";
    const addedLabel = i18n ? i18n.added : "Added";

    // Check publication_year
    if (b.publication_year !== null && b.publication_year !== undefined) {
      const year = Number(b.publication_year);
      if (!Number.isNaN(year) && year > 0) {
        return `${pubLabel}: ${year}`;
      }
    }

    // Fallback to created_at
    if (b.created_at) {
      const d = new Date(b.created_at);
      if (!isNaN(d.getTime())) {
        return `${addedLabel}: ${d.toLocaleDateString()}`;
      }
    }

    // Don't show anything if no date info
    return '';
  }

  function escapeHtml(s) {
    return String(s).replace(
      /[&<>"']/g,
      (m) =>
      ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
      }[m])
    );
  }

  // Events
  if (searchInput) {
    let t;
    searchInput.addEventListener("input", () => {
      clearTimeout(t);
      t = setTimeout(() => { currentPage = 1; render(); }, 150);
    });
  }


  if (availSel) availSel.addEventListener("change", () => { currentPage = 1; render(); }); if (sortSelect) sortSelect.addEventListener("change", () => { currentPage = 1; render(); }); if (catSel) catSel.addEventListener("change", () => { currentPage = 1; render(); });

  // Init - fetch books
  fetchBooks();

}); // End DOMContentLoaded
