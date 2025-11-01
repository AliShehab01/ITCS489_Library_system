<?php
// reservations_lib.php
require_once '../models/dbconnect.php';

/**
 * Return the oldest ACTIVE reservation for a book (FIFO), or null if none.
 * Expected table: reservations(reservation_id, user_id, book_id, status, reserved_at)
 * status: 'active' | 'notified' | 'fulfilled' | 'cancelled'
 */
function get_first_active_reservation(mysqli $conn, int $bookId): ?array
{
    $sql = "SELECT reservation_id, user_id, reserved_at
            FROM reservations
            WHERE book_id = ? AND status = 'active'
            ORDER BY reserved_at ASC
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $bookId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: null;
}

/**
 * True if there is any ACTIVE reservation for this book.
 */
function has_active_queue(mysqli $conn, int $bookId): bool
{
    $sql = "SELECT 1
            FROM reservations
            WHERE book_id = ? AND status = 'active'
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $bookId);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

/**
 * Fairness check for Borrow page:
 * - If there is NO queue → anyone can borrow (return true)
 * - If there IS a queue → only the FIRST user in the queue may borrow (return true only for them)
 */
function borrower_is_first_in_queue(mysqli $conn, int $bookId, int $userId): bool
{
    $first = get_first_active_reservation($conn, $bookId);
    if (!$first) return true; // no queue → allow
    return (int)$first['user_id'] === $userId;
}

/**
 * When a user with a reservation successfully borrows the book,
 * mark their reservation as fulfilled so the queue advances fairly.
 */
function fulfill_user_reservation(mysqli $conn, int $bookId, int $userId): void
{
    // find that user's earliest active/notified reservation for this book
    $sql = "SELECT reservation_id
            FROM reservations
            WHERE book_id = ? AND user_id = ?
              AND status IN ('active','notified')
            ORDER BY reserved_at ASC
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $bookId, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) return;

    $rid = (int)$row['reservation_id'];
    $upd = $conn->prepare("UPDATE reservations SET status = 'fulfilled' WHERE reservation_id = ?");
    $upd->bind_param('i', $rid);
    $upd->execute();
}

/**
 * After a book is RETURNED (stock increased), call this to notify the next waiting user.
 * It inserts a row into `notifications` and marks their reservation as 'notified'.
 * Expected table: notifications(id, user_id, book_id, message, is_read, created_at)
 */
function notify_next_in_queue(mysqli $conn, int $bookId): void
{
    $first = get_first_active_reservation($conn, $bookId);
    if (!$first) return; // nobody waiting

    $reservationId = (int)$first['reservation_id'];
    $userId        = (int)$first['user_id'];

    $message = "Your reserved book is now available. Please borrow it soon.";
    $ins = $conn->prepare("
        INSERT INTO notifications (user_id, book_id, message, is_read)
        VALUES (?, ?, ?, 0)
    ");
    $ins->bind_param('iis', $userId, $bookId, $message);
    $ins->execute();

    $upd = $conn->prepare("UPDATE reservations SET status = 'notified' WHERE reservation_id = ?");
    $upd->bind_param('i', $reservationId);
    $upd->execute();
}
