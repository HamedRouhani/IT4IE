<?php
// دریافت آدرس پایه سایت به صورت داینامیک
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// اینجا آدرس اصلی نرم‌افزار را بر اساس اسلاگ دیتابیس تنظیم می‌کنیم
// اگر پوشه شما babok است اما اسلاگ babok-analyzer است، آدرس زیر را تغییر دهید
$baseSoftwareUrl = '/software/babok-analyzer';

// هدایت کاربر به پوشه public نرم‌افزار
header('Location: ' . $protocol . '://' . $host . $baseSoftwareUrl . '/public/');
exit;
?>