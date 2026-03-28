<?php
/**
 * Eloquent Database Service Provider
 * 
 * Initializes Laravel's Eloquent ORM for the DT Web 2.0 application.
 * This provides a modern database abstraction layer over the existing SQL Server connection.
 * 
 * Usage:
 *   require_once __DIR__ . '/app/Database/EloquentServiceProvider.php';
 *   use App\Database\EloquentServiceProvider;
 *   EloquentServiceProvider::init();
 */

namespace App\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class EloquentServiceProvider
{
    private static bool $initialized = false;
    
    /**
     * Initialize Eloquent with SQL Server connection
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }
        
        // Get database configuration from environment or config.php
        $sql_host = getenv('SQL_SERVER') ?: (getenv('DB_HOST') ?: 'mu-db-dev');
        $sql_user = getenv('DB_USER') ?: 'sa';
        $sql_pass = getenv('DB_PASS') ?: 'Abcd@1234';
        $database = getenv('DB_NAME') ?: 'MuOnline';
        
        // Parse host and port
        $hostParts = explode(',', $sql_host);
        $host = $hostParts[0];
        $port = isset($hostParts[1]) ? $hostParts[1] : '1433';
        
        $capsule = new Capsule;
        
        // Main MuOnline database connection
        $capsule->addConnection([
            'driver'    => 'sqlsrv',
            'host'      => $host,
            'port'      => $port,
            'database'  => $database,
            'username'  => $sql_user,
            'password'  => $sql_pass,
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'options'   => [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE  => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ],
        ]);
        
        // Set the event dispatcher and container
        $capsule->setEventDispatcher(new Dispatcher(new Container()));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        
        self::$initialized = true;
    }
    
    /**
     * Check if Eloquent is initialized
     */
    public static function isInitialized(): bool
    {
        return self::$initialized;
    }
}
