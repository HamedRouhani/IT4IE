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
        /* استایل‌های اضافی برای اطمینان از چیدمان در موبایل */
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column !important;
            }
            .admin-sidebar {
                width: 100% !important;
                position: static !important;
                order: 1 !important;
                margin-bottom: 16px;
            }
            .admin-content {
                order: 2 !important;
                width: 100% !important;
            }
            .admin-sidebar ul {
                display: flex !important;
                flex-wrap: wrap !important;
                gap: 4px;
                justify-content: center;
            }
            .admin-sidebar ul li a {
                padding: 8px 12px !important;
                font-size: 13px !important;
                white-space: nowrap !important;
            }
        }
        @media (max-width: 480px) {
            .admin-sidebar ul {
                flex-direction: column !important;
                align-items: stretch !important;
            }
            .admin-sidebar ul li a {
                white-space: normal !important;
                justify-content: center;
            }
        }
    </style>
</head>
<body class="admin-page">
    
    <!-- Header -->
    <?php include VIEWS_PATH . '/partials/header.php'; ?>
    
    <!-- Admin Content -->
    <main class="admin-main">
        <div class="container">
            <?php echo $content ?? ''; ?>
        </div>
    </main>
    
    <!-- Custom JS -->
    <script src="/assets/js/script.js"></script>
    
    <?php if (isset($_SESSION['message'])): ?>
    <script>
        alert('<?php echo $_SESSION['message']; ?>');
        <?php unset($_SESSION['message']); ?>
    </script>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <script>
        alert('<?php echo $_SESSION['error']; ?>');
        <?php unset($_SESSION['error']); ?>
    </script>
    <?php endif; ?>
    
</body>
</html>