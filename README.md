Made for **ITCS489 – Library System** coursework.




Here’s a **GitHub-ready README** (Markdown). You can copy-paste it into a file named `README.md` in your repo.

````md
# ITCS489 Library System

A web-based Library Management System built with **PHP**, **MySQL**, **Bootstrap**, and a simple **MVC-style** structure (`controller`, `models`, `view`, `public`).

The system supports:

- User authentication (login / signup)
- Browsing & searching the catalog
- Borrowing and returning books
- Reservations with FIFO queue logic
- User & admin notifications
- Admin area for reports and user management

---

## 🔧 Tech Stack

- **Backend:** PHP 8 / PDO & MySQLi
- **Database:** MySQL (`library_system` schema)
- **Frontend:** HTML5, CSS3, Bootstrap 5, Vanilla JS
- **Architecture:** MVC-ish (controllers, models, views)
- **Session-based Auth:** PHP `$_SESSION` for user/role tracking

---

## 📂 Project Structure

```text
ITCS489_Library_system-7/
├── config.php
├── app/
│   ├── controller/
│   ├── models/
│   └── view/
├── public/
│   ├── index.php
│   ├── css/
│   └── js/
├── imgs/
├── uploads/
└── .git/ (VCS metadata)
````

---

## ⚙️ Global Config

### `config.php`

Defines the base URL used throughout the app:

```php
define('BASE_URL', '/itcs489_library_system/');
```

> If you change the folder name or host, update this constant.

---

## 🌐 Public Entry & Assets

### `public/index.php`

* **Entry page** when visiting the root (e.g., `http://localhost/itcs489_library_system/public/`).
* Starts the session.
* Loads `config.php` and shared `navbar.php`.
* Renders a styled landing page (hero section, introduction, call-to-action) and links into the main app (home, catalog, login, etc.).

### `public/css/style.css`

Global styling for:

* Layout spacing and basic reset
* Navbar spacing (`padding-top` to account for fixed navbar)
* Cards, sections, typography utilities
* General frontend polish for homepage and shared components

### `public/css/style_CatalogSearch.css`

Styling specifically for **catalog search & browsing**:

* Layout for search filters, sorting controls, and grid of books
* Styles for search bar, filters panel, labels, tags
* Cards for each book (cover, title, metadata, buttons)

### `public/js/CatalogSearch.js`

Client-side logic to drive the **catalog browsing page**:

* Fetches books via a JSON **API endpoint** (`CatalogSearch_Browsingbackend.php`)
* Applies:

  * Search by title/author/keyword
  * Filters (categories, availability, etc.)
  * Sorting (e.g., A–Z, date, popularity)
* Handles language labels & mapping for display
* Renders results dynamically into the DOM

---

## 🧠 Models (`app/models/`)

### `app/models/dbconnect.php` — Database Helper

Defines the `Database` class:

* Uses **PDO** to connect to MySQL:

  ```php
  class Database {
      private $user = "root";
      private $host = "localhost";
      private $pass = "";
      private $dbname = "library_system";
      public $conn;
      public function __construct() { /* opens PDO connection */ }
      public function getPdo() { return $this->conn; }
  }
  ```

* Used by most controllers/views for DB access.

---

### `app/models/CreateDefaultDBTables.php` — Initial Schema Creator

* Connects to MySQL using PDO without database, then ensures DB + tables.

* Creates schema `library_system` and the main tables (if not exist):

  * `users` — user accounts, roles, contact info
  * `books` — book metadata (title, author, category, year, quantity, image path, etc.)
  * `borrows` — tracking borrowed books, due dates, return status, price/fine
  * `reservations` — reservation queue per book (FIFO)
  * `notifications` — announcements, due reminders, overdue alerts, reservation messages

* Can be included on pages like `HomePage-EN.php` or `login.php` to guarantee tables exist in development.

---

## 🎛 Controllers (`app/controller/`)

### `book.php`

Mixed controller/view page to:

* Display a basic interface around books (historically used as a management form).
* Interacts with the `books` table for simple operations.
* Some logic related to user updates and redirect to `managingusers.php`.

> This file is a candidate for refactor (split into pure controller + view).

---

### `BookApi.php`

JSON **API endpoint** for managing books:

* `Content-Type: application/json`

* Expects JSON or form data.

* Supports operations like:

  * **Add a new book** (`POST`) with required fields (`title`, `author`, `isbn`, etc.)
  * **Update existing book** via `isbn`
  * Responds with JSON objects: `{ "success": "...", "error": "..." }`

