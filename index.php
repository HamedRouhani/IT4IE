<?php
/**
 * ==========================================================
 * نقطهٔ ورود قدیمی — فقط برای سازگاری باقی مانده است.
 * ==========================================================
 * نقطهٔ ورود واقعی برنامه public/index.php است (نگاه کنید به .htaccess ریشه).
 * اگر این پوشه مستقیماً DocumentRoot شود و mod_rewrite در دسترس نباشد، این فایل
 * درخواست را به همان بوت‌استرپ اصلی می‌سپارد؛ منطق راه‌اندازی فقط یک‌جا
 * (public/index.php) نگهداری می‌شود.
 */

$entryPoint = __DIR__ . '/public/index.php';

if (!is_file($entryPoint)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Application entry point not found: public/index.php';
    exit;
}

require $entryPoint;
