<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function db(): mysqli {
    static $conn = null;
    if ($conn !== null) return $conn;

    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_errno) {
        error_log("MySQL connect failed: ({$conn->connect_errno}) {$conn->connect_error}");
        die("DB connection error");
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
