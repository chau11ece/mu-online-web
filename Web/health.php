<?php
/**
 * Health Check Endpoint
 * Returns JSON response for CI/CD pipeline validation
 */

// Set JSON header
header('Content-Type: application/json');
http_response_code(200);

// Start output buffering to catch any PHP errors
ob_start();

// Basic health checks
$checks = [];

// Check PHP version
$checks['php'] = phpversion();

// Check if required extensions are loaded
$required_extensions = ['pdo', 'pdo_dblib', 'zip'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        $checks[$ext] = 'loaded';
    } else {
        $checks[$ext] = 'missing';
    }
}

// Check optional extensions (don't fail if missing)
$optional_extensions = ['gd', 'pdo_sqlsrv'];
foreach ($optional_extensions as $ext) {
    if (extension_loaded($ext)) {
        $checks[$ext] = 'loaded';
    } else {
        $checks[$ext] = 'not_installed';
    }
}

// Check database connectivity (only if DB_HOST is set and driver is available)
$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

if ($db_host && $db_name && $db_user && extension_loaded('pdo_sqlsrv')) {
    try {
        $dsn = "sqlsrv:Server=$db_host;Database=$db_name";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $checks['database'] = 'connected';
    } catch (PDOException $e) {
        $checks['database'] = 'failed: ' . $e->getMessage();
    }
} else {
    $checks['database'] = 'skipped (no DB config or driver)';
}

// Check disk space
$free_bytes = disk_free_space("/");
$checks['disk_free'] = round($free_bytes / 1024 / 1024, 2) . ' MB';

// Check memory usage
$memory_usage = memory_get_usage(true);
$checks['memory_usage'] = round($memory_usage / 1024 / 1024, 2) . ' MB';

// Determine overall status
$has_failures = false;
foreach ($checks as $key => $value) {
    // Only fail if required extensions are missing
    if (strpos($value, 'missing') !== false && in_array($key, ['pdo', 'pdo_dblib', 'zip'])) {
        $has_failures = true;
        break;
    }
}

$response = [
    'status' => $has_failures ? 'degraded' : 'ok',
    'timestamp' => date('c'),
    'checks' => $checks
];

// Clear any output buffer (to remove PHP warnings/notices)
ob_end_clean();

// Output JSON
echo json_encode($response, JSON_PRETTY_PRINT);
