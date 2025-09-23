let books = [];
let currentPage = 1;
const perPage = 9;

const searchInput = document.getElementById("search");
const sortSelect = document.getElementById("sort");
const availSel = document.getElementById("availability");
const catSel = document.getElementById("category");
const resultsEl = document.getElementById("results");

// API URL (relative)
const API_URL = "./Catalog Search and Browsing.php";

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
    const res = await fetch(API_URL, { method: "GET", cache: "no-store" });

    if (!res.ok) {
      // include response text for debugging
      const txt = await res.text().catch(() => "");
      throw new Error(`API responded ${res.status} ${res.statusText} - ${txt}`);
    }

    const response = await res.json();
    books = Array.isArray(response) ? response : response.data || [];

    render();
  } catch (err) {
    console.error("Failed to fetch books:", err);

    resultsEl.innerHTML = `
      <div class="col-12">
        <div class="bookCard">
          <strong>Failed to load catalog.</strong>
          <div class="bookMeta">${escapeHtml(String(err.message || err))}</div>
        </div>
      </div>`;
  }
}

// Filters
function applyFilters(list) {
  let out = list;

  // Search (title/author/ISBN)
  const q = (searchInput?.value || "").trim().toLowerCase();
  if (q) {
    out = out.filter(
      (b) =>
        (b.title || "").toLowerCase().includes(q) ||
        (b.author || "").toLowerCase().includes(q) ||
        (b.isbn || "").toLowerCase().includes(q)
    );
  }

  // Availability filter
  if (availSel && availSel.value) {
    out = out.filter((b) => (b.availability || "") === availSel.value);
  }

  // Category filter
  if (catSel && catSel.value) {
    out = out.filter((b) => (b.category || "") === catSel.value);
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
    html += `<button class="btn btn-sm ${
      i === currentPage ? "btn-primary" : "btn-outline-primary"
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
  const badge = makeBadge(b.availability);
  const metaLines = [
    `${i18n ? i18n.author : "Author"}: ${author}`,
    `${i18n ? i18n.category : "Category"}: ${cat}`,
    `${i18n ? i18n.isbn : "ISBN"}: ${isbn}`,
    dateLine(b),
  ].join("<br/>");

  return `
    <div class="col-12 col-sm-6 col-lg-4">
      <div class="bookCard">
        <div class="bookHead">
          <strong class="bookTitle">${title}</strong>
          ${badge}
        </div>
        <div class="bookMeta">${metaLines}</div>
      </div>
    </div>
  `;
}

// Helpers
function makeBadge(av) {
  const cls =
    av === "available"
      ? "success"
      : av === "issued"
      ? "secondary"
      : av === "reserved"
      ? "warning"
      : "light";
  let label = "—";
  if (av) {
    if (pageLang.startsWith("ar")) {
      label =
        av === "available"
          ? "متاح"
          : av === "issued"
          ? "مستعار"
          : av === "reserved"
          ? "محجوز"
          : av;
    } else {
      label = av[0].toUpperCase() + av.slice(1);
    }
  }
  return `<span class="badge bg-${cls}">${label}</span>`;
}

function dateLine(b) {
  const pubLabel = i18n ? i18n.publication : "Publication";
  const addedLabel = i18n ? i18n.added : "Added";
  if (
    typeof b.publication_year === "number" ||
    (b.publication_year && !Number.isNaN(Number(b.publication_year)))
  ) {
    return `${pubLabel}: ${b.publication_year}`;
  }
  if (b.created_at) {
    const d = new Date(b.created_at);
    return `${addedLabel}: ${isNaN(d) ? b.created_at : d.toLocaleDateString()}`;
  }
  return `${pubLabel}: —`;
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
    t = setTimeout(() => {
      currentPage = 1;
      render();
    }, 150);
  });
}
if (sortSelect)
  sortSelect.addEventListener("change", () => {
    currentPage = 1;
    render();
  });
if (availSel)
  availSel.addEventListener("change", () => {
    currentPage = 1;
    render();
  });
if (catSel)
  catSel.addEventListener("change", () => {
    currentPage = 1;
    render();
  });

// Init
fetchBooks();
