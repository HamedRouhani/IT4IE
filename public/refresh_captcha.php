<?php
// Start session
session_start();

// Error details are never printed here; failures are reported through the log.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Define base paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

// Load environment variables
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $value = trim($value, '"\'');
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Check if GD extension is loaded
if (!extension_loaded('gd')) {
    http_response_code(503);
    exit('سرویس تولید تصویر موقتاً در دسترس نیست.');
}

// Direct include Captcha
$captchaFile = APP_PATH . '/helpers/Captcha.php';
if (!file_exists($captchaFile)) {
    error_log('Captcha helper file is missing.');
    http_response_code(500);
    exit('خطای داخلی رخ داد.');
}

require_once $captchaFile;
use App\Helpers\Captcha;

$captcha = new Captcha();
$captcha->generate();
