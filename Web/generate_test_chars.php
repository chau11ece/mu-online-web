<?php
/**
 * Random Character Generator for Testing Ranking Functions
 * 
 * This script generates random characters with various levels, resets, and settings
 * to test the ranking functions at http://192.168.1.252/?p=topchars
 * 
 * Usage: 
 *   - Access via browser: http://192.168.1.252/generate_test_chars.php
 *   - Or run via CLI: php generate_test_chars.php
 */

// Database connection settings
$sql_host = 'mu-sqlserver';
$sql_user = 'sa';
$sql_pass = 'Abcd@1234';
$database = 'MuOnline';

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
    0 => 'Lorencia',
    1 => 'Dungeon',
    2 => 'Devias',
    3 => 'Noria',
    4 => 'Lost Tower',
    5 => 'Exile',
    6 => 'Arena',
    7 => 'Atlans',
    8 => 'Tarkan',
    9 => 'Devil Square',
    10 => 'Icarus',
    11 => 'Blood Castle 1',
    12 => 'Blood Castle 2',
    13 => 'Blood Castle 3',
    14 => 'Blood Castle 4',
    15 => 'Blood Castle 5',
    16 => 'Blood Castle 6',
    17 => 'Blood Castle 7',
    18 => 'Chaos Castle 1',
    19 => 'Chaos Castle 2',
    20 => 'Chaos Castle 3',
    21 => 'Chaos Castle 4',
    22 => 'Chaos Castle 5',
    23 => 'Chaos Castle 6',
    24 => 'Kalima 1',
    25 => 'Kalima 2',
    26 => 'Kalima 3',
    27 => 'Kalima 4',
    28 => 'Kalima 5',
    29 => 'Kalima 6',
    30 => 'Kanturu',
    31 => 'Kanturu Remain',
    33 => 'Barracks',
    34 => 'Refuge',
    36 => 'Land of Trials',
    37 => 'Aida',
    38 => 'Core Chamber',
    39 => 'Egg Dragon',
    40 => 'Crystal Cave',
    41 => 'ARGOS',
    42 => 'Crossbow',
    43 => 'Zaiyerro',
    44 => 'Vip Map 1',
    45 => 'Vip Map 2',
    46 => 'Vip Map 3',
    47 => 'Vip Map 4',
    56 => 'Swamp of Meditation',
    57 => 'Pirates',
    58 => 'Pirates',
    59 => 'Icarus',
    60 => 'Global Map 1',
    61 => 'Global Map 2',
    62 => 'Global Map 3',
    63 => 'Global Map 4'
);

// First names for generating random character names
$first_names = array(
    'Dragon', 'Phoenix', 'Shadow', 'Storm', 'Thunder', 'Blade', 'Knight',
    'Warrior', 'Mage', 'Archer', 'Hunter', 'Demon', 'Angel', 'Spirit',
    'Fire', 'Ice', 'Dark', 'Light', 'Star', 'Moon', 'Sun', 'Wolf',
    'Tiger', 'Lion', 'Eagle', 'Hawk', 'Raven', 'Wolf', 'Bear', 'Viper',
    'Scorpion', 'Spider', 'Snake', 'Panther', 'Panther', 'Cougar', 'Lynx'
);

// Titles for generating random character names
$title = array(
    'King', 'Queen', 'Lord', 'Lady', 'Master', 'Champion', 'Hero',
    'Legend', 'Myth', 'God', 'Slayer', 'Hunter', 'Killer', 'Breaker',
    'Crusher', 'Destroyer', 'Hunter', 'Seeker', 'Hunter', 'Keeper',
    'Guardian', 'Protector', 'Defender', 'Avenger', 'Revenant', 'Phantom'
);

/**
 * Generate a random character name
 */
function generateCharacterName($used_names) {
    global $first_names, $title;
    
    $max_attempts = 100;
    for ($i = 0; $i < $max_attempts; $i++) {
        $name = $first_names[array_rand($first_names)] . $title[array_rand($title)];
        
        // Ensure name is at most 10 characters
        if (strlen($name) > 10) {
            $name = substr($name, 0, 10);
        }
        
        // Check if name is already used
        if (!in_array($name, $used_names)) {
            return $name;
        }
    }
    
    // Fallback: generate unique name with random number
    return "TestChar" . rand(1000, 9999);
}

/**
 * Generate a random account ID
 */
function generateAccountId($used_accounts) {
    $max_attempts = 100;
    for ($i = 0; $i < $max_attempts; $i++) {
        $account_id = "test" . rand(10000, 99999);
        
        if (!in_array($account_id, $used_accounts)) {
            return $account_id;
        }
    }
    
    return "test" . rand(10000, 99999);
}

/**
 * Generate random level based on settings
 */
