<?php
/**
 * Database Singleton Pattern
 *
 * Zajišťuje pouze jedno připojení k databázi během celého životního cyklu aplikace.
 * Implementuje PDO s plnou ochranou proti SQL Injection pomocí prepared statements.
 */

namespace App;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    /**
     * Private konstruktor - zabraňuje přímé instanciaci
     */
    private function __construct()
    {
        $config = require __DIR__ . '/../config/db.php';

        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['dbname'],
                $config['charset']
            );

            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            // V produkci NIKDY neukazovat detaily chyby
            error_log('Database connection failed: ' . $e->getMessage());
            die('Database connection error. Please contact administrator.');
        }
    }

    /**
     * Zabraňuje klonování instance
     */
    private function __clone() {}

    /**
     * Zabraňuje unserializaci instance
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Vrátí singleton instanci databáze
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Vrátí PDO connection objekt
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Spustí dotaz s parametry (prepared statement)
     *
     * @param string $query SQL dotaz s placeholdery
     * @param array $params Parametry pro binding
     * @return \PDOStatement
     */
    public function query(string $query, array $params = [])
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Query failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Začne transakci
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Potvrdí transakci
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Rollback transakce
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * Vrátí ID posledního vloženého záznamu
     */
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }
}
