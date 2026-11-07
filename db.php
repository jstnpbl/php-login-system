<?php
// Opens the database connection ($conn). Settings come from config.php (not committed to Git).

// Make database errors throw exceptions we can catch, instead of failing silently.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    exit('Missing config.php. Copy config.example.php to config.php, then reload this page.');
}
$config = require $configFile;

try {
    $conn = new mysqli($config['host'], $config['user'], $config['password'], $config['database']);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // The details go to the server log, not to the visitor's screen.
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Could not connect to the database. Check config.php and that MySQL is running.');
}
