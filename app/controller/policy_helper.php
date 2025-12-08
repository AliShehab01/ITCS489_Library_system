<?php

/**
 * Policy Helper - Retrieves borrowing policies from database
 */

function getSystemConfig(PDO $conn, string $key, $default = null)
{
    static $cache = [];

    if (isset($cache[$key])) {
        return $cache[$key];
    }

    try {
        $stmt = $conn->prepare("SELECT config_value FROM system_config WHERE config_key = :key");
        $stmt->execute([':key' => $key]);
        $value = $stmt->fetchColumn();
        $cache[$key] = $value !== false ? $value : $default;
        return $cache[$key];
    } catch (PDOException $e) {
        return $default;
    }
}

function getAllPolicies(PDO $conn): array
{
    try {
        $stmt = $conn->query("SELECT * FROM system_config ORDER BY config_key");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function updatePolicy(PDO $conn, string $key, string $value): bool
{
    try {
        $stmt = $conn->prepare("UPDATE system_config SET config_value = :val WHERE config_key = :key");
        return $stmt->execute([':val' => $value, ':key' => $key]);
    } catch (PDOException $e) {
        return false;
    }
}

function getBorrowLimitByRole(PDO $conn, string $role): int
{
    $role = strtolower($role);
    $key = "borrow_limit_{$role}";
    $defaults = ['admin' => 10, 'staff' => 10, 'vipstudent' => 7, 'student' => 3];
    return (int)getSystemConfig($conn, $key, $defaults[$role] ?? 3);
}

function getLoanDaysByRole(PDO $conn, string $role): int
{
    $role = strtolower($role);
    $key = "loan_days_{$role}";
    $defaults = ['admin' => 60, 'staff' => 60, 'vipstudent' => 30, 'student' => 14];
    return (int)getSystemConfig($conn, $key, $defaults[$role] ?? 14);
}

function getFineRate(PDO $conn): float
{
    return (float)getSystemConfig($conn, 'fine_rate_per_day', 1.00);
}

function getMaxRenewals(PDO $conn): int
{
    return (int)getSystemConfig($conn, 'max_renewals', 2);
}

function getReservationLimit(PDO $conn): int
{
    return (int)getSystemConfig($conn, 'reservation_limit', 5);
}
