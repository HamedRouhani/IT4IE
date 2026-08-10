<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'پنل مدیریت - IT4IE'; ?></title>
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Vazirmatn Font -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <style>
        /* مخفی کردن سایدبار اصلی سایت در پنل ادمین */
        body.admin-page .sidebar {
            display: none !important;
        }
        
        /* ساختار admin container */
        .admin-container {
            display: flex;
            gap: 25px;
            padding: 20px;
            min-height: calc(100vh - 100px);
        }
        
        /* سایدبار ادمین */
        .admin-sidebar {
            width: 260px;
            flex-shrink: 0;
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .admin-brand {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #3498db;
        }
        
        .admin-brand h3 {
            margin: 0 0 5px 0;
            color: #2c3e50;
            font-size: 1.2rem;
        }
        
        .admin-brand span {
            font-size: 0.8rem;
            color: #7f8c8d;
        }
        
        .admin-sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .admin-sidebar ul li {
            margin-bottom: 5px;
        }
        
        .admin-sidebar ul li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            color: #555;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        
        .admin-sidebar ul li a:hover {
            background: #f0f7ff;
            color: #3498db;
        }
        
        .admin-sidebar ul li a.active {
            background: #3498db;
            color: #fff;
        }
        
        .admin-sidebar ul li a i {
            width: 18px;
            text-align: center;
        }
        
        /* محتوای اصلی ادمین */
        .admin-content {
            flex: 1;
            min-width: 0;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .admin-header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.6rem;
        }
        
        /* کارت‌های ادمین */
        .admin-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .admin-card h2 {
            margin: 0 0 20px 0;
            color: #2c3e50;
            font-size: 1.3rem;
        }
        
        /* ریسپانسیو */
        @media (max-width: 992px) {
            .admin-container {
                flex-direction: column;
            }
            
            .admin-sidebar {
                width: 100%;
                position: static;
            }
        }
        
        @media (max-width: 768px) {
            .admin-sidebar ul {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
            }
            
            .admin-sidebar ul li {
                margin: 0;
            }
            
            .admin-sidebar ul li a {
                padding: 8px 12px;
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 480px) {
            .admin-sidebar ul {
                flex-direction: column;
                align-items: stretch;
            }
            
            .admin-sidebar ul li a {
                justify-content: center;
            }
        }
    </style>
</head>
<body class="admin-page">
    
    <!-- Header سایت -->
    <?php include VIEWS_PATH . '/partials/header.php'; ?>
    
    <!-- Admin Content (سایدبار و محتوا در ویوهای admin رندر می‌شوند) -->
    <main class="admin-main">
        <div class="container">
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