function generateRandomLevel($min_level = 1, $max_level = 400) {
    // Higher probability for mid-levels
    $rand = rand(1, 100);
    if ($rand <= 30) {
        // Low levels: 1-100
        return rand($min_level, 100);
    } elseif ($rand <= 70) {
        // Mid levels: 101-250
        return rand(101, 250);
    } elseif ($rand <= 90) {
        // High levels: 251-350
        return rand(251, 350);
    } else {
        // Very high levels: 351-max
        return rand(351, $max_level);
    }
}

/**
 * Generate random resets
 */
function generateRandomResets($level) {
    // Resets roughly correlate with level
    $max_resets = floor($level / 10);
    return rand(0, min($max_resets, 200));
}

/**
 * Generate random master level
 */
function generateRandomMasterLevel($regular_level) {
    if ($regular_level < 200) {
        return 0;
    }
    
    // Master level based on regular level
    $max_ml = min(floor(($regular_level - 200) / 2), 400);
    return rand(0, $max_ml);
}

/**
 * Generate stats based on class
 */
function generateStats($class) {
    // Base stats for each class type
    $class_type = floor($class / 16);
    
    switch ($class_type) {
        case 0: // Wizard/SM/GM
            return array(
                'strength' => rand(100, 300),
                'dexterity' => rand(200, 400),
                'vitality' => rand(200, 500),
                'energy' => rand(600, 1500),
                'leadership' => 0
            );
        case 1: // Knight/BK/BM
            return array(
                'strength' => rand(800, 2000),
                'dexterity' => rand(400, 800),
                'vitality' => rand(600, 1500),
                'energy' => rand(100, 300),
                'leadership' => 0
            );
        case 2: // Elf/ME/HE
            return array(
                'strength' => rand(200, 400),
                'dexterity' => rand(800, 2000),
                'vitality' => rand(400, 800),
                'energy' => rand(400, 800),
                'leadership' => 0
            );
        case 3: // MG/DM
            return array(
                'strength' => rand(600, 1500),
                'dexterity' => rand(600, 1500),
                'vitality' => rand(400, 1000),
                'energy' => rand(400, 1000),
                'leadership' => 0
            );
        case 4: // DL/LE
            return array(
                'strength' => rand(600, 1500),
                'dexterity' => rand(400, 800),
                'vitality' => rand(500, 1200),
                'energy' => rand(400, 1000),
                'leadership' => rand(800, 2500)
            );
        case 5: // Summoner/BS/DM
            return array(
                'strength' => rand(100, 250),
                'dexterity' => rand(200, 400),
                'vitality' => rand(200, 500),
                'energy' => rand(700, 1500),
                'leadership' => 0
            );
        case 6: // RF/FM
            return array(
                'strength' => rand(800, 2200),
                'dexterity' => rand(400, 1000),
                'vitality' => rand(600, 1500),
                'energy' => rand(100, 400),
                'leadership' => 0
            );
        default:
            return array(
                'strength' => rand(200, 1000),
                'dexterity' => rand(200, 1000),
                'vitality' => rand(200, 1000),
                'energy' => rand(200, 1000),
                'leadership' => rand(100, 1000)
            );
    }
}

/**
 * Calculate experience needed for a level
 */
function calculateExperience($level) {
    // Mu Online experience formula (approximate)
    if ($level <= 255) {
        return floor(pow($level, 3) * ($level + 100) / 100);
    } else {
        // For season 6+ with master level
        return floor(pow(255, 3) * 100 + pow($level - 254, 3) * ($level - 254 + 100));
    }
}

/**
 * Calculate life/mana based on stats
 */
function calculateLifeMana($class, $vitality, $energy) {
    $class_type = floor($class / 16);
    
    // Base values
    $base_life = 60;
    $base_mana = 40;
    
    // Vitality to life multiplier
    $vit_mult = array(3.5, 5.0, 3.0, 4.0, 4.5, 3.0, 5.5);
    $energy_mult = array(2.0, 2.0, 1.5, 2.0, 2.5, 2.5, 1.5);
    
    $life = $base_life + ($vitality * ($vit_mult[$class_type] ?? 3.5));
    $mana = $base_mana + ($energy * ($energy_mult[$class_type] ?? 2.0));
    
    return array(
        'life' => floor($life),
        'maxlife' => floor($life),
        'mana' => floor($mana),
        'maxmana' => floor($mana)
    );
}

/**
 * Main function to generate test characters
 */
