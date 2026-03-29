<?php
/**
 * Test database connection
 * REMOVE THIS FILE AFTER DEBUGGING
 */

header('Content-Type: text/plain');

echo "=== Database Connection Test ===\n\n";

// Load constants
require_once('constants.php');

echo "1. Database Configuration:\n";
echo "   HOST: " . HOST . "\n";
echo "   USER: " . USER . "\n";
echo "   PASS: " . (PASS ? '[SET]' : '[EMPTY]') . "\n";
echo "   WEB_DB: " . WEB_DB . "\n";
echo "   DRIVER: " . DRIVER . "\n\n";

echo "2. Testing Connection:\n";

try {
    // Try PDO connection
    if (DRIVER === 'pdo_dblib') {
        $dsn = "dblib:host=" . HOST . ";dbname=" . WEB_DB;
        echo "   DSN: $dsn\n";

        $pdo = new PDO($dsn, USER, PASS, [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        echo "   ✓ Connection successful!\n\n";

        // Test query
        echo "3. Testing Query:\n";
        $stmt = $pdo->query("SELECT @@VERSION as version");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "   SQL Server Version: " . $result['version'] . "\n\n";

        // Test table access
        echo "4. Testing Table Access:\n";
        $stmt = $pdo->query("SELECT TOP 1 * FROM INFORMATION_SCHEMA.TABLES");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            echo "   ✓ Can query system tables\n";
            echo "   First table: " . $result['TABLE_NAME'] . "\n";
        }

    } else {
        echo "   ERROR: Unknown driver: " . DRIVER . "\n";
    }

} catch (PDOException $e) {
    echo "   ✗ Connection FAILED!\n";
    echo "   Error: " . $e->getMessage() . "\n";
    echo "   Code: " . $e->getCode() . "\n";
}
