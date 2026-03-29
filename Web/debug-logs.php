<?php
/**
 * Debug endpoint to check logs and errors
 * REMOVE THIS FILE AFTER DEBUGGING
 */

header('Content-Type: text/plain');

echo "=== Debug Information ===\n\n";

// Check if logs directory exists and is writable
$logsDir = '/var/www/html/application/logs';
echo "1. Logs directory check:\n";
echo "   Path: $logsDir\n";
echo "   Exists: " . (file_exists($logsDir) ? 'YES' : 'NO') . "\n";
echo "   Writable: " . (is_writable($logsDir) ? 'YES' : 'NO') . "\n";
echo "   Permissions: " . substr(sprintf('%o', fileperms($logsDir)), -4) . "\n\n";

// Check Tracy directory
$tracyDir = $logsDir . '/Tracy';
echo "2. Tracy directory check:\n";
echo "   Path: $tracyDir\n";
echo "   Exists: " . (file_exists($tracyDir) ? 'YES' : 'NO') . "\n";
echo "   Writable: " . (is_writable($tracyDir) ? 'YES' : 'NO') . "\n";
if (file_exists($tracyDir)) {
    echo "   Permissions: " . substr(sprintf('%o', fileperms($tracyDir)), -4) . "\n";
}
echo "\n";

// List recent error logs
echo "3. Recent error logs:\n";
if (is_dir($logsDir)) {
    $files = array_diff(scandir($logsDir), array('.', '..'));
    foreach ($files as $file) {
        if (strpos($file, 'error') !== false || strpos($file, 'system') !== false) {
            echo "   - $file\n";
        }
    }
}
echo "\n";

// Check Apache error log
echo "4. Recent Apache errors:\n";
$apacheLog = '/var/log/apache2/error.log';
if (file_exists($apacheLog)) {
    $lines = file($apacheLog);
    $recent = array_slice($lines, -20);
    foreach ($recent as $line) {
        if (stripos($line, 'error') !== false || stripos($line, 'fatal') !== false) {
            echo "   " . trim($line) . "\n";
        }
    }
} else {
    echo "   Apache log not accessible\n";
}

echo "\n5. PHP Info:\n";
echo "   PHP Version: " . phpversion() . "\n";
echo "   Error Reporting: " . error_reporting() . "\n";
echo "   Display Errors: " . ini_get('display_errors') . "\n\n";

// Database connection test
echo "6. Database Connection Test:\n";
require_once('constants.php');
echo "   HOST: " . HOST . "\n";
echo "   DATABASE: " . WEB_DB . "\n";
echo "   DRIVER: " . DRIVER . "\n";

try {
    if (DRIVER === 'pdo_dblib') {
        $dsn = "dblib:host=" . HOST . ";dbname=" . WEB_DB;
        $pdo = new PDO($dsn, USER, PASS, [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "   Status: ✓ CONNECTED\n";

        $stmt = $pdo->query("SELECT @@VERSION as version");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "   SQL Server: " . substr($result['version'], 0, 50) . "...\n";
    }
} catch (PDOException $e) {
    echo "   Status: ✗ FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n7. Recent Database Error Log:\n";
$dbLog = $logsDir . '/database_error_log_' . date('Y-m-d') . '.txt';
if (file_exists($dbLog)) {
    echo "   File: " . basename($dbLog) . "\n";
    $lines = file($dbLog);
    $recent = array_slice($lines, -10);
    foreach ($recent as $line) {
        echo "   " . trim($line) . "\n";
    }
} else {
    echo "   No database error log for today\n";
}
