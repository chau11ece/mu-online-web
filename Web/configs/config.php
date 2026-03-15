<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
// DT Web 2.0 r00tme version 1.18 - PHP 8.2 Optimized
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header("Location:../error.php");
    exit();
}

// ================================================================================================
// SQL Server Connection Settings
// Reads from environment variables (set by Docker/Terraform), falls back to defaults for local dev
// ================================================================================================
$sql_host = getenv('SQL_SERVER') ?: (getenv('DB_HOST') ?: '192.168.100.96');
$sql_user = getenv('DB_USER')   ?: 'sa';
$sql_pass = getenv('DB_PASS')   ?: 'Abcd@1234';
$database = getenv('DB_NAME')   ?: 'MuOnline';

$option['web_address']        = getenv('PUBLIC_IP') ? "http://" . getenv('PUBLIC_IP') : "http://192.168.100.96";
$option['has_dl']             = 0;
$option['md5']                = 0;
$option['debug']              = 1; // 1=Show Errors (skip zbblock) / 0=Hidden
$option['default_admin']      = 'test';
$option['default_admin_ip']   = '127.0.0.1';
$option['item_hex_lenght']    = 64;
$option['theme']              = getenv('THEME') ?: ''; // Theme override via env var

// ================================================================================================
// Game & Economy Settings
// ================================================================================================
$option['bank_limit']         = '60000000000';
$option['ware_limit']         = '2000000000';
$option['inventory_limit']    = '2000000000';
$option['rc_level']           = 400;
$option['rc_zen']             = 20000000;
$option['rc_max_resets']      = 200;
$option['rc_stats_per_reset'] = 500;
$option['as_max_stats']       = 32767;

// Credits Table Config
$option['cr_db_column']       = "credits";
$option['cr_db_table']        = "Memb_Credits";
$option['cr_db_check_by']     = "memb___id";

// ================================================================================================
// MODERN PHP 8.2 COMPATIBILITY LAYER (The "Bridge")
// ================================================================================================
if (!function_exists('mssql_connect')) {
    // Advanced Query Class for DT Web Results
    class mssqlQuery {
        private $data = array();
        private $rowsCount = 0;
        public function __construct($res) {
            if ($res) {
                while ($d = sqlsrv_fetch_array($res, SQLSRV_FETCH_BOTH)) {
                    $this->data[] = $d;
                    $this->rowsCount++;
                }
                sqlsrv_free_stmt($res);
            }
        }
        public function getRowsCount() { return $this->rowsCount; }
        public function shiftData($type) {
            $d = array_shift($this->data);
            if (!$d) return false;
            if ($type == 2) { // ASSOC
                foreach ($d as $k => $v) { if (is_numeric($k)) unset($d[$k]); }
            } elseif ($type == 3) { // NUM
                foreach ($d as $k => $v) { if (!is_numeric($k)) unset($d[$k]); }
            }
            return $d;
        }
    }

    function mssql_connect($srv, $usr, $pw) {
        $info = array(
            "UID" => $usr, 
            "PWD" => $pw, 
            "Database" => "MuOnline", 
            "CharacterSet" => "UTF-8", 
            "TrustServerCertificate" => true,
            "Encrypt" => 0 // Add this to disable forced encryption for Driver 18
        );
        $GLOBALS['_conn'] = sqlsrv_connect($srv, $info);
    
        if ($GLOBALS['_conn'] === false) {
            echo "<h3>SQL Connection Debug:</h3><pre>";
            die(print_r(sqlsrv_errors(), true)); 
            echo "</pre>";
        }
        return $GLOBALS['_conn'];
    }

    function mssql_select_db($db, $c) { return true; }
    
    function mssql_query($q) {
        $res = sqlsrv_query($GLOBALS['_conn'], $q);
        if ($res === false) { return false; }
        return new mssqlQuery($res);
    }

    function mssql_fetch_array($queryObj, $type = 1) { return ($queryObj instanceof mssqlQuery) ? $queryObj->shiftData($type) : null; }
    function mssql_num_rows($queryObj) { return ($queryObj instanceof mssqlQuery) ? $queryObj->getRowsCount() : 0; }
    function mssql_close($c) { if(isset($GLOBALS['_conn'])) sqlsrv_close($GLOBALS['_conn']); }
}

// Define Constants used by the script
if (!defined('MSSQL_BOTH')) {
    define('MSSQL_BOTH', 1);
    define('MSSQL_ASSOC', 2);
    define('MSSQL_NUM', 3);
}

// Execute Connection
$sql_connect = mssql_connect($sql_host, $sql_user, $sql_pass) or die("Couldn't connect to SQL Server!");
$db_connect  = mssql_select_db($database, $sql_connect) or die("Couldn't open database!");

// Initialize Admin Check
if(mssql_num_rows(mssql_query("SELECT * FROM DTweb_GM_Accounts")) == 0){
    mssql_query("INSERT INTO [DTweb_GM_Accounts] (name,gm_level,ip) VALUES ('".$option['default_admin']."','666','".$option['default_admin_ip']."')");
}
?>