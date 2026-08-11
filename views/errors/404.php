<?php
// views/errors/404.php
$url = $_GET['url'] ?? 'نامشخص';
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <title>404 - صفحه یافت نشد | IT4IE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Tahoma, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            max-width: 550px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .error-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3.5rem;
            box-shadow: 0 10px 30px rgba(245, 87, 108, 0.4);
        }
        .error-code {
            font-size: 6rem;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 15px;
        }
        .error-title {
            font-size: 1.8rem;
            color: #2D3748;
            margin-bottom: 15px;
            font-weight: 700;
        }
        .error-message {
            color: #718096;
            line-height: 1.7;
            margin-bottom: 30px;
            font-size: 1.05rem;
        }
        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 0.95rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        .btn-secondary {
            background: #f7fafc;
            color: #4A5568;
            border: 2px solid #e2e8f0;
        }
        .btn-secondary:hover {
            background: #edf2f7;
            transform: translateY(-2px);
        }
        .searched-url {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            font-family: monospace;
            color: #e74c3c;
            margin: 15px 0;
            word-break: break-all;
            font-size: 0.9rem;
            direction: ltr;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon"><i class="fas fa-map-signs"></i></div>
        <div class="error-code">404</div>
        <h1 class="error-title">صفحه مورد نظر یافت نشد</h1>
        <p class="error-message">متأسفانه صفحه‌ای که به دنبال آن هستید وجود ندارد.</p>
        <div class="searched-url"><?= htmlspecialchars($url) ?></div>
        <div class="error-actions">
            <a href="/" class="btn btn-primary"><i class="fas fa-home"></i> صفحه اصلی</a>
            <a href="/software" class="btn btn-secondary"><i class="fas fa-cubes"></i> نرم‌افزارها</a>
        </div>
    </div>
</body>
</html>