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
    if (empty($string)) {
        return 'post-' . time();
    }
    
    $slug = mb_strtolower(trim($string), 'UTF-8');
    
    // جایگزینی کاراکترهای فارسی با معادل انگلیسی
    $persian = ['آ', 'ا', 'ب', 'پ', 'ت', 'ث', 'ج', 'چ', 'ح', 'خ', 'د', 'ذ', 'ر', 'ز', 'ژ', 'س', 'ش', 'ص', 'ض', 'ط', 'ظ', 'ع', 'غ', 'ف', 'ق', 'ک', 'گ', 'ل', 'م', 'ن', 'و', 'ه', 'ی'];
    $english = ['a', 'a', 'b', 'p', 't', 's', 'j', 'ch', 'h', 'kh', 'd', 'z', 'r', 'z', 'zh', 's', 'sh', 's', 'z', 't', 'z', 'a', 'gh', 'f', 'gh', 'k', 'g', 'l', 'm', 'n', 'o', 'h', 'y'];
    $slug = str_replace($persian, $english, $slug);
    
    $slug = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    if (empty($slug)) {
        $slug = 'post-' . time();
    }
    
    return $slug;
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
    
    $body = '
    <!DOCTYPE html>
    <html dir="rtl" lang="fa">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>بازیابی رمز عبور</title>
        <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
        <style>
            /* ============================================
               RESET & BASE
               ============================================ */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: "Vazirmatn", "IRANSans", Tahoma, sans-serif !important;
                direction: rtl !important;
                background-color: #f8fafc;
                margin: 0;
                padding: 0;
                -webkit-font-smoothing: antialiased;
            }
            
            .container {
                max-width: 600px;
                margin: 30px auto;
                padding: 0 20px;
                background-color: #ffffff;
                border-radius: 16px;
                box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
                overflow: hidden;
            }
            
            /* ============================================
               HEADER
               ============================================ */
            .header {
                background: linear-gradient(135deg, #6C3CE1, #8B6FE8);
                color: #ffffff;
                padding: 30px 24px;
                text-align: center;
            }
            
            .header h2 {
                margin: 0;
                font-size: 24px;
                font-weight: 700;
                font-family: "Vazirmatn", "IRANSans", Tahoma, sans-serif !important;
            }
            
            .header p {
                margin: 4px 0 0 0;
                font-size: 14px;
                opacity: 0.85;
                font-family: "Vazirmatn", "IRANSans", Tahoma, sans-serif !important;
            }
            
            /* ============================================
               CONTENT
               ============================================ */
            .content {
                padding: 30px 24px;
                direction: rtl !important;
                text-align: right !important;
            }
            
            .content p {
                color: #1e293b;
                margin-bottom: 16px;
                font-size: 15px;
                line-height: 2;
                font-family: "Vazirmatn", "IRANSans", Tahoma, sans-serif !important;
                text-align: right !important;
            }
            
            .content strong {
                color: #1e293b;
            }
            
            /* ============================================
               BUTTON
               ============================================ */
            .button-container {
                text-align: center;
                margin: 28px 0 24px;
            }
            
            .button {
                display: inline-block;
                background: linear-gradient(135deg, #6C3CE1, #8B6FE8);
                color: #ffffff !important;
                padding: 14px 44px;
                text-decoration: none;
                border-radius: 50px;
                font-weight: 600;
                font-size: 16px;
                font-family: "Vazirmatn", "IRANSans", Tahoma, sans-serif !important;
                box-shadow: 0 4px 16px rgba(108, 60, 225, 0.25);
                transition: all 0.3s ease;
                border: none;
                cursor: pointer;
            }
            
            .button:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 30px rgba(108, 60, 225, 0.35);
            }
            
            /* ============================================
               LINK BOX
               ============================================ */
            .link-box {
                background: #f1f5f9;
                padding: 14px 18px;
                border-radius: 10px;
                word-break: break-all;
                direction: ltr;
                text-align: left;
                margin: 12px 0 16px;
                font-size: 13px;
                color: #475569;
                font-family: "Vazirmatn", "IRANSans", Tahoma, sans-serif !important;
            }
            
            .link-box a {
                color: #6C3CE1;
                text-decoration: none;
                font-family: "Vazirmatn", "IRANSans", Tahoma, sans-serif !important;
            }
            
            .link-box a:hover {
                text-decoration: underline;
            }
            
            /* ============================================
               WARNING
               ============================================ */
            .warning {
                background: #fef3c7;
                border-right: 4px solid #f59e0b;
                padding: 14px 18px;
                border-radius: 10px;
                margin: 16px 0;
                font-size: 14px;
                color: #92400e;
                font-family: "Vazirmatn", "IRANSans", Tahoma, sans-serif !important;
                text-align: right !important;
            }
            
            .warning ul {
                margin: 8px 0 0 0;
                padding-right: 20px;
                font-family: "Vazirmatn", "IRANSans", Tahoma, sans-serif !important;
            }
            
            .warning ul li {
                margin-bottom: 4px;
                font-family: "Vazirmatn", "IRANSans", Tahoma, sans-serif !important;
            }
            
            /* ============================================
               FOOTER
               ============================================ */
            .footer {
                text-align: center;
                padding: 20px 24px;
                color: #94a3b8;
                font-size: 13px;
                border-top: 1px solid #e2e8f0;
                font-family: "Vazirmatn", "IRANSans", Tahoma, sans-serif !important;
            }
            
            .footer p {
                margin: 0;
                font-family: "Vazirmatn", "IRANSans", Tahoma, sans-serif !important;
            }
            
            /* ============================================
               RESPONSIVE
               ============================================ */
            @media (max-width: 480px) {
                .container {
                    margin: 16px auto;
                    border-radius: 12px;
                }
                .header {
                    padding: 20px 16px;
                }
                .header h2 {
                    font-size: 20px;
                }
                .content {
                    padding: 20px 16px;
                }
                .content p {
                    font-size: 14px;
                }
                .button {
                    padding: 12px 32px;
                    font-size: 14px;
                }
                .link-box {
                    font-size: 12px;
                    padding: 10px 14px;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- Header -->
            <div class="header">
                <h2>🔐 بازیابی رمز عبور</h2>
                <p>' . $siteName . '</p>
            </div>
            
            <!-- Content -->
            <div class="content">
                <p>سلام <strong>' . $name . '</strong>،</p>
                
                <p>درخواست بازیابی رمز عبور برای حساب کاربری شما در سایت <strong>' . $siteName . '</strong> ثبت شده است.</p>
                
                <p>برای تنظیم رمز عبور جدید، روی دکمه زیر کلیک کنید:</p>
                
                <div class="button-container">
                    <a href="' . $resetUrl . '" class="button">🔄 بازیابی رمز عبور</a>
                </div>
                
                <p>اگر روی دکمه نمی‌توانید کلیک کنید، لینک زیر را در مرورگر خود کپی کنید:</p>
                
                <div class="link-box">
                    <a href="' . $resetUrl . '">' . $resetUrl . '</a>
                </div>
                
                <div class="warning">
                    <strong>⚠️ نکات مهم:</strong>
                    <ul>
                        <li>این لینک به مدت <strong>۱ ساعت</strong> معتبر است.</li>
                        <li>پس از تغییر رمز عبور، این لینک دیگر قابل استفاده نخواهد بود.</li>
                        <li>اگر این درخواست را ارسال نکرده‌اید، این ایمیل را نادیده بگیرید.</li>
                    </ul>
                </div>
                
                <p style="margin-top: 20px; color: #64748b; font-size: 14px;">
                    برای امنیت بیشتر، پس از ورود با رمز جدید، حتماً رمز عبور خود را در پنل کاربری به‌روزرسانی کنید.
                </p>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p>این ایمیل به‌طور خودکار ارسال شده است. لطفاً به آن پاسخ ندهید.</p>
                <p>© 2026 ' . $siteName . ' - تمامی حقوق محفوظ است.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
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