<?php
/**
 * Random Character Generator for Testing Ranking Functions
 * 
 * This script generates random characters with various levels, resets, and settings
 * to test the ranking functions at http://192.168.1.252/?p=topchars
 * 
 * Usage: Access via http://192.168.1.252/?p=generate_test_chars
 */

// Include config to get database connection
require_once $_SERVER['DOCUMENT_ROOT']."/configs/config.php";

// Character classes (Mu Online)
$character_classes = array(
    0 => 'Dark Wizard',
    1 => 'Soul Master', 
    2 => 'Grand Master',
    16 => 'Dark Knight',
    17 => 'Blade Knight',
    18 => 'Blade Master',
    32 => 'Fairy Elf',
    33 => 'Muse Elf',
    34 => 'High Elf',
    48 => 'Magic Gladiator',
    49 => 'Duel Master',
    64 => 'Dark Lord',
    65 => 'Lord Emperor',
    80 => 'Summoner',
    81 => 'Bloody Summoner',
    82 => 'Dimension Master',
    96 => 'Rage Fighter',
    97 => 'Fist Master'
);

// Maps
$maps = array(
    0 => 'Lorencia', 1 => 'Dungeon', 2 => 'Devias', 3 => 'Noria', 4 => 'Lost Tower',
    5 => 'Exile', 6 => 'Arena', 7 => 'Atlans', 8 => 'Tarkan', 9 => 'Devil Square',
    10 => 'Icarus', 30 => 'Kanturu', 31 => 'Kanturu Remain', 36 => 'Land of Trials',
    37 => 'Aida', 38 => 'Core Chamber', 40 => 'Crystal Cave', 41 => 'ARGOS'
);

// First names for generating random character names
$first_names = array(
    'Dragon', 'Phoenix', 'Shadow', 'Storm', 'Thunder', 'Blade', 'Knight',
    'Warrior', 'Mage', 'Archer', 'Hunter', 'Demon', 'Angel', 'Spirit',
    'Fire', 'Ice', 'Dark', 'Light', 'Star', 'Moon', 'Sun', 'Wolf',
    'Tiger', 'Lion', 'Eagle', 'Hawk', 'Raven', 'Bear', 'Viper', 'Scorpion'
);

// Titles for generating random character names
$title = array(
    'King', 'Queen', 'Lord', 'Lady', 'Master', 'Champion', 'Hero',
    'Legend', 'Myth', 'God', 'Slayer', 'Hunter', 'Killer', 'Breaker',
    'Crusher', 'Destroyer', 'Guardian', 'Protector', 'Defender', 'Avenger'
);

/**
 * Generate a random character name
 */
function generateCharacterName($used_names) {
    global $first_names, $title;
    
    for ($i = 0; $i < 100; $i++) {
        $name = $first_names[array_rand($first_names)] . $title[array_rand($title)];
        if (strlen($name) > 10) {
            $name = substr($name, 0, 10);
        }
        if (!in_array($name, $used_names)) {
            return $name;
        }
    }
    return "TestChar" . rand(1000, 9999);
}

/**
 * Generate a random account ID
 */
function generateAccountId($used_accounts) {
    for ($i = 0; $i < 100; $i++) {
        $account_id = "test" . rand(10000, 99999);
        if (!in_array($account_id, $used_accounts)) {
            return $account_id;
        }
    }
    return "test" . rand(10000, 99999);
}

/**
 * Generate random level
 */
function generateRandomLevel($min_level = 1, $max_level = 400) {
    $rand = rand(1, 100);
    if ($rand <= 30) return rand($min_level, 100);
    elseif ($rand <= 70) return rand(101, 250);
    elseif ($rand <= 90) return rand(251, 350);
    else return rand(351, $max_level);
}

/**
 * Generate random resets
 */
function generateRandomResets($level) {
    $max_resets = floor($level / 10);
    return rand(0, min($max_resets, 200));
}

/**
 * Generate random master level
 */
function generateRandomMasterLevel($regular_level) {
    if ($regular_level < 200) return 0;
    $max_ml = min(floor(($regular_level - 200) / 2), 400);
    return rand(0, $max_ml);
}

/**
 * Generate stats based on class
 */