* Uses `Database` (PDO) from `dbconnect.php`.

---

### `BorrowBook.php`

Controller for **borrowing flow**:

* Requires `dbconnect.php` (MySQLi `$conn`) and `reservations_lib.php` (queue helpers).

* On `POST`, reads:

  * `bookid` (book ID)
  * `QuantityWanted` (quantity requested)
  * `user_id`
  * `dueDate`
  * `price` (optional)

* Business logic:

  * Validates quantities, stock availability.
  * Inserts a row into `borrows` table.
  * Decrements `books.quantity`.
  * Calls reservation helpers to notify next-in-line when stock frees up.

* On `GET` or invalid POST, can render or redirect to appropriate pages.

---

### `changestatus.php`

* Updates the **status/availability** of a book.
* Typically used when toggling availability (e.g., active/inactive) or setting a custom `status` column.
* Uses PDO to perform `UPDATE` on `books` (or related table).

---

### `checkifadmin.php`

* Ensures the current user is an **admin**:

  * Starts/uses the session.
  * If `$_SESSION['role']` is not `admin`, redirects or exits.

* Included at the top of admin-only pages (e.g., `AdminArea.php`, `notifications.php`, `reports.php`).

---

### `checkifstaff.php`

* Similar to `checkifadmin.php` but for **staff** (librarian/staff roles).
* Used to guard staff-only functionality (borrowing desk, reservations management, etc.).

---

### `deleterecord.php`

* REST-like endpoint to **delete a book** by ISBN:

  * Reads `isbn` from the request (query or POST).

  * Prepares:

    ```php
    DELETE FROM books WHERE isbn = :isbn
    ```

  * Returns JSON:

    * Success: `{ "success": "Book deleted successfully" }`
    * Not found / error: `{ "error": "..." }`

* Consumed by admin UI or JS fetch calls.

---

### `LoginSubmit.php`

* Handles **login form submission**:

  * Starts session.

  * Accepts username/password from `POST`.

  * Validates credentials against `users` table.

  * On success:

    * Sets `$_SESSION['user_id']`, `$_SESSION['username']`, `$_SESSION['role']`.
    * Redirects to `HomePage-EN.php` or appropriate landing page.

  * On failure:

    * Shows error message inside a simple HTML template.

---

### `Logout.php`

* Logs the user out:

  * Starts session if needed.
  * `session_unset()` and `session_destroy()`.
  * Redirects to `login.php` (or home).

---

### `ManagingUsers.php`

Admin-side controller to **manage user accounts**:

* Requires admin check.
* Uses PDO from `dbconnect.php`.
* Responsibilities:

  * List all users.
  * Create/update/delete user accounts.
  * Change roles (Admin, Librarian, Staff, VIP Student, Student).
  * Redirects back to management view after actions.

> Often drives an HTML interface for user management.

---

### `RegisterSubmit.php`

* Handles **sign-up** form submission:

  * Reads `username`, `password`, `email`, `role`, and optional `phone`.
  * Validates and inserts into `users` table.
  * May set a default role (e.g., Student).
  * On success, redirects to `login.php` or a welcome page.

---

### `reservations_lib.php`

Helpers for **reservation queue & notification logic** (MySQLi-based):

* `get_first_active_reservation(mysqli $conn, int $bookId): ?array`
  Returns the oldest `active` reservation (FIFO) for a given book.

* `fulfill_first_reservation(...)`
  Picks the first active reservation, moves it to `fulfilled`, and may create a borrow record.

* `notify_next_reserver(...)`
  When a book becomes available, creates a `notifications` entry for the next in queue and marks reservation as `notified`.

Used by:

* `BorrowBook.php`
* `bookReturnAndRenew.php`
* Other reservation-related flows.

---

### `UserProfileUpdatedSubmit.php`

* Handles **user profile updates** (admin editing a user):

  * Reads updated first/last name, phone number, role, email, etc.
  * Executes `UPDATE users SET ... WHERE user_id = ?`.
  * Redirects to `ManagingUsers` / user management page on success.

---

## 🎨 Views / Pages (`app/view/`)

### `AdminArea.php`

Admin dashboard:

* `session_start()` + `checkifadmin.php`.
* Uses Bootstrap 5, custom dark theme CSS.
* Shows:

  * Navigation to user management, reports, notifications control, reservation management.
  * Cards/sections summarizing overall system metrics and shortcuts.

---

### `bookPage.php`

Admin/staff **book management page**:

* Includes DB connection and navbar.

