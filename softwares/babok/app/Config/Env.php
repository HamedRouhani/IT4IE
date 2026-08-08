<?php
namespace App\Config;

class Env
{
    private static $variables = [];
    private static $loaded = false;

    /**
     * بارگذاری فایل .env
     */
    public static function load($path = null)
    {
        if (self::$loaded) {
            return;
        }

        // اگر مسیر داده نشده، مسیرهای مختلف را امتحان کن
        if (!$path) {
            $possiblePaths = [
                __DIR__ . '/../../.env',
                __DIR__ . '/../.env',
                __DIR__ . '/.env'
            ];
            foreach ($possiblePaths as $tryPath) {
                if (file_exists($tryPath)) {
                    $path = $tryPath;
                    break;
                }
            }
        }

        if (!file_exists($path)) {
            throw new \Exception(".env file not found at: " . $path);
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // نادیده گرفتن کامنت‌ها
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($name, $value) = array_map('trim', explode('=', $line, 2));
                
                // حذف نقل قول‌ها
                $value = trim($value);
                if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                    $value = substr($value, 1, -1);
                }
                if (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
                    $value = substr($value, 1, -1);
                }
                
                self::$variables[$name] = $value;
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
            }
        }

        self::$loaded = true;
    }

    /**
     * دریافت مقدار یک متغیر
     */
    public static function get($key, $default = null)
    {
        if (!self::$loaded) {
            $possiblePaths = [
                __DIR__ . '/../../.env',
                __DIR__ . '/../.env',
                __DIR__ . '/.env'
            ];
            foreach ($possiblePaths as $tryPath) {
                if (file_exists($tryPath)) {
                    try {
                        self::load($tryPath);
                        break;
                    } catch (\Exception $e) {
                        // ادامه به مسیر بعدی
                    }
                }
            }
        }

        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        if (isset(self::$variables[$key])) {
            return self::$variables[$key];
        }

        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        return $default;
    }

    /**
     * بررسی وجود متغیر
     */
    public static function has($key)
    {
        return self::get($key) !== null;
    }

    /**
     * دریافت متغیر به صورت boolean
     */
    public static function getBool($key, $default = false)
    {
        $value = self::get($key, $default);
        if (is_bool($value)) {
            return $value;
        }
        return in_array(strtolower($value), ['true', '1', 'on', 'yes']);
    }

    /**
     * دریافت متغیر به صورت integer
     */
    public static function getInt($key, $default = 0)
    {
        return (int) self::get($key, $default);
    }
}