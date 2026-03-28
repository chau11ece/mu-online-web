<?php
/**
 * Eloquent Database Service Provider
 * 
 * Initializes Laravel's Eloquent ORM for the DT Web 2.0 application.
 * Uses the custom SQL Server connector that handles SSL properly.
 */

namespace App\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use App\Database\Connectors\SQLServerConnector;

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
        
        // Get database configuration from environment
        $sql_host = getenv('SQL_SERVER') ?: (getenv('DB_HOST') ?: 'mu-db-dev,1433');
        $sql_user = getenv('DB_USER') ?: 'sa';
        $sql_pass = getenv('DB_PASS') ?: 'Abcd@1234';
        $database = getenv('DB_NAME') ?: 'MuOnline';
        
        $capsule = new Capsule;
        
        // Add connection using custom connector
        $capsule->addConnection([
            'driver'      => 'sqlsrv',
            'host'        => $sql_host,
            'port'        => 1433,
            'database'    => $database,
            'username'    => $sql_user,
            'password'    => $sql_pass,
            'charset'     => 'utf8',
            'collation'   => 'utf8_unicode_ci',
            'prefix'      => '',
        ]);
        
        // Set the event dispatcher and container
        $capsule->setEventDispatcher(new Dispatcher(new Container()));
        $capsule->setAsGlobal();
        
        // Override the SQL Server connector with our custom one
        $container = $capsule->getContainer();
        $container->singleton('db.connector.sqlsrv', function () {
            return new SQLServerConnector();
        });
        
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
