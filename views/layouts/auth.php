<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'ورود / ثبت‌نام - IT4IE'; ?></title>
    <meta name="description" content="ورود و ثبت‌نام در سایت IT4IE">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Vazirmatn Font -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <style>
        body.auth-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .auth-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .auth-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .auth-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .auth-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #2D3748;
            font-size: 1.4rem;
            font-weight: 800;
        }
        .auth-logo i {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="<?php echo $bodyClass ?? 'auth-page'; ?>">
    
    <!-- Header ساده برای صفحات احراز هویت -->
    <header class="auth-header">
        <div class="container">
            <a href="/" class="auth-logo">
                <i class="fas fa-cubes"></i>
                <span>IT4IE</span>
            </a>
            <a href="/" style="color: #4A5568; text-decoration: none; font-size: 0.9rem;">
                <i class="fas fa-arrow-right"></i> بازگشت به خانه
            </a>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="auth-main">
        <div class="container" style="max-width: 1200px; width: 100%;">
            <?php echo $content ?? ''; ?>
        </div>
    </main>
    
    <!-- Custom JS -->
    <script src="/assets/js/script.js"></script>
    
    <?php if (isset($_SESSION['message'])): ?>
    <script>
        alert('<?php echo addslashes($_SESSION['message']); ?>');
        <?php unset($_SESSION['message']); ?>
    </script>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <script>
        alert('<?php echo addslashes($_SESSION['error']); ?>');
        <?php unset($_SESSION['error']); ?>
    </script>
    <?php endif; ?>
    
</body>
</html>