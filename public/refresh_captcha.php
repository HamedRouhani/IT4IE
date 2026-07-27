<?php
// Start session
session_start();

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    die('❌ GD extension is not loaded. Please enable GD extension in php.ini');
}

// Direct include Captcha
$captchaFile = APP_PATH . '/helpers/Captcha.php';
if (!file_exists($captchaFile)) {
    die('❌ Captcha helper file not found: ' . $captchaFile);
}

require_once $captchaFile;
use App\Helpers\Captcha;

$captcha = new Captcha();
$captcha->generate();