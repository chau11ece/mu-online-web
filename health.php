<?php
/**
 * Health Check Endpoint
 * Returns JSON response for CI/CD pipeline validation
 */

header('Content-Type: application/json');
http_response_code(200);

// Basic health checks
$checks = [];

// Check PHP version
$checks['php'] = phpversion();

// Check if required extensions are loaded
$required_extensions = ['pdo', 'pdo_dblib', 'zip', 'gd'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        $checks[$ext] = 'loaded';
    } else {
        $checks[$ext] = 'missing';
    }
}

// Check database connectivity (if configured)
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'MuOnline';
$db_user = getenv('DB_USER') ?: 'sa';
$db_pass = getenv('DB_PASS') ?: '';

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

// Check disk space
$free_bytes = disk_free_space("/");
$checks['disk_free'] = round($free_bytes / 1024 / 1024, 2) . ' MB';

// Check memory usage
$memory_usage = memory_get_usage(true);
$checks['memory_usage'] = round($memory_usage / 1024 / 1024, 2) . ' MB';

// Determine overall status
$has_failures = false;
foreach ($checks as $key => $value) {
    if (strpos($value, 'failed') !== false || strpos($value, 'missing') !== false) {
        $has_failures = true;
        break;
    }
}

$response = [
    'status' => $has_failures ? 'degraded' : 'ok',
    'timestamp' => date('c'),
    'checks' => $checks
];

echo json_encode($response, JSON_PRETTY_PRINT);
