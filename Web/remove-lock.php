<?php
/**
 * Remove installation lock file
 * Run this once to allow reinstallation
 * DELETE THIS FILE AFTER USE
 */

header('Content-Type: text/plain');

$lockFile = __DIR__ . '/setup/data/install.lock';

echo "=== Installation Lock Remover ===\n\n";
echo "Lock file: $lockFile\n";

if (file_exists($lockFile)) {
    if (unlink($lockFile)) {
        echo "Status: ✓ Lock file removed successfully\n\n";
        echo "You can now proceed with installation at:\n";
        echo "http://danangmu.com/setup/\n";
    } else {
        echo "Status: ✗ Failed to remove lock file\n";
        echo "Error: Permission denied\n\n";
        echo "Manual removal required:\n";
        echo "SSH to server and run: rm /var/www/html/setup/data/install.lock\n";
    }
} else {
    echo "Status: ✓ Lock file doesn't exist (already removed)\n\n";
    echo "You can proceed with installation at:\n";
    echo "http://danangmu.com/setup/\n";
}

echo "\n=== IMPORTANT ===\n";
echo "After setup is complete, delete this file:\n";
echo "rm /var/www/html/remove-lock.php\n";
