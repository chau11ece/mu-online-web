<?php
/**
 * Eloquent Test Script
 * 
 * Tests the Eloquent ORM setup for DT Web 2.0
 * 
 * Run: php test_eloquent.php
 */

// Load autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load config (for database settings)
require_once __DIR__ . '/configs/config.php';

// Initialize Eloquent
require_once __DIR__ . '/app/Database/EloquentServiceProvider.php';

use App\Database\EloquentServiceProvider;
use App\Models\Character;
use App\Models\Member;
use App\Models\MemberStat;
use App\Models\Guild;
use App\Models\WebSettings;

echo "=== DT Web 2.0 Eloquent Test ===\n\n";

try {
    // Initialize Eloquent
    echo "[1] Initializing Eloquent...\n";
    EloquentServiceProvider::init();
    echo "    ✓ Eloquent initialized successfully\n\n";
    
    // Test WebSettings
    echo "[2] Testing WebSettings...\n";
    $settings = WebSettings::first();
    if ($settings) {
        echo "    ✓ Title: " . $settings->getTitle() . "\n";
        echo "    ✓ Server: " . $settings->getServerName() . "\n";
    } else {
        echo "    ✗ No settings found\n";
    }
    echo "\n";
    
    // Test Character count
    echo "[3] Testing Character model...\n";
    $totalChars = Character::count();
    echo "    ✓ Total characters: $totalChars\n";
    
    // Get top characters
    $topChars = Character::orderBy('Resets', 'desc')->orderBy('cLevel', 'desc')->limit(5)->get();
    echo "    ✓ Top characters:\n";
    foreach ($topChars as $char) {
        echo "      - {$char->Name} (Lv{$char->cLevel}, {$char->Resets} resets)\n";
    }
    echo "\n";
    
    // Test Member count
    echo "[4] Testing Member model...\n";
    $totalMembers = Member::count();
    echo "    ✓ Total accounts: $totalMembers\n";
    
    // Test online players
    echo "[5] Testing online players...\n";
    $onlineCount = MemberStat::getOnlineCount();
    echo "    ✓ Online players: $onlineCount\n";
    
    // Test Guilds
    echo "[6] Testing Guild model...\n";
    $totalGuilds = Guild::count();
    echo "    ✓ Total guilds: $totalGuilds\n";
    
    $topGuilds = Guild::orderBy('G_Score', 'desc')->limit(3)->get();
    echo "    ✓ Top guilds:\n";
    foreach ($topGuilds as $guild) {
        echo "      - {$guild->G_Name} (Score: {$guild->G_Score})\n";
    }
    echo "\n";
    
    echo "=== All Tests Passed! ===\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
