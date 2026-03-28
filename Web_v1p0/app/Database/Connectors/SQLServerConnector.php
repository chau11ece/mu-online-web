<?php
/**
 * Custom SQL Server Connector for Laravel Eloquent
 * 
 * This connector disables SSL certificate verification for local development
 */

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;
use PDO;

class SQLServerConnector extends Connector implements ConnectorInterface
{
    /**
     * The PDO connection options.
     *
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    /**
     * Establish a database connection.
     *
     * @param  array  $config
     * @return \PDO
     */
    public function connect(array $config)
    {
        $options = $this->getOptions($config);

        // Add custom options for self-signed certificate
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        $options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_ASSOC;
        $options[PDO::ATTR_EMULATE_PREPARES] = false;
        
        $dsn = $this->getDsn($config);
        
        // Add SSL disabling options to DSN
        $dsn .= ';TrustServerCertificate=1';
        $dsn .= ';Encrypt=0';

        return $this->createConnection($dsn, $config, $options);
    }
    
    /**
     * Create a new PDO connection.
     *
     * @param  string  $dsn
     * @param  array  $config
     * @param  array  $options
     * @return \PDO
     */
    public function createConnection($dsn, array $config, array $options): \PDO
    {
        [$username, $password] = [
            $config['username'] ?? null, $config['password'] ?? null,
        ];

        return new PDO($dsn, $username, $password, $options);
    }
    
    /**
     * Get the DSN for a SQL Server connection.
     *
     * @param  array  $config
     * @return string
     */
    protected function getDsn(array $config): string
    {
        $port = isset($config['port']) ? $config['port'] : 1433;
        
        // Check if we're using a host:port format
        if (strpos($config['host'], ',') !== false) {
            return "sqlsrv:Server={$config['host']};Database={$config['database']}";
        }
        
        return "sqlsrv:Server={$config['host']},{$port};Database={$config['database']}";
    }
}
