let books = [];
let currentPage = 1;
const perPage = 9;


const searchInput = document.getElementById('search');
const sortSelect  = document.getElementById('sort');
const availSel    = document.getElementById('availability');
const catSel      = document.getElementById('category');
const resultsEl   = document.getElementById('results');

// API
const API_URL = 'http://localhost/Catalog Search and Browsing/Catalog Search and Browsing.php'; 

//Fetch

async function fetchBooks() {

  try {
    const res = await fetch(API_URL, { method: 'GET' });
    const response = await res.json();

    books = Array.isArray(response) ? response : (response.data || []);

    render();
 } 
  
  catch (err) {
    console.error('Failed to fetch books:', err);
    
    resultsEl.innerHTML = `<div class="col-12"><div class="bookCard">Failed to load catalog.</div></div>`;
  }
}

// Filter
function applyFilters(list) {
  let out = list;

  // search (عنوان/مؤلف/ISBN)
  const q = (searchInput?.value || '').trim().toLowerCase();
  if (q) {
    out = out.filter(b =>
      (b.title || '').toLowerCase().includes(q) ||
      (b.author || '').toLowerCase().includes(q) ||
      (b.isbn || '').toLowerCase().includes(q)
    );
  }

  // availability
  if (availSel && availSel.value) {
    out = out.filter(b => (b.availability || '') === availSel.value);
  }

  // category
  if (catSel && catSel.value) {
    out = out.filter(b => (b.category || '') === catSel.value);
  }

  return out;
}

// Sort 
function applySort(list) {
  const v = sortSelect?.value || '';

  const byTitleAsc  = (a, b) => (a.title || '').localeCompare(b.title || '');
  const byTitleDesc = (a, b) => (b.title || '').localeCompare(a.title || '');

  // get comparable value for publication date or added date
  const pubVal = (b) => {
    if (typeof b.publication_year === 'number') return b.publication_year;
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

  if (v === 'title-asc')  return [...list].sort(byTitleAsc);
  if (v === 'title-desc') return [...list].sort(byTitleDesc);
  if (v === 'pub-asc')    return [...list].sort((a,b) => pubVal(a) - pubVal(b));
  if (v === 'pub-desc')   return [...list].sort((a,b) => pubVal(b) - pubVal(a));
  if (v === 'added-asc')  return [...list].sort((a,b) => addedVal(a) - addedVal(b));
  if (v === 'added-desc') return [...list].sort((a,b) => addedVal(b) - addedVal(a));

  return list;
}

// ===== Pagination =====
function paginate(list, page, per) {
  const start = (page - 1) * per;
  return list.slice(start, start + per);
}

function renderPager(total) {
  const pageCount = Math.ceil(total / perPage) || 1;
  let html = '';
  for (let i = 1; i <= pageCount; i++) {
    html += `<button class="btn btn-sm ${i===currentPage?'btn-primary':'btn-outline-primary'} me-1 mb-2" data-page="${i}">${i}</button>`;
  }
  return `<div class="col-12 d-flex flex-wrap align-items-center mt-2">${html}</div>`;
}

// ===== Render =====
function render() {
  const filtered = applyFilters(books);
  const sorted   = applySort(filtered);
  const pageData = paginate(sorted, currentPage, perPage);

  // بطاقات الكتب
  const cards = pageData.map(b => bookCard(b)).join('');

  // ازرار الصفحات
  const pager = renderPager(sorted.length);

  resultsEl.innerHTML = cards + pager;

  // ربط أزرار الصفحات
  resultsEl.querySelectorAll('button[data-page]').forEach(btn => {
    btn.addEventListener('click', () => {
      currentPage = parseInt(btn.getAttribute('data-page'), 10);
      render();
    });
  });
}

// ===== Card Template =====
function bookCard(b) {
  const title  = escapeHtml(b.title || 'Untitled');
  const author = escapeHtml(b.author || 'Unknown');
  const cat    = escapeHtml(b.category || '—');
  const isbn   = escapeHtml(b.isbn || '—');
  const badge  = makeBadge(b.availability);
  const metaLines = [
    `Author: ${author}`,
    `Category: ${cat}`,
    `ISBN: ${isbn}`,
    dateLine(b)
  ].join('<br/>');

  return `
    <div class="col-12 col-sm-6 col-lg-4">
      <div class="bookCard">
        <div class="bookHead">
          <strong class="bookTitle">${title}</strong>
          ${badge}
        </div>
        <div class="bookMeta">${metaLines}</div>
        <div class="bookActions">
          <button class="btn btn-outline-primary btn-sm">Details</button>
          <button class="btn btn-primary btn-sm">Borrow</button>
        </div>
      </div>
    </div>
  `;
}

// ===== Helpers =====
function makeBadge(av) {
  const cls = av === 'available' ? 'success'
            : av === 'issued'    ? 'secondary'
            : av === 'reserved'  ? 'warning'
            : 'light';
  const label = av ? av[0].toUpperCase()+av.slice(1) : '—';
  return `<span class="badge bg-${cls}">${label}</span>`;
}

function dateLine(b) {
  if (typeof b.publication_year === 'number' || (b.publication_year && !Number.isNaN(Number(b.publication_year)))) {
    return `Publication: ${b.publication_year}`;
  }
  if (b.created_at) {
    const d = new Date(b.created_at);
    return `Added: ${isNaN(d) ? b.created_at : d.toLocaleDateString()}`;
  }
  return `Publication: —`;
}

function escapeHtml(s) {
  return String(s).replace(/[&<>"']/g, m => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[m]));
}

// ===== Events =====
if (searchInput) {
  let t;
  searchInput.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(() => { currentPage = 1; render(); }, 150);
  });
}
if (sortSelect) sortSelect.addEventListener('change', () => { currentPage = 1; render(); });
if (availSel)   availSel.addEventListener('change', () => { currentPage = 1; render(); });
if (catSel)     catSel.addEventListener('change', () => { currentPage = 1; render(); });

// ===== Init =====
fetchBooks();

