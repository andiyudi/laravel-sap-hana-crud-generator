<?php

namespace Custom\LaravelHana;

use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;
use PDO;

class HanaConnector extends Connector implements ConnectorInterface
{
    /**
     * Establish a database connection.
     *
     * @param  array  $config
     * @return \PDO
     */
    public function connect(array $config): PDO
    {
        $dsn     = $this->getDsn($config);
        $options = $this->getOptions($config);

        // HANA via ODBC requires emulated prepares so bindings are substituted
        // as string literals rather than sent as native parameters.
        $options[PDO::ATTR_EMULATE_PREPARES] = true;

        $connection = $this->createConnection($dsn, $config, $options);

        if (isset($config['schema'])) {
            $connection->exec("SET SCHEMA \"{$config['schema']}\"");
        }

        return $connection;
    }

    /**
     * Create a DSN string from a configuration.
     *
     * @param  array  $config
     * @return string
     */
    protected function getDsn(array $config): string
    {
        $host     = $config['host'] ?? '127.0.0.1';
        $port     = $config['port'] ?? '30015';
        $database = $config['database'] ?? '';

        return "odbc:DRIVER={HDBODBC};ServerNode={$host}:{$port};DatabaseName={$database}";
    }
}
