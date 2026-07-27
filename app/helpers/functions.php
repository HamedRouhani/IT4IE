<?php

// Convert date to Jalali (Persian)
function jdate($datetime, $format = 'Y/m/d - H:i')
{
    if (empty($datetime)) {
        return '';
    }
    
    try {
        $date = new DateTime($datetime);
        $date->setTimezone(new DateTimeZone('Asia/Tehran'));
        return $date->format($format);
    } catch (Exception $e) {
        return $datetime;
    }
}

// Generate random string
function random_string($length = 32)
{
    return bin2hex(random_bytes($length / 2));
}

// Truncate text
function truncate_text($text, $length = 150, $suffix = '...')
{
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    
    $truncated = mb_substr($text, 0, $length);
    $lastSpace = mb_strrpos($truncated, ' ');
    if ($lastSpace !== false) {
        $truncated = mb_substr($truncated, 0, $lastSpace);
    }
    
    return $truncated . $suffix;
}

// Safe output
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Get current user ID
function getUserId()
{
    return $_SESSION['user_id'] ?? null;
}

// Get current user name
function getUserName()
{
    return $_SESSION['user_name'] ?? 'کاربر';
}

// Generate slug from string
function generateSlug($string)
{
    $string = trim($string);
    $string = preg_replace('/[^a-zA-Z0-9_\u0600-\u06FF]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return strtolower(trim($string, '-'));
}

// ============================================
// SEND MAIL - با استفاده از mail() ساده
// ============================================
function sendMail($to, $subject, $body, $from = null)
{
    if (empty($to) || empty($subject) || empty($body)) {
        return false;
    }
    
    // تنظیم هدرها
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . ($from ?: 'noreply@it4ie.ir') . "\r\n";
    $headers .= "Reply-To: " . ($from ?: 'noreply@it4ie.ir') . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // ارسال ایمیل
    $result = mail($to, $subject, $body, $headers);
    
    // لاگ برای دیباگ
    error_log("Email sent to: {$to} - Subject: {$subject} - Result: " . ($result ? 'Success' : 'Failed'));
    
    return $result;
}

// ============================================
// SEND RESET PASSWORD EMAIL
// ============================================
function sendResetEmail($email, $name, $token)
{
    $siteName = getenv('APP_NAME') ?: 'IT4IE';
    $resetUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/reset-password/' . $token;
    
    $subject = 'بازیابی رمز عبور - ' . $siteName;
    
    $body = "
    <!DOCTYPE html>
    <html dir='rtl' lang='fa'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>بازیابی رمز عبور</title>
        <style>
            body {
                font-family: 'Vazirmatn', 'IRANSans', Tahoma, sans-serif;
                direction: rtl;
                background-color: #f8fafc;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #ffffff;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                margin-top: 30px;
                margin-bottom: 30px;
            }
            .header {
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                color: white;
                padding: 25px 20px;
                border-radius: 12px 12px 0 0;
                text-align: center;
                margin: -20px -20px 0 -20px;
            }
            .header h2 {
                margin: 0;
                font-size: 22px;
            }
            .content {
                padding: 30px 20px;
                line-height: 1.8;
            }
            .content p {
                color: #1e293b;
                margin-bottom: 16px;
            }
            .button-container {
                text-align: center;
                margin: 30px 0 20px;
            }
            .button {
                display: inline-block;
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                color: white;
                padding: 14px 40px;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                font-size: 16px;
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
                transition: all 0.3s ease;
            }
            .button:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            }
            .link-box {
                background: #f1f5f9;
                padding: 12px 16px;
                border-radius: 8px;
                word-break: break-all;
                direction: ltr;
                text-align: left;
                margin: 10px 0;
                font-size: 14px;
                color: #475569;
            }
            .link-box a {
                color: #2563eb;
                text-decoration: none;
            }
            .footer {
                text-align: center;
                padding: 20px;
                color: #94a3b8;
                font-size: 13px;
                border-top: 1px solid #e2e8f0;
                margin: 0 -20px -20px -20px;
            }
            .warning {
                background: #fef3c7;
                border-right: 4px solid #f59e0b;
                padding: 12px 16px;
                border-radius: 8px;
                margin: 16px 0;
                font-size: 14px;
                color: #92400e;
            }
            .warning ul {
                margin: 8px 0 0 0;
                padding-right: 20px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>🔐 بازیابی رمز عبور</h2>
            </div>
            
            <div class='content'>
                <p>سلام <strong>{$name}</strong>،</p>
                
                <p>درخواست بازیابی رمز عبور برای حساب کاربری شما در سایت <strong>{$siteName}</strong> ثبت شده است.</p>
                
                <p>برای تنظیم رمز عبور جدید، روی دکمه زیر کلیک کنید:</p>
                
                <div class='button-container'>
                    <a href='{$resetUrl}' class='button'>🔄 بازیابی رمز عبور</a>
                </div>
                
                <p>اگر روی دکمه نمی‌توانید کلیک کنید، لینک زیر را در مرورگر خود کپی کنید:</p>
                
                <div class='link-box'>
                    <a href='{$resetUrl}'>{$resetUrl}</a>
                </div>
                
                <div class='warning'>
                    ⚠️ <strong>نکات مهم:</strong>
                    <ul>
                        <li>این لینک به مدت <strong>۱ ساعت</strong> معتبر است.</li>
                        <li>پس از تغییر رمز عبور، این لینک دیگر قابل استفاده نخواهد بود.</li>
                        <li>اگر این درخواست را ارسال نکرده‌اید، این ایمیل را نادیده بگیرید.</li>
                    </ul>
                </div>
                
                <p style='margin-top: 20px; color: #64748b; font-size: 14px;'>
                    برای امنیت بیشتر، پس از ورود با رمز جدید، حتماً رمز عبور خود را در پنل کاربری به‌روزرسانی کنید.
                </p>
            </div>
            
            <div class='footer'>
                <p>این ایمیل به‌طور خودکار ارسال شده است. لطفاً به آن پاسخ ندهید.</p>
                <p>© 2026 {$siteName} - تمامی حقوق محفوظ است.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendMail($email, $subject, $body);
}

// ============================================
// SEND VERIFICATION EMAIL
// ============================================
function sendVerificationEmail($email, $name, $token)
{
    $siteName = getenv('APP_NAME') ?: 'IT4IE';
    $verifyUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/verify/' . $token;
    
    $subject = 'تأیید ایمیل - ' . $siteName;
    
    $body = "
    <!DOCTYPE html>
    <html dir='rtl' lang='fa'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>تأیید ایمیل</title>
        <style>
            body {
                font-family: 'Vazirmatn', 'IRANSans', Tahoma, sans-serif;
                direction: rtl;
                background-color: #f8fafc;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #ffffff;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                margin-top: 30px;
                margin-bottom: 30px;
            }
            .header {
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                color: white;
                padding: 25px 20px;
                border-radius: 12px 12px 0 0;
                text-align: center;
                margin: -20px -20px 0 -20px;
            }
            .header h2 {
                margin: 0;
                font-size: 22px;
            }
            .content {
                padding: 30px 20px;
                line-height: 1.8;
            }
            .content p {
                color: #1e293b;
                margin-bottom: 16px;
            }
            .button-container {
                text-align: center;
                margin: 30px 0 20px;
            }
            .button {
                display: inline-block;
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                color: white;
                padding: 14px 40px;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                font-size: 16px;
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
                transition: all 0.3s ease;
            }
            .button:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            }
            .link-box {
                background: #f1f5f9;
                padding: 12px 16px;
                border-radius: 8px;
                word-break: break-all;
                direction: ltr;
                text-align: left;
                margin: 10px 0;
                font-size: 14px;
                color: #475569;
            }
            .link-box a {
                color: #2563eb;
                text-decoration: none;
            }
            .footer {
                text-align: center;
                padding: 20px;
                color: #94a3b8;
                font-size: 13px;
                border-top: 1px solid #e2e8f0;
                margin: 0 -20px -20px -20px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>✅ تأیید ایمیل</h2>
            </div>
            
            <div class='content'>
                <p>سلام <strong>{$name}</strong>،</p>
                
                <p>از ثبت‌نام شما در سایت <strong>{$siteName}</strong> خرسندیم.</p>
                
                <p>لطفاً برای تأیید ایمیل خود روی دکمه زیر کلیک کنید:</p>
                
                <div class='button-container'>
                    <a href='{$verifyUrl}' class='button'>✅ تأیید ایمیل</a>
                </div>
                
                <p>اگر روی دکمه نمی‌توانید کلیک کنید، لینک زیر را در مرورگر خود کپی کنید:</p>
                
                <div class='link-box'>
                    <a href='{$verifyUrl}'>{$verifyUrl}</a>
                </div>
                
                <p>این لینک به مدت <strong>۷۲ ساعت</strong> معتبر است.</p>
            </div>
            
            <div class='footer'>
                <p>این ایمیل به‌طور خودکار ارسال شده است. لطفاً به آن پاسخ ندهید.</p>
                <p>© 2026 {$siteName} - تمامی حقوق محفوظ است.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendMail($email, $subject, $body);
}