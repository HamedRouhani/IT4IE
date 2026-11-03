<?php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;
    
    private function __construct()
    {
        $host = getenv('DB_HOST') ;
        $name = getenv('DB_NAME') ;
        $user = getenv('DB_USER') ;
        $pass = getenv('DB_PASS') ;
        
        try {
            $this->connection = new PDO(
                "mysql:host=$host;dbname=$name;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            $this->connection->exec("SET time_zone = '+03:30'");
        } catch (PDOException $e) {
            // پیام کامل فقط در لاگ سرور ثبت می‌شود؛ کاربر جزئیات اعتبارنامه را نمی‌بیند.
            error_log('Database connection failed: ' . $e->getMessage());
            self::renderUnavailable();
        }
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }
    
    public static function getConnection($host, $name, $user, $pass)
    {
        try {
            $connection = new PDO(
                "mysql:host=$host;dbname=$name;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            $connection->exec("SET time_zone = '+03:30'");
            return $connection;
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new \Exception('Database connection failed.');
        }
    }

    /**
     * نمایش خطای عمومی ۵۰۳ بدون افشای هاست، کاربر یا اعتبارنامهٔ پایگاه داده.
     */
    private static function renderUnavailable()
    {
        if (!headers_sent()) {
            http_response_code(503);
            header('Content-Type: text/html; charset=utf-8');
        }

        echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8">'
            . '<title>سرویس در دسترس نیست</title></head>'
            . '<body style="font-family:Tahoma;direction:rtl;text-align:center;padding:60px;">'
            . '<h1>۵۰۳ - سرویس موقتاً در دسترس نیست</h1>'
            . '<p>ارتباط با پایگاه داده برقرار نشد. لطفاً چند لحظه بعد دوباره تلاش کنید.</p>'
            . '</body></html>';

        exit;
    }
}