function generateStats($class) {
    $class_type = floor($class / 16);
    switch ($class_type) {
        case 0: return array('strength' => rand(100, 300), 'dexterity' => rand(200, 400), 'vitality' => rand(200, 500), 'energy' => rand(600, 1500), 'leadership' => 0);
        case 1: return array('strength' => rand(800, 2000), 'dexterity' => rand(400, 800), 'vitality' => rand(600, 1500), 'energy' => rand(100, 300), 'leadership' => 0);
        case 2: return array('strength' => rand(200, 400), 'dexterity' => rand(800, 2000), 'vitality' => rand(400, 800), 'energy' => rand(400, 800), 'leadership' => 0);
        case 3: return array('strength' => rand(600, 1500), 'dexterity' => rand(600, 1500), 'vitality' => rand(400, 1000), 'energy' => rand(400, 1000), 'leadership' => 0);
        case 4: return array('strength' => rand(600, 1500), 'dexterity' => rand(400, 800), 'vitality' => rand(500, 1200), 'energy' => rand(400, 1000), 'leadership' => rand(800, 2500));
        case 5: return array('strength' => rand(100, 250), 'dexterity' => rand(200, 400), 'vitality' => rand(200, 500), 'energy' => rand(700, 1500), 'leadership' => 0);
        case 6: return array('strength' => rand(800, 2200), 'dexterity' => rand(400, 1000), 'vitality' => rand(600, 1500), 'energy' => rand(100, 400), 'leadership' => 0);
        default: return array('strength' => rand(200, 1000), 'dexterity' => rand(200, 1000), 'vitality' => rand(200, 1000), 'energy' => rand(200, 1000), 'leadership' => rand(100, 1000));
    }
}

/**
 * Calculate life/mana based on stats
 */
function calculateLifeMana($class, $vitality, $energy) {
    $class_type = floor($class / 16);
    $base_life = 60; $base_mana = 40;
    $vit_mult = array(3.5, 5.0, 3.0, 4.0, 4.5, 3.0, 5.5);
    $energy_mult = array(2.0, 2.0, 1.5, 2.0, 2.5, 2.5, 1.5);
    $life = $base_life + ($vitality * ($vit_mult[$class_type] ?? 3.5));
    $mana = $base_mana + ($energy * ($energy_mult[$class_type] ?? 2.0));
    return array('life' => floor($life), 'maxlife' => floor($life), 'mana' => floor($mana), 'maxmana' => floor($mana));
}

/**
 * Main function to generate test characters
 */
function generateTestCharacters($num_chars = 50) {
    global $sql_connect, $character_classes, $maps;
    
    echo "<h1>Random Character Generator for Ranking Tests</h1>";
    echo "<div style='background:#f0f0f0;padding:15px;margin:10px 0;border-radius:5px;'>";
    echo "=================================================<br>";
    echo "Generating $num_chars random test characters...<br>";
    echo "=================================================<br><br>";
    
    // Get existing character names
    $result = mssql_query("SELECT Name FROM Character");
    $used_names = array();
    while ($row = mssql_fetch_array($result)) {
        $used_names[] = $row['Name'];
    }
    
    // Get existing test accounts
    $result = mssql_query("SELECT memb___id FROM MEMB_INFO WHERE memb___id LIKE 'test%'");
    $used_accounts = array();
    while ($row = mssql_fetch_array($result)) {
        $used_accounts[] = $row['memb___id'];
    }
    
    $success_count = 0;
    $fail_count = 0;
    
    for ($i = 0; $i < $num_chars; $i++) {
        $char_name = generateCharacterName($used_names);
        $used_names[] = $char_name;
        
        $account_id = generateAccountId($used_accounts);
        $used_accounts[] = $account_id;
        
        $class = array_rand($character_classes);
        $level = generateRandomLevel(1, 400);
        $resets = generateRandomResets($level);
        $m_level = generateRandomMasterLevel($level);
        
        $stats = generateStats($class);
        $life_mana = calculateLifeMana($class, $stats['vitality'], $stats['energy']);
        
        $map = array_rand($maps);
        $map_x = rand(50, 230);
        $map_y = rand(50, 230);
        
        $money = rand(1000000, 10000000000);
        
        // Check if character already exists
        $result = mssql_query("SELECT Name FROM Character WHERE Name = '$char_name'");
        if (mssql_num_rows($result) > 0) {
            echo "Character $char_name already exists, skipping...<br>";
            $fail_count++;
            continue;
        }
        
        // Check if account exists
        $result = mssql_query("SELECT memb___id FROM MEMB_INFO WHERE memb___id = '$account_id'");
        if (mssql_num_rows($result) == 0) {
            // Create account
            $password = "test" . rand(1000, 9999);
            $password_hash = strtoupper(md5($account_id . $password));
            $personal_id = rand(1000000000000, 9999999999999);
            
            mssql_query("INSERT INTO MEMB_INFO (memb___id, memb__pwd, memb_name, sno__numb, ctl1_code, bloc_code) 
                VALUES ('$account_id', '$password_hash', '$account_id', '$personal_id', 0, 0)");
            
            mssql_query("INSERT INTO MEMB_STAT (memb___id, ConnectStat) VALUES ('$account_id', 0)");
        }
        
        // Insert character
        $inventory = str_repeat(chr(255), 7584);
        
        $sql = "INSERT INTO Character (
            AccountID, Name, cLevel, LevelUpPoint, Class, Experience,
            Strength, Dexterity, Vitality, Energy, Leadership,
            Money, Life, MaxLife, Mana, MaxMana,
            MapNumber, MapPosX, MapPosY, MapDir,
            PkCount, PkLevel, PkTime,
            MDate, LDate, CtlCode, RESETS, mLevel,
            Inventory, ChatLimitTime, FruitPoint,
            mlPoint, mlExperience, mlNextExp,
            WinDuels, LoseDuels, InventoryExpansion, PenaltyMask,
            ExGameServerCode
        ) VALUES (
            '$account_id', '$char_name', $level, ".rand(0,5000).", $class, ".rand(1000,1000000000).",
            {$stats['strength']}, {$stats['dexterity']}, {$stats['vitality']}, {$stats['energy']}, {$stats['leadership']},
            $money, {$life_mana['life']}, {$life_mana['maxlife']}, {$life_mana['mana']}, {$life_mana['maxmana']},
            $map, $map_x, $map_y, ".rand(0,7).",
            ".rand(0,10).", ".rand(0,3).", 0,
            GETDATE(), GETDATE(), 0, $resets, $m_level,
            0x".bin2hex($inventory).", 0, 0,
            0, 0, 0,
            0, 0, 0, 0, 0
        )";
        
        if (mssql_query($sql)) {
            // Link character to account
            $result = mssql_query("SELECT Id FROM AccountCharacter WHERE Id = '$account_id'");
            if (mssql_num_rows($result) > 0) {
                mssql_query("UPDATE AccountCharacter SET GameID1 = COALESCE(NULLIF(GameID1, ''), '$char_name') 
                    WHERE Id = '$account_id' AND (GameID1 IS NULL OR GameID1 = '')");
            } else {
                mssql_query("INSERT INTO AccountCharacter (Id, GameID1, Summoner, WarehouseExpansion, RageFighter, SecCode) 
                    VALUES ('$account_id', '$char_name', 0, 0, 0, 0)");
            }
            
            $class_name = $character_classes[$class];
            echo "Created: $char_name (Lvl: $level, Resets: $resets, MLvl: $m_level, Class: $class_name)<br>";
            $success_count++;
        } else {
            echo "Failed to create character: $char_name<br>";
            $fail_count++;
        }
    }
    
    echo "<br>=================================================<br>";
    echo "Summary:<br>";
    echo "  - Successfully created: $success_count characters<br>";
    echo "  - Failed/Skipped: $fail_count characters<br>";
    echo "=================================================<br>";
    echo "</div>";
    
    echo "<p><a href='?p=topchars' style='padding:10px;background:#4CAF50;color:white;text-decoration:none;border-radius:5px;'>View Top Characters Ranking</a></p>";
    
    // Show statistics
    echo "<h2>Character Statistics</h2>";
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;width:100%;'>";
    echo "<tr style='background:#eee;'><th>Class</th><th>Count</th><th>Avg Level</th><th>Avg Resets</th></tr>";
    
    $result = mssql_query("
        SELECT 
            Class,
            COUNT(*) as CharCount,
            AVG(cLevel) as AvgLevel,
            AVG(RESETS) as AvgResets
        FROM Character 
        WHERE AccountID LIKE 'test%'
        GROUP BY Class
    ");
    while ($row = mssql_fetch_array($result)) {
        $class_name = $character_classes[$row['Class']] ?? "Unknown ({$row['Class']})";
        echo "<tr><td>$class_name</td><td>{$row['CharCount']}</td><td>".round($row['AvgLevel'],1)."</td><td>".round($row['AvgResets'],1)."</td></tr>";
    }
    echo "</table>";
    
    echo "<h2>Top 10 Characters</h2>";
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;width:100%;'>";
    echo "<tr style='background:#eee;'><th>Rank</th><th>Name</th><th>Class</th><th>Level</th><th>Resets</th><th>Master Lvl</th></tr>";
    
    $result = mssql_query("
        SELECT TOP 10 Name, Class, cLevel, RESETS, mLevel
        FROM Character
        WHERE CtlCode = 0
        ORDER BY RESETS DESC, cLevel DESC
    ");
    $rank = 1;
    while ($row = mssql_fetch_array($result)) {
        $class_name = $character_classes[$row['Class']] ?? "Unknown";
        echo "<tr><td>$rank</td><td>{$row['Name']}</td><td>$class_name</td><td>{$row['cLevel']}</td><td>{$row['RESETS']}</td><td>{$row['mLevel']}</td></tr>";
        $rank++;
    }
    echo "</table>";
    
    // Option to clear test characters
    echo "<h2>Admin Options</h2>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='clear_test' value='1'>";
    echo "<input type='submit' value='Clear All Test Characters' onclick='return confirm(\"Are you sure you want to delete all test characters?\");' style='padding:10px;background:#f44336;color:white;border:none;border-radius:5px;cursor:pointer;'>";
    echo "</form>";
    
    // Handle clear request
    if (isset($_POST['clear_test'])) {
        mssql_query("DELETE FROM Character WHERE AccountID LIKE 'test%'");
        mssql_query("DELETE FROM MEMB_INFO WHERE memb___id LIKE 'test%'");
        echo "<p style='color:red;font-weight:bold;'>All test characters have been cleared!</p>";
    }
}

// Run the generator - check if form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate'])) {
    $num_chars = isset($_POST['num_chars']) ? intval($_POST['num_chars']) : 50;
    if ($num_chars < 1) $num_chars = 1;
    if ($num_chars > 500) $num_chars = 500;
    generateTestCharacters($num_chars);
} else {
    // Show form
    echo "<h1>Random Character Generator for Ranking Tests</h1>";
    echo "<div style='background:#f0f0f0;padding:20px;margin:10px 0;border-radius:5px;'>";
    echo "<p>This tool generates random test characters to test the ranking functions.</p>";
    echo "<form method='post'>";
    echo "<label>Number of characters to generate: ";
    echo "<input type='number' name='num_chars' value='50' min='1' max='500' style='padding:5px;width:100px;'>";
    echo "</label><br><br>";
    echo "<input type='submit' name='generate' value='Generate Test Characters' style='padding:10px 20px;background:#4CAF50;color:white;border:none;border-radius:5px;cursor:pointer;font-size:16px;'>";
    echo "</form>";
    echo "</div>";
    
    // Show current status
    $result = mssql_query("SELECT COUNT(*) as total FROM Character WHERE AccountID LIKE 'test%'");
    $row = mssql_fetch_array($result);
    $total_test = $row['total'];
    
    $result = mssql_query("SELECT COUNT(*) as total FROM Character WHERE CtlCode = 0");
    $row = mssql_fetch_array($result);
    $total_active = $row['total'];
    
    echo "<p>Current Status:</p>";
    echo "<ul>";
    echo "<li>Test Characters: $total_test</li>";
    echo "<li>Total Active Characters: $total_active</li>";
    echo "</ul>";
    
    if ($total_test > 0) {
        echo "<h2>Top 10 Test Characters</h2>";
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse;width:100%;'>";
        echo "<tr style='background:#eee;'><th>Rank</th><th>Name</th><th>Class</th><th>Level</th><th>Resets</th><th>Master Lvl</th></tr>";
        
        $result = mssql_query("
            SELECT TOP 10 Name, Class, cLevel, RESETS, mLevel
            FROM Character
            WHERE AccountID LIKE 'test%' AND CtlCode = 0
            ORDER BY RESETS DESC, cLevel DESC
        ");
        $rank = 1;
        while ($row = mssql_fetch_array($result)) {
            $class_name = $character_classes[$row['Class']] ?? "Unknown";
            echo "<tr><td>$rank</td><td>{$row['Name']}</td><td>$class_name</td><td>{$row['cLevel']}</td><td>{$row['RESETS']}</td><td>{$row['mLevel']}</td></tr>";
            $rank++;
        }
        echo "</table>";
    }
    
    echo "<p><a href='?p=topchars' style='padding:10px;background:#2196F3;color:white;text-decoration:none;border-radius:5px;'>View Full Rankings</a></p>";
}
?>