* Provides a **form** for:

  * Creating a new book (`title`, `author`, `isbn`, `category`, `publisher`, `year`, `quantity`, `image_path`, etc.).
  * Possibly editing an existing book.

* Posts to a controller endpoint (e.g. `BookApi.php` or another processor) to save data.

> Contains a comment noting it should be split into controller + view to be fully MVC.

---

### `bookReturnAndRenew.php`

Handles **returning and renewing borrowed books**:

* Includes `dbconnect.php` (MySQLi) and `reservations_lib.php`.

* On `POST`:

  * If `return`:

    * Marks `borrows.isReturned = 'true'`.
    * Increases `books.quantity`.
    * May notify next reservation in queue.

  * If `renew`:

    * Extends the `dueDate` for the borrow.
    * Validates that the book's not already overdue or blocked.

* Renders a Bootstrap-based page:

  * Shows success/error alerts.
  * Lists current borrows for a user with Return/Renew actions.

---

### `borrowedDashboard.php`

User’s **Borrowed Books dashboard**:

* Requires `navbar.php`, `dbconnect.php`, `CreateDefaultDBTables.php`.

* Uses `$_SESSION['user_id']` to:

  * Fetch all records from `borrows` for the logged-in user.
  * Display table: book info, due dates, returned status, possibly price/fine.

* Gives a quick overview of:

  * Active borrows
  * Returned items
  * Due and overdue items

---

### `catalogMuntadherTemporary.php`

Older / temporary **catalog display page**:

* Starts a session.
* Connects to DB.
* Outputs a simple HTML table showing:

  * `title`, `author`, `isbn`, `category`, `publisher`, `year`, `quantity`, `image_path`.

> Mostly kept for reference or testing; `CatalogSearch_Browsing-EN.php` is the main catalog page.

---

### `CatalogSearch_Browsing-EN.php`

Main **Catalog Search & Browsing** UI:

* HTML + Bootstrap layout.

* Includes:

  * `navbar.php`
  * `style_CatalogSearch.css`
  * `CatalogSearch.js`

* Sections:

  * Search bar & filters (categories, language, etc.).
  * Sort dropdown (e.g., Title asc/desc).
  * Results area populated dynamically via JS (using `CatalogSearch_Browsingbackend.php` as backend).

---

### `CatalogSearch_Browsingbackend.php`

Backend **JSON API** for catalog search:

* Pure PHP script responding with `Content-Type: application/json`.

* Uses `mysqli` to connect to `library_system`.

* Handles:

  * Query parameters for search text, filters, sort options.
  * Builds `SELECT` queries (joins or filtering on `books` table).
  * Normalizes publication year (`publication_year` or `year`).
  * Outputs an array of books as JSON.

* Error-handling:

  * Wraps DB access in try/catch.
  * Suppresses noisy HTML errors to keep JSON valid.
  * On error, sends `{ "status": "error", "message": "..." }` with `500` code.

---

### `editUserProfile.php`

Admin page to **edit a user account**:

* Requires:

  * `config.php`
  * `checkifadmin.php`
  * `dbconnect.php`

* Uses `$_GET['username']` to load a user.

* Displays a form for:

  * First name / last name
  * Role (select from: `Admin`, `Librarian`, `Staff`, `VIP Student`, `Student`)
  * Email
  * Phone number

* Submits changes to `UserProfileUpdatedSubmit.php`.

---

### `HomePage-EN.php`

Main **authenticated homepage**:

* Requires:

  * `config.php`
  * `dbconnect.php`
  * `CreateDefaultDBTables.php`
  * `navbar.php`

* Guards access:

  ```php
  if (!isset($_SESSION['username'])) {
      header("Location: login.php");
  }
  ```

* Fetches and displays **announcements** from `notifications` table where `type = 'announcement'`:

  * Only for the logged-in user (`user_id`).
  * Limits to recent announcements, marks them as read after viewing.

* Provides a dashboard-style introduction, shortcuts to catalog, borrowed dashboard, etc.

---

### `login.php`

Public **Login page**:

* Includes:

  * `navbar.php`
  * `dbconnect.php`
  * `CreateDefaultDBTables.php`

* Displays a Bootstrap form with:

  * Username
  * Password
  * Submit → sends to `LoginSubmit.php`.

---

### `navbar.php`

Global **navigation bar** included on most pages:

* Starts a session if needed.

* Requires `config.php` to build absolute URLs.

* If `$_SESSION['user_id']` is set:

  * Uses `dbconnect.php` to calculate unread notifications count.
  * Shows a **Notifications** button with a badge count.
  * Shows **Admin Area** button if `$_SESSION['role'] === 'admin'`.

* Provides links to:

  * Home
  * Catalog
  * Borrowed Dashboard
  * Login / Logout
  * User Notifications

---

### `notifications.php`

**Admin Notification Control Center**:

* Restricted via `checkifadmin.php`.

* Uses `dbconnect.php` (PDO) to manage `notifications` table.

* On `POST`, supports actions like:

  * `generate_due` — create reminder notifications for users whose due dates are N days away.
  * `generate_overdue` — create overdue alerts.
  * `generate_reservation` — create notifications about reservation availability.
  * `create_announcement` — broadcast announcements to many users.

* Shows:

  * Total and unread counts.
  * Recent notifications.
  * Flash messages summarizing operations (`$flash` array).

---

### `reports.php`

Admin **Reporting & Tracking** page:

* Restricted to admins.
* Uses PDO (`$pdo = $db->getPdo()`).

Provides multiple sections:

1. **Summary counts**:

   * Total users, books, active borrows, total reservations.

2. **Circulation stats**:

   * Borrow counts by day, week, month.

3. **Top borrowed books & top users**:

   * Queries `borrows` and `books` to rank them.

4. **Outstanding fines** (simple estimate):

   * Sums `price` from overdue, unreturned borrows.

* Renders data in tables, cards, and basic charts-style layouts.

---

### `reservations.php`

Admin/staff **Reservation Management** page:

* Uses `dbconnect.php` (`$db->conn` via PDO).

* Features:

  * Create reservation:

    * Form with user dropdown and book dropdown → inserts into `reservations`.

  * Prevents duplicates:

    * Checks if a user already has an `active`/`notified` reservation for a book.

  * List view:

    * Shows all reservations (user, book, status, date).
    * Actions to **cancel**, **fulfill**, or **notify**.

* Displays status messages (`$msg`) when actions are performed.

---

### `signup.php`

Public **Register page**:

* Includes `navbar.php`.

* Simple Bootstrap form:

  * Username
  * Password / confirm
  * Email
  * Phone number

* Submits to `RegisterSubmit.php`.

---

### `userNotifications.php`

User-facing **Notifications Center**:

* Requires login; if no `$_SESSION['user_id']`, redirects to `login.php`.

* Uses `dbconnect.php` + `config.php`.

* Fetches notifications for the logged-in user from `notifications` table.

* Provides counts by type:

  * Unread
  * Due soon
  * Overdue
  * Reservation
  * Announcement

* Allows actions:

  * Mark individual notification as read/unread.
  * Mark all as read.
  * Filter by type.

---

## 🖼 Media & Uploads

### `imgs/`

* Contains static images used in the UI (logos, decorative images, etc.).

### `uploads/`

* Contains **uploaded files**, primarily **book cover images**.
* Filenames often include timestamps and short identifiers, e.g.:

  * `1758965708_artofwar.webp`
  * `1758635506_kal.jpg`

> You may want to protect this directory (e.g., with `.htaccess` or proper path checks) in production.

---

## 🚀 Getting Started

1. **Clone the repository**

   ```bash
   git clone <your-repo-url>.git
   cd ITCS489_Library_system-7
   ```

2. **Configure Apache / Nginx**

   * Point your web root to `public/` or ensure URLs are built correctly with `BASE_URL` in `config.php`.

3. **Create MySQL database**

   ```sql
   CREATE DATABASE library_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Update DB credentials (if needed)**

   * Edit `app/models/dbconnect.php` and `app/models/CreateDefaultDBTables.php`:

     * DB host
     * Username
     * Password

5. **Generate tables**

   * Visit `login.php` or `HomePage-EN.php` in the browser.
   * `CreateDefaultDBTables.php` will ensure required tables exist.

6. **Create admin user**

   * Either manually insert into `users` table or adapt `RegisterSubmit.php` to create an admin.

7. **Login & test**

   * Go to: `http://localhost/itcs489_library_system/public/`
   * Use the Admin Area, Catalog, Borrowed Dashboard, and Notifications center to test the flows.

---

## ✅ Notes & Possible Improvements

* Some files mix **controller + view** logic (e.g., `book.php`, `LoginSubmit.php`).
* In a future refactor, you can:

  * Move database operations into controllers and models.
  * Keep views as mostly HTML/templating.
* Consider environment-based config for DB credentials and `BASE_URL`.

---

Made for **ITCS489 – Library System** coursework and easily extensible for real deployments.

```

If you want, I can also:

- Add **screenshots sections** to the README.
- Add **sequence diagrams / flow diagrams** as Markdown images.
```