function generateTestCharacters($num_chars = 50) {
    global $sql_host, $sql_user, $sql_pass, $database, $character_classes, $maps;
    
    // Connect to database
    $conn = sqlsrv_connect($sql_host, array(
        "UID" => $sql_user,
        "PWD" => $sql_pass,
        "Database" => $database,
        "CharacterSet" => "UTF-8",
        "TrustServerCertificate" => true,
        "Encrypt" => false
    ));
    
    if (!$conn) {
        die("Database connection failed: " . print_r(sqlsrv_errors(), true));
    }
    
    echo "<h1>Random Character Generator for Ranking Tests</h1>";
    echo "<pre>";
    echo "=================================================\n";
    echo "Generating $num_chars random test characters...\n";
    echo "=================================================\n\n";
    
    // Get existing character names
    $sql = "SELECT Name FROM Character";
    $stmt = sqlsrv_query($conn, $sql);
    $used_names = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $used_names[] = $row['Name'];
    }
    sqlsrv_free_stmt($stmt);
    
    // Get existing accounts
    $sql = "SELECT memb___id FROM MEMB_INFO WHERE memb___id LIKE 'test%'";
    $stmt = sqlsrv_query($conn, $sql);
    $used_accounts = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $used_accounts[] = $row['memb___id'];
    }
    sqlsrv_free_stmt($stmt);
    
    $success_count = 0;
    $fail_count = 0;
    
    for ($i = 0; $i < $num_chars; $i++) {
        // Generate random character data
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
        $experience = calculateExperience($level);
        
        // Generate random other values
        $pk_count = rand(0, 10);
        $pk_level = ($pk_count > 0) ? rand(1, 3) : 0;
        $pk_time = 0;
        $ctl_code = 0; // Active character
        
        $level_up_point = rand(0, 5000);
        
        // Default empty inventory (hex: 0xFF...)
        $inventory = str_repeat("\xFF", 7584);
        
        // Insert account if not exists
        $sql_check_account = "SELECT memb___id FROM MEMB_INFO WHERE memb___id = ?";
        $params = array($account_id);
        $stmt = sqlsrv_query($conn, $sql_check_account, $params);
        $account_exists = sqlsrv_has_rows($stmt);
        sqlsrv_free_stmt($stmt);
        
        if (!$account_exists) {
            // Create account with random password
            $password = "test" . rand(1000, 9999);
            $password_hash = strtoupper(md5($account_id . $password));
            
            $sql_insert_account = "
                INSERT INTO MEMB_INFO (memb___id, memb__pwd, memb_name, sno__numb, ctl1_code, bloc_code)
                VALUES (?, ?, ?, ?, 0, 0)
            ";
            $params = array($account_id, $password_hash, $account_id, rand(1000000000000, 9999999999999));
            $stmt = sqlsrv_query($conn, $sql_insert_account, $params);
            
            if ($stmt) {
                sqlsrv_free_stmt($stmt);
                
                // Also insert into MEMB_STAT
                $sql_insert_stat = "
                    INSERT INTO MEMB_STAT (memb___id, ConnectStat)
                    VALUES (?, 0)
                ";
                $params = array($account_id);
                sqlsrv_query($conn, $sql_insert_stat, $params);
            }
        }
        
        // Check if character name already exists
        $sql_check_char = "SELECT Name FROM Character WHERE Name = ?";
        $params = array($char_name);
        $stmt = sqlsrv_query($conn, $sql_check_char, $params);
        $char_exists = sqlsrv_has_rows($stmt);
        sqlsrv_free_stmt($stmt);
        
        if ($char_exists) {
            echo "Character $char_name already exists, skipping...\n";
            $fail_count++;
            continue;
        }
        
        // Insert character
        $sql_insert_char = "
            INSERT INTO Character (
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
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?,
                GETDATE(), GETDATE(), ?, ?,
                ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?, 0
            )
        ";
        
        $params = array(
            $account_id, $char_name, $level, $level_up_point, $class, $experience,
            $stats['strength'], $stats['dexterity'], $stats['vitality'], $stats['energy'], $stats['leadership'],
            $money, $life_mana['life'], $life_mana['maxlife'], $life_mana['mana'], $life_mana['maxmana'],
            $map, $map_x, $map_y, rand(0, 7),
            $pk_count, $pk_level, $pk_time,
            $ctl_code, $resets, $m_level,
            $inventory, 0, 0,
            0, 0, 0,
            0, 0, 0, 0
        );
        
        $stmt = sqlsrv_query($conn, $sql_insert_char, $params);
        
        if ($stmt) {
            sqlsrv_free_stmt($stmt);
            
            // Link character to account in AccountCharacter
            $sql_check_acc_char = "SELECT Id FROM AccountCharacter WHERE Id = ?";
            $params = array($account_id);
            $stmt = sqlsrv_query($conn, $sql_check_acc_char, $params);
            
            if (sqlsrv_has_rows($stmt)) {
                // Account exists, add character to first empty slot
                $sql_update_acc_char = "
                    UPDATE AccountCharacter SET 
                    GameID1 = COALESCE(NULLIF(GameID1, ''), ?),
                    GameID2 = CASE WHEN GameID1 IS NOT NULL AND GameID1 <> '' THEN COALESCE(NULLIF(GameID2, ''), ?) ELSE GameID2 END,
                    GameID3 = CASE WHEN GameID2 IS NOT NULL AND GameID2 <> '' THEN COALESCE(NULLIF(GameID3, ''), ?) ELSE GameID3 END,
                    GameID4 = CASE WHEN GameID3 IS NOT NULL AND GameID3 <> '' THEN COALESCE(NULLIF(GameID4, ''), ?) ELSE GameID4 END,
                    GameID5 = CASE WHEN GameID4 IS NOT NULL AND GameID4 <> '' THEN COALESCE(NULLIF(GameID5, ''), ?) ELSE GameID5 END
                    WHERE Id = ?
                ";
                $params = array($char_name, $char_name, $char_name, $char_name, $char_name, $account_id);
            } else {
                // Create new account character record
                $sql_insert_acc_char = "
                    INSERT INTO AccountCharacter (Id, GameID1, Summoner, WarehouseExpansion, RageFighter, SecCode)
                    VALUES (?, ?, 0, 0, 0, 0)
                ";
                $params = array($account_id, $char_name);
            }
            sqlsrv_free_stmt($stmt);
            
            sqlsrv_query($conn, isset($sql_update_acc_char) ? $sql_update_acc_char : $sql_insert_acc_char, $params ?? array());
            
            $class_name = $character_classes[$class];
            echo "Created: $char_name (Lvl: $level, Resets: $resets, MLvl: $m_level, Class: $class_name)\n";
            $success_count++;
        } else {
            echo "Failed to create character: $char_name\n";
            $fail_count++;
        }
    }
    
    echo "\n=================================================\n";
    echo "Summary:\n";
    echo "  - Successfully created: $success_count characters\n";
    echo "  - Failed/Skipped: $fail_count characters\n";
    echo "=================================================\n";
    echo "</pre>";
    
    echo "<p><a href='?p=topchars'>View Top Characters Ranking</a></p>";
    
    // Show statistics
    echo "<h2>Character Statistics</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Class</th><th>Count</th><th>Avg Level</th><th>Avg Resets</th></tr>";
    
    $sql_stats = "
        SELECT 
            Class,
            COUNT(*) as CharCount,
            AVG(cLevel) as AvgLevel,
            AVG(RESETS) as AvgResets
        FROM Character 
        WHERE AccountID LIKE 'test%'
        GROUP BY Class
    ";
    $stmt = sqlsrv_query($conn, $sql_stats);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $class_name = $character_classes[$row['Class']] ?? "Unknown ({$row['Class']})";
        echo "<tr>";
        echo "<td>$class_name</td>";
        echo "<td>{$row['CharCount']}</td>";
        echo "<td>" . round($row['AvgLevel'], 1) . "</td>";
        echo "<td>" . round($row['AvgResets'], 1) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>Top 10 Characters</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Rank</th><th>Name</th><th>Class</th><th>Level</th><th>Resets</th><th>Master Lvl</th></tr>";
    
    $sql_top = "
        SELECT TOP 10 Name, Class, cLevel, RESETS, mLevel
        FROM Character
        WHERE CtlCode = 0
        ORDER BY RESETS DESC, cLevel DESC
    ";
    $rank = 1;
    $stmt = sqlsrv_query($conn, $sql_top);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $class_name = $character_classes[$row['Class']] ?? "Unknown";
        echo "<tr>";
        echo "<td>$rank</td>";
        echo "<td>{$row['Name']}</td>";
        echo "<td>$class_name</td>";
        echo "<td>{$row['cLevel']}</td>";
        echo "<td>{$row['RESETS']}</td>";
        echo "<td>{$row['mLevel']}</td>";
        echo "</tr>";
        $rank++;
    }
    echo "</table>";
    
    // Option to clear test characters
    echo "<h2>Admin Options</h2>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='clear_test' value='1'>";
    echo "<input type='submit' value='Clear All Test Characters' onclick='return confirm(\"Are you sure you want to delete all test characters?\");'>";
    echo "</form>";
    
    // Handle clear request
    if (isset($_POST['clear_test'])) {
        $sql_delete_chars = "DELETE FROM Character WHERE AccountID LIKE 'test%'";
        sqlsrv_query($conn, $sql_delete_chars);
        
        $sql_delete_acc = "DELETE FROM MEMB_INFO WHERE memb___id LIKE 'test%'";
        sqlsrv_query($conn, $sql_delete_acc);
        
        echo "<p><strong>All test characters have been cleared!</strong></p>";
    }
    
    sqlsrv_close($conn);
}

// Run the generator
generateTestCharacters(50);
?>
