<?php
namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;
    private $config;

    private function __construct()
    {
        // بارگذاری تنظیمات از .env
        $this->config = [
            'host' => Env::get('DB_HOST', 'localhost'),
            'dbname' => Env::get('DB_NAME', 'aiproduc_BABOK'),
            'username' => Env::get('DB_USER', 'aiproduc_BABOK'),
            'password' => Env::get('DB_PASS', ''),
            'port' => Env::get('DB_PORT', 3306),
            'charset' => Env::get('DB_CHARSET', 'utf8mb4')
        ];

        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $this->config['host'],
                $this->config['port'],
                $this->config['dbname'],
                $this->config['charset']
            );

            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );

            $this->connection->exec("SET NAMES {$this->config['charset']} COLLATE {$this->config['charset']}_unicode_ci");
            
        } catch (PDOException $e) {
            $this->handleConnectionError($e);
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    public function commit()
    {
        return $this->connection->commit();
    }

    public function rollback()
    {
        return $this->connection->rollBack();
    }

    private function handleConnectionError($e)
    {
        $isDebug = Env::getBool('APP_DEBUG', true);
        
        if ($isDebug) {
            die("Connection failed: " . $e->getMessage());
        } else {
            die("خطا در اتصال به دیتابیس. لطفاً با پشتیبانی تماس بگیرید.");
        }
    }

    public static function testConnection()
    {
        try {
            $instance = self::getInstance();
            $connection = $instance->getConnection();
            $connection->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}