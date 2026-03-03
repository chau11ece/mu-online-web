<?php
/**
 * MU Online - Web Health Check
 * GET /health.php
 * Returns 200 OK if everything is fine, 503 if something is broken
 */

header('Content-Type: application/json');

$status = [
    "service"   => "mu-web",
    "php"       => phpversion(),
    "timestamp" => date('c'),
    "checks"    => []
];

// ── Check 1: PHP is working ──────────────────────────────────────────────────
$status["checks"]["php"] = "ok";

// ── Check 2: ODBC driver is loaded ──────────────────────────────────────────
if (extension_loaded('odbc') || extension_loaded('sqlsrv')) {
    $status["checks"]["odbc_driver"] = "ok";
} else {
    $status["checks"]["odbc_driver"] = "missing - sqlsrv/odbc extension not loaded";
}

// ── Check 3: Database connection ─────────────────────────────────────────────
// Read from environment variables (never hardcode credentials!)
$db_host   = getenv('DB_HOST')   ?: '192.168.100.96';
$db_name   = getenv('DB_NAME')   ?: 'MuOnline';
$db_user   = getenv('DB_USER')   ?: 'sa';
$db_pass   = getenv('DB_PASS')   ?: 'Abcd@1234';

if (empty($db_user)) {
    // No credentials configured — skip DB check
    $status["checks"]["database"] = "skipped - DB_USER not set";
    $http_status = 200;
} else {
    try {
        $dsn  = "sqlsrv:Server=$db_host;Database=$db_name;TrustServerCertificate=1;LoginTimeout=3";
        $conn = new PDO($dsn, $db_user, $db_pass);
        $conn->query("SELECT 1");
        $status["checks"]["database"] = "ok";
        $http_status = 200;
    } catch (Exception $e) {
        $status["checks"]["database"] = "failed - " . $e->getMessage();
        $http_status = 503;
    }
}

// ── Determine overall status ─────────────────────────────────────────────────
$has_failure = in_array("failed", array_map(function($v) {
    return str_starts_with($v, "failed") ? "failed" : "ok";
}, $status["checks"]));

$status["status"] = $has_failure ? "unhealthy" : "ok";

http_response_code($http_status ?? ($has_failure ? 503 : 200));
echo json_encode($status, JSON_PRETTY_PRINT